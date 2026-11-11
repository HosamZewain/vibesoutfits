<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Variant;
use App\Services\Cart;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\File;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Throwable;

class StoreController extends Controller
{
    public function home(): View
    {
        $products = Product::where('active', true)->where('featured', true)->with('variants')->limit(3)->get();

        return view('store.home', compact('products'));
    }

    public function shop(Request $request): View
    {
        $category = $request->query('category');
        $category = is_string($category) && array_key_exists($category, config('vibe.categories')) ? $category : null;
        $products = Product::where('active', true)->when($category, fn ($query) => $query->where('category', $category))
            ->with('variants')->orderByDesc('featured')->orderBy('id')->paginate(12)->withQueryString();

        return view('store.shop', compact('products', 'category'));
    }

    public function customize(): View
    {
        $products = Product::where('active', true)->where('custom_enabled', true)->with('variants')->orderBy('id')->get();

        return view('store.customize', compact('products'));
    }

    public function product(Request $request, Product $product): View
    {
        abort_unless($product->active, 404);
        $product->load(['variants' => fn ($query) => $query->where('active', true)->where('stock', '>', 0)]);
        $customize = $request->query('design') === 'custom' && $product->custom_enabled;

        return view('store.product', compact('product', 'customize'));
    }

    public function image(Request $request, Product $product): StreamedResponse
    {
        abort_unless(($product->active || $request->user()?->is_admin) && $product->image_path && str_starts_with($product->image_path, 'products/'), 404);

        $disk = Storage::disk('local');
        abort_unless($disk->exists($product->image_path), 404);

        return $disk->response($product->image_path, null, ['X-Content-Type-Options' => 'nosniff']);
    }

    public function add(Request $request, Product $product, Cart $cart): RedirectResponse
    {
        abort_unless($product->active, 404);
        $data = $request->validate([
            'color' => ['required', Rule::in(array_keys(config('vibe.colors')))],
            'size' => ['required', Rule::in(config('vibe.sizes'))],
            'quantity' => ['required', 'integer', 'min:1', 'max:10'],
            'design_mode' => ['required', Rule::in(array_keys(config('vibe.modes')))],
            'placement' => ['required', Rule::in(array_keys(config('vibe.placements')))],
            'artwork' => ['exclude_unless:design_mode,upload', 'required', File::types(['jpg', 'jpeg', 'png', 'webp'])->max('10mb')],
            'idea' => ['exclude_unless:design_mode,idea', 'required', 'string', 'min:8', 'max:2000'],
        ]);
        if ($data['design_mode'] === 'ready' ? ! $product->ready_enabled : ! $product->custom_enabled) {
            Cart::fail('نوع التصميم ده مش متاح للقطعة دي.');
        }

        $variant = Variant::whereBelongsTo($product)->where('active', true)->where('color', $data['color'])->where('size', $data['size'])->first();
        $reservedInCart = array_sum(array_map(fn (array $line) => $line['variant_id'] === $variant?->id ? $line['quantity'] : 0, $cart->raw()));
        if (! $variant || $variant->stock < $reservedInCart + $data['quantity']) {
            Cart::fail('اللون والمقاس والكمية اللي اخترتهم مش متاحين حاليًا.');
        }

        $path = isset($data['artwork']) ? $data['artwork']->store('customer-artwork', 'local') : null;
        if ($path === false) {
            Cart::fail('رفع التصميم ماكملش. حاول مرة تانية.');
        }

        try {
            $cart->add([
                'variant_id' => $variant->id, 'product_name' => $product->name, 'quantity' => (int) $data['quantity'],
                'design_mode' => $data['design_mode'], 'placement' => $data['placement'],
                'idea' => $data['idea'] ?? null, 'upload_path' => $path,
            ]);
        } catch (Throwable $error) {
            if ($path) {
                Storage::disk('local')->delete($path);
            }
            throw $error;
        }

        return redirect()->route('cart')->with('success', 'القطعة اتضافت للسلة.');
    }

    public function cart(Cart $cart): View
    {
        return view('store.cart', $cart->summarize());
    }

    public function updateCart(Request $request, string $key, Cart $cart): RedirectResponse
    {
        $data = $request->validate(['quantity' => ['required', 'integer', 'min:0', 'max:10']]);
        $cart->update($key, (int) $data['quantity']);

        return redirect()->route('cart')->with('success', $data['quantity'] ? 'تم تحديث السلة.' : 'القطعة اتشالت من السلة.');
    }
}
