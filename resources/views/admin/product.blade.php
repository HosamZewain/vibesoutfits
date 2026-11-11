@extends('layouts.admin')
@section('title', $product ? 'تعديل المنتج' : 'منتج جديد')
@section('content')
<div class="admin-page-head"><div><a href="{{ route('admin.products') }}" class="table-link">المنتجات والمخزون ↗</a><h1>{{ $product ? 'تعديل المنتج' : 'منتج جديد' }}</h1><p>البيانات اللي هتظهر للعملاء في الكوليكشن.</p></div>@if($product?->active)<a class="btn subtle" href="{{ route('product', ['product' => $product->slug]) }}">شوف في المتجر <x-icon name="up-right" width="16" height="16" /></a>@endif</div>
<div class="admin-columns order-columns">
    <section class="admin-panel">
        <header><h2>بيانات المنتج</h2></header>
        <form class="admin-pad fields" method="post" action="{{ $product ? route('admin.products.update', $product) : route('admin.products.store') }}" enctype="multipart/form-data">
            @csrf @if($product) @method('PATCH') @endif
            <input type="hidden" name="editing_product" value="1">
            <div class="field-row">
                <div class="field"><label for="name">اسم المنتج</label><input id="name" name="name" value="{{ old('name', $product?->name) }}" maxlength="90" required></div>
                <div class="field"><label for="category">الفئة</label><select id="category" name="category">@foreach(config('vibe.categories') as $key => $label)<option value="{{ $key }}" @selected(old('category', $product?->category) === $key)>{{ $label }}</option>@endforeach</select></div>
            </div>
            <div class="field"><label for="description">الوصف</label><textarea id="description" name="description" rows="3" maxlength="2000" required>{{ old('description', $product?->description) }}</textarea></div>
            <div class="field-row">
                <div class="field"><label for="price">السعر الأساسي (ج.م.)</label><input id="price" name="price" type="number" min="1" max="100000" value="{{ old('price', $product?->price) }}" required></div>
                <div class="field"><label for="custom_fee">إضافة التخصيص (ج.م.)</label><input id="custom_fee" name="custom_fee" type="number" min="0" max="100000" value="{{ old('custom_fee', $product?->custom_fee ?? 0) }}" required></div>
            </div>
            <div class="field"><label for="badge">شارة المنتج <small>اختياري</small></label><input id="badge" name="badge" maxlength="30" value="{{ old('badge', $product?->badge) }}" placeholder="DROP 01"></div>
            <div class="field"><label for="image">صورة المنتج <small>حتى ٦ ميجابايت</small></label><input id="image" name="image" type="file" accept="image/jpeg,image/png,image/webp"></div>
            <div class="check-grid">
                @foreach(['active' => 'ظاهر في المتجر', 'featured' => 'مميز في الرئيسية', 'ready_enabled' => 'تصميم جاهز', 'custom_enabled' => 'يسمح بالتخصيص'] as $flag => $label)
                    <label><input type="checkbox" name="{{ $flag }}" value="1" @checked(old('editing_product') ? old($flag) : ($product ? $product->$flag : $flag !== 'featured'))>{{ $label }}</label>
                @endforeach
            </div>
            <button type="submit" class="btn primary">حفظ المنتج</button>
        </form>
    </section>
    <aside class="admin-panel">
        <header><h2>الصورة الحالية</h2></header>
        <img class="admin-product-image" src="{{ $product?->imageUrl() ?? asset('assets/img/placeholder.svg') }}" alt="صورة المنتج">
    </aside>
</div>
@if($product)
    <section class="admin-panel" id="stock">
        <header><h2>المقاسات والألوان والمخزون</h2><span class="small muted">الكمية المتاحة للحجز</span></header>
        <form class="admin-filters" method="post" action="{{ route('admin.variants.store', $product) }}">
            @csrf
            <div class="field"><label for="color">اللون</label><select id="color" name="color">@foreach(config('vibe.colors') as $key => $color)<option value="{{ $key }}">{{ $color[0] }}</option>@endforeach</select></div>
            <div class="field"><label for="size">المقاس</label><select id="size" name="size">@foreach(config('vibe.sizes') as $size)<option>{{ $size }}</option>@endforeach</select></div>
            <div class="field"><label for="stock-add">الكمية</label><input id="stock-add" name="stock" type="number" min="0" max="100000" value="0" required></div>
            <button type="submit" class="btn primary">أضف اختيار</button>
        </form>
        @if($product->variants->isNotEmpty())
            <div class="admin-pad variant-list">
                @foreach($product->variants as $variant)
                    <form class="variant-row" method="post" action="{{ route('admin.variants.update', [$product, $variant]) }}">
                        @csrf @method('PATCH')<input name="current_stock" type="hidden" value="{{ $variant->stock }}">
                        <span><i class="color-dot" style="--swatch:{{ config('vibe.colors.'.$variant->color.'.1') }}"></i> {{ config('vibe.colors.'.$variant->color.'.0') }} · <bdi>{{ $variant->size }}</bdi></span>
                        <div class="field"><label for="stock-{{ $variant->id }}" class="sr-only">الكمية</label><input id="stock-{{ $variant->id }}" name="stock" type="number" min="0" max="100000" value="{{ $variant->stock }}" required></div>
                        <label class="check-label"><input type="hidden" name="active" value="0"><input type="checkbox" name="active" value="1" @checked($variant->active)>متاح</label>
                        <button class="btn subtle compact" type="submit">حفظ</button>
                    </form>
                @endforeach
            </div>
        @endif
    </section>
@endif
@endsection
