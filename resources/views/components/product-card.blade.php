@props(['product', 'custom' => false])
@php
    $url = route('product', ['product' => $product->slug] + ($custom ? ['design' => 'custom'] : []));
    $available = $product->variants->where('active', true)->where('stock', '>', 0);
    $photos = $product->catalogPhotos();
    $colors = $photos ? array_unique(array_column($photos, 1)) : $available->pluck('color')->unique();
    $sizes = array_values(array_intersect(config('vibe.sizes'), $available->pluck('size')->unique()->all()));
    $price = $product->price + (($custom || ! $product->ready_enabled) ? $product->custom_fee : 0);
@endphp
<article class="product-card">
    <a href="{{ $url }}" @class(['product-media', 'catalog-media' => $photos]) tabindex="-1" aria-hidden="true">
        <img src="{{ $product->imageUrl() }}" alt="" width="512" height="512" loading="lazy">
        @if($product->badge)<span class="product-badge">{{ $product->badge }}</span>@endif
        <span class="product-arrow"><x-icon name="up-right" width="17" height="17" /></span>
    </a>
    <div class="product-meta" lang="en">
        <span class="micro muted">{{ config('vibe.categories.'.$product->category) }}</span>
        <h3><a href="{{ $url }}">{{ $product->name }}</a></h3>
        <div class="product-bottom">
            <strong>{{ number_format($price) }} <small>EGP</small></strong>
            <div class="swatches" aria-label="ألوان المنتج">
                @foreach($colors as $color)<span style="--swatch:{{ config('vibe.colors.'.$color.'.1') }}" title="{{ config('vibe.colors.'.$color.'.0') }}"></span>@endforeach
            </div>
        </div>
        @if($sizes)<p class="size-caption">{{ implode(' / ', $sizes) }}</p>@else<p class="size-caption" lang="ar">غير متاح للطلب حاليًا</p>@endif
    </div>
</article>
