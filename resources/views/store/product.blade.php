@extends('layouts.store')
@section('title', $product->name)
@section('content')
@php
    $colorKeys = $product->variants->pluck('color')->unique()->values();
    $sizeKeys = array_values(array_intersect(config('vibe.sizes'), $product->variants->pluck('size')->unique()->all()));
    $mode = old('design_mode', $customize || ! $product->ready_enabled ? 'idea' : 'ready');
@endphp
<div class="wrap">
    <nav class="breadcrumb" aria-label="مسار الصفحة"><a href="{{ route('home') }}">الرئيسية</a><span>/</span><a href="{{ route('shop') }}">المتجر</a><span>/</span><b lang="en">{{ $product->name }}</b></nav>
    <div class="product-detail">
        <div class="detail-media">
            <img src="{{ $product->imageUrl() }}" width="512" height="512" alt="{{ $product->name }}">
            @if($product->badge)<span class="product-badge">{{ $product->badge }}</span>@endif
            <div class="media-caption" lang="en"><span>{{ config('app.name') }}</span><span>MADE TO ORDER <x-icon name="spark" width="13" height="13" /></span></div>
        </div>
        <section class="detail-content" dir="rtl">
            <div class="detail-name" dir="ltr">
                <p class="micro accent">{{ config('vibe.categories.'.$product->category) }}</p>
                <h1 class="display" lang="en">{{ $product->name }}</h1>
                <div class="detail-price" lang="en"><span data-product-price>{{ number_format($product->price + ($mode === 'ready' ? 0 : $product->custom_fee)) }}</span> <small>EGP</small></div>
            </div>
            <p class="detail-description">{{ $product->description }}</p>
            @if($colorKeys->isEmpty())
                <div class="empty"><p>القطعة دي خلصت حاليًا.</p><a class="btn primary" href="{{ route('shop') }}">شوف باقي الكوليكشن</a></div>
            @else
                <form method="post" action="{{ route('cart.add', ['product' => $product->slug]) }}" enctype="multipart/form-data" class="fields" data-product-form data-base="{{ $product->price }}" data-custom="{{ $product->custom_fee }}" data-variants='@json($product->variants->map->only(["color", "size", "stock"])->values())'>
                    @csrf
                    <fieldset><legend>اللون</legend><div class="choices">
                        @foreach($colorKeys as $color)
                            <label class="choice"><input type="radio" name="color" value="{{ $color }}" required @checked(old('color', $colorKeys->first()) === $color)><span><i class="color-dot" style="--swatch:{{ config('vibe.colors.'.$color.'.1') }}"></i>{{ config('vibe.colors.'.$color.'.0') }}</span></label>
                        @endforeach
                    </div></fieldset>
                    <fieldset><legend>المقاس</legend><div class="choices">
                        @foreach($sizeKeys as $size)
                            <label class="choice size-choice"><input type="radio" name="size" value="{{ $size }}" required @checked(old('size') === $size)><span lang="en">{{ $size }}</span></label>
                        @endforeach
                    </div></fieldset>
                    <fieldset><legend>نوع التصميم</legend><div class="choices">
                        @foreach(config('vibe.modes') as $value => $label)
                            @continue($value === 'ready' ? ! $product->ready_enabled : ! $product->custom_enabled)
                            <label class="choice"><input type="radio" name="design_mode" value="{{ $value }}" required @checked($mode === $value)><span>{{ $label }}</span></label>
                        @endforeach
                    </div></fieldset>
                    @if($product->custom_enabled)
                        <div class="field file-field" data-design-panel="upload"><label for="artwork"><x-icon name="upload" width="18" height="18" /> ارفع تصميمك</label><input type="file" id="artwork" name="artwork" accept="image/jpeg,image/png,image/webp"><small>JPG, PNG أو WebP حتى ١٠ ميجابايت.</small></div>
                        <div class="field" data-design-panel="idea"><label for="idea">احكيلنا الفكرة</label><textarea name="idea" id="idea" maxlength="2000" minlength="8" rows="4" placeholder="مثلاً: رسمة قمر بالأحمر وخطوط بسيطة…">{{ old('idea') }}</textarea><small>هنجهّز معاينة تشوفها وتوافق عليها قبل الطباعة.</small></div>
                    @endif
                    <div class="field-row">
                        <div class="field"><label for="placement">مكان الطباعة</label><select id="placement" name="placement">@foreach(config('vibe.placements') as $value => $label)<option value="{{ $value }}" @selected(old('placement') === $value)>{{ $label }}</option>@endforeach</select></div>
                        <div class="field"><label for="quantity">الكمية</label><input id="quantity" name="quantity" type="number" min="1" max="10" value="{{ old('quantity', 1) }}" required></div>
                    </div>
                    <button class="btn primary full" type="submit"><x-icon name="bag" /> أضف للسلة <span dir="ltr" lang="en"><span data-line-price>{{ number_format($product->price + ($mode === 'ready' ? 0 : $product->custom_fee)) }}</span> EGP</span></button>
                    <p class="small muted">بتتطبع مخصوص ليك. الشحن بيتحسب حسب المحافظة.</p>
                </form>
            @endif
        </section>
    </div>
</div>
@endsection
