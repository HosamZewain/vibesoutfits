<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\ShippingZone;
use App\Models\Variant;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\File;
use Illuminate\Validation\ValidationException;
use Throwable;

class CatalogAdminController extends Controller
{
    public function products(): View
    {
        $products = Product::withSum(['variants as active_stock' => fn ($query) => $query->where('active', true)], 'stock')->latest('id')->paginate(15);

        return view('admin.products', compact('products'));
    }

    public function edit(?Product $product = null): View
    {
        $product?->load('variants');

        return view('admin.product', compact('product'));
    }

    public function save(Request $request, ?Product $product = null): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:90'],
            'category' => ['required', Rule::in(array_keys(config('vibe.categories')))],
            'description' => ['required', 'string', 'max:2000'],
            'price' => ['required', 'integer', 'min:1', 'max:100000'],
            'custom_fee' => ['required', 'integer', 'min:0', 'max:100000'],
            'badge' => ['nullable', 'string', 'max:30'],
            'image' => ['nullable', File::types(['jpg', 'jpeg', 'png', 'webp'])->max('6mb')],
            'active' => ['sometimes', 'boolean'], 'featured' => ['sometimes', 'boolean'],
            'ready_enabled' => ['sometimes', 'boolean'], 'custom_enabled' => ['sometimes', 'boolean'],
        ]);
        if (! $request->boolean('ready_enabled') && ! $request->boolean('custom_enabled')) {
            throw ValidationException::withMessages(['ready_enabled' => 'اختار تصميم جاهز أو تخصيص للمنتج.']);
        }
        $image = isset($data['image']) ? $data['image']->store('products', 'local') : null;
        unset($data['image']);
        if ($image === false) {
            throw ValidationException::withMessages(['image' => 'رفع الصورة ماكملش.']);
        }
        foreach (['active', 'featured', 'ready_enabled', 'custom_enabled'] as $flag) {
            $data[$flag] = $request->boolean($flag);
        }

        try {
            [$product, $old] = DB::transaction(function () use ($product, $data, $image): array {
                $record = $product ? Product::lockForUpdate()->findOrFail($product->id) : new Product;
                $old = $record->image_path;
                if (! $record->exists) {
                    $record->slug = (Str::slug($data['name']) ?: 'vibe').'-'.Str::lower(Str::random(7));
                }
                if ($image) {
                    $record->image_path = $image;
                }
                $record->fill($data)->save();

                return [$record, $old];
            }, 3);
        } catch (Throwable $error) {
            if ($image) {
                Storage::disk('local')->delete($image);
            }
            throw $error;
        }
        if ($image && $old && str_starts_with($old, 'products/')) {
            Storage::disk('local')->delete($old);
        }

        return redirect()->route('admin.products.edit', $product)->with('success', 'تم حفظ بيانات المنتج.');
    }

    public function addVariant(Request $request, Product $product): RedirectResponse
    {
        $data = $request->validate([
            'color' => ['required', Rule::in(array_keys(config('vibe.colors')))],
            'size' => ['required', Rule::in(config('vibe.sizes'))],
            'stock' => ['required', 'integer', 'min:0', 'max:100000'],
        ]);
        DB::transaction(function () use ($product, $data): void {
            $product = Product::lockForUpdate()->findOrFail($product->id);
            if ($product->variants()->where('color', $data['color'])->where('size', $data['size'])->exists()) {
                throw ValidationException::withMessages(['color' => 'اللون والمقاس دول موجودين بالفعل.']);
            }
            $product->variants()->create($data + ['active' => true]);
        }, 3);

        return back()->with('success', 'تم إضافة الاختيار للمخزون.');
    }

    public function updateVariant(Request $request, Product $product, Variant $variant): RedirectResponse
    {
        abort_unless($variant->product_id === $product->id, 404);
        $data = $request->validate(['stock' => ['required', 'integer', 'min:0', 'max:100000'], 'current_stock' => ['required', 'integer', 'min:0'], 'active' => ['required', 'boolean']]);
        DB::transaction(function () use ($variant, $data): void {
            $current = Variant::lockForUpdate()->findOrFail($variant->id);
            if ($current->stock !== (int) $data['current_stock']) {
                throw ValidationException::withMessages(['stock' => 'المخزون اتغير من وقت ما فتحت الصفحة. راجع الرقم الحالي.']);
            }
            $current->update(['stock' => $data['stock'], 'active' => $data['active']]);
        }, 3);

        return back()->with('success', 'تم تحديث المخزون.');
    }

    public function shipping(): View
    {
        return view('admin.shipping', ['zones' => ShippingZone::orderBy('id')->get()]);
    }

    public function updateShipping(Request $request, ShippingZone $zone): RedirectResponse
    {
        $zone->update($request->validate(['fee' => ['required', 'integer', 'min:0', 'max:10000'], 'active' => ['required', 'boolean']]));

        return back()->with('success', 'تم تحديث شحن '.$zone->name.'.');
    }
}
