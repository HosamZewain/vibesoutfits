<?php

namespace App\Services;

use App\Models\Variant;
use Illuminate\Session\Store;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class Cart
{
    public function __construct(private Store $session) {}

    public function raw(): array
    {
        return $this->session->get('vibe.cart', []);
    }

    public function add(array $line): void
    {
        $lines = $this->raw();
        if (count($lines) >= 20) {
            self::fail('السلة فيها ٢٠ اختيار. شيل قطعة عشان تضيف غيرها.');
        }

        $lines[(string) Str::uuid()] = $line;
        $this->session->put('vibe.cart', $lines);
    }

    public function update(string $key, int $quantity): void
    {
        $lines = $this->raw();
        abort_unless(isset($lines[$key]), 404);
        if ($quantity === 0) {
            if ($lines[$key]['upload_path']) {
                Storage::disk('local')->delete($lines[$key]['upload_path']);
            }
            unset($lines[$key]);
        } else {
            $lines[$key]['quantity'] = $quantity;
        }
        $this->session->put('vibe.cart', $lines);
    }

    public function clear(): void
    {
        $this->session->forget('vibe.cart');
    }

    public function summarize(bool $lock = false): array
    {
        $lines = $this->raw();
        $query = Variant::query()->whereIn('id', array_column($lines, 'variant_id'))->orderBy('id');
        $variants = ($lock ? $query->lockForUpdate() : $query)->with('product')->get()->keyBy('id');
        $quantities = [];
        foreach ($lines as $line) {
            $quantities[$line['variant_id']] = ($quantities[$line['variant_id']] ?? 0) + $line['quantity'];
        }

        $subtotal = $customSubtotal = 0;
        $valid = true;
        foreach ($lines as $key => &$line) {
            $variant = $variants->get($line['variant_id']);
            $product = $variant?->product;
            $isCustom = $line['design_mode'] !== 'ready';
            $line['variant'] = $variant;
            $line['product'] = $product;
            $line['available'] = $product?->active && $variant->active && $quantities[$variant->id] <= $variant->stock
                && ($isCustom ? $product->custom_enabled : $product->ready_enabled)
                && (! $line['upload_path'] || Storage::disk('local')->exists($line['upload_path']));
            $line['unit_price'] = $product ? $product->price + ($isCustom ? $product->custom_fee : 0) : 0;
            $line['line_total'] = $line['unit_price'] * $line['quantity'];
            $subtotal += $line['line_total'];
            $customSubtotal += $isCustom ? $line['line_total'] : 0;
            $valid = $valid && $line['available'];
        }
        unset($line);

        return compact('lines', 'subtotal', 'customSubtotal', 'valid', 'variants', 'quantities');
    }

    public static function fail(string $message): never
    {
        throw ValidationException::withMessages(['cart' => $message]);
    }
}
