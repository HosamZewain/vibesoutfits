<?php

namespace App\Http\Controllers;

use App\Mail\OrderConfirmation;
use App\Models\Order;
use App\Models\ShippingZone;
use App\Services\Cart;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Throwable;

class CheckoutController extends Controller
{
    public function show(Cart $cart): View|RedirectResponse
    {
        $summary = $cart->summarize();
        if (! $summary['lines'] || ! $summary['valid']) {
            return redirect()->route('cart');
        }

        $zones = ShippingZone::where('active', true)->orderBy('id')->get();
        $transfer = $this->transferEnabled();
        $depositPercent = $this->depositPercent();

        return view('store.checkout', $summary + compact('zones', 'transfer', 'depositPercent'));
    }

    public function place(Request $request, Cart $cart): RedirectResponse
    {
        $request->merge(['phone' => self::phone((string) $request->string('phone'))]);
        $data = $request->validate([
            'name' => ['required', 'string', 'min:2', 'max:100'],
            'phone' => ['required', 'regex:/^01[0125][0-9]{8}$/'],
            'email' => ['nullable', 'email:rfc', 'max:150'],
            'zone' => ['required', 'integer'],
            'city' => ['required', 'string', 'max:90'],
            'address' => ['required', 'string', 'min:8', 'max:300'],
            'notes' => ['nullable', 'string', 'max:500'],
            'payment' => ['required', Rule::in(['cod', 'transfer'])],
            'quoted_total' => ['nullable', 'integer', 'min:0'],
        ]);

        $order = DB::transaction(function () use ($cart, $data): Order {
            $summary = $cart->summarize(lock: true);
            if (! $summary['lines'] || ! $summary['valid']) {
                Cart::fail('السلة اتغيرت. راجع المقاس والكمية قبل تأكيد الطلب.');
            }
            $zone = ShippingZone::where('active', true)->lockForUpdate()->find($data['zone']);
            if (! $zone) {
                throw ValidationException::withMessages(['zone' => 'اختار محافظة الشحن المتاحة.']);
            }
            $total = $summary['subtotal'] + $zone->fee;
            if (isset($data['quoted_total']) && (int) $data['quoted_total'] !== $total) {
                Cart::fail('السعر أو الشحن اتغير. راجع الإجمالي الجديد وأكد الطلب.');
            }
            $custom = $summary['customSubtotal'] > 0 || collect($summary['lines'])->contains(fn (array $line) => $line['design_mode'] !== 'ready');
            if (($custom && $data['payment'] !== 'transfer') || ($data['payment'] === 'transfer' && ! $this->transferEnabled())) {
                throw ValidationException::withMessages(['payment' => 'طريقة الدفع دي مش متاحة للطلب ده.']);
            }
            $deposit = $custom
                ? max(1, (int) ceil($summary['customSubtotal'] * $this->depositPercent() / 100))
                : ($data['payment'] === 'transfer' ? $total : 0);

            $order = Order::create([
                'number' => 'VB-'.Str::upper(Str::random(10)),
                'name' => $data['name'], 'phone' => $data['phone'], 'email' => $data['email'] ?? null,
                'governorate' => $zone->name, 'city' => $data['city'], 'address' => $data['address'], 'notes' => $data['notes'] ?? null,
                'subtotal' => $summary['subtotal'], 'shipping_fee' => $zone->fee, 'total' => $total,
                'payment_method' => $data['payment'], 'deposit_due' => $deposit,
            ]);

            foreach ($summary['quantities'] as $id => $quantity) {
                $summary['variants'][$id]->decrement('stock', $quantity);
            }
            foreach ($summary['lines'] as $line) {
                $order->items()->create([
                    'variant_id' => $line['variant_id'], 'product_name' => $line['product']->name,
                    'color' => $line['variant']->color, 'size' => $line['variant']->size,
                    'unit_price' => $line['unit_price'], 'quantity' => $line['quantity'],
                    'design_mode' => $line['design_mode'], 'placement' => $line['placement'],
                    'idea' => $line['idea'], 'upload_path' => $line['upload_path'],
                    'design_status' => $line['design_mode'] === 'ready' ? null : 'review',
                ]);
            }
            $order->addEvent('استلمنا طلبك.');

            return $order;
        }, 3);

        $cart->clear();
        TrackingController::authorizeOrder($request, $order);

        if ($order->email && config('vibe.email_confirmations')) {
            try {
                Mail::to($order->email)->send(new OrderConfirmation($order));
            } catch (Throwable $error) {
                report($error);
            }
        }

        return redirect()->route('tracking.show', $order->number)->with('placed', true);
    }

    private function transferEnabled(): bool
    {
        return (filled(config('vibe.transfer_recipient')) && filled(config('vibe.transfer_details'))) || app()->environment(['local', 'testing']);
    }

    private function depositPercent(): int
    {
        return max(1, min(100, config('vibe.deposit_percent')));
    }

    public static function phone(string $phone): string
    {
        $phone = strtr($phone, array_combine(preg_split('//u', '٠١٢٣٤٥٦٧٨٩', -1, PREG_SPLIT_NO_EMPTY), range(0, 9)));
        $phone = preg_replace('/[\s()\-]+/u', '', $phone) ?? '';

        return preg_replace('/^(?:\+20|0020|20)(?=1[0125][0-9]{8}$)/', '0', $phone) ?? '';
    }
}
