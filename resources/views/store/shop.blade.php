@extends('layouts.store')
@section('title', 'المتجر')
@section('content')
<div class="wrap">
    <div class="page-intro">
        <p class="micro accent"><span class="square"></span> THE COLLECTION</p>
        <h1 class="display" lang="en">SHOP THE DROP</h1>
        <p dir="rtl">اختار من التيشيرتات والهوديز، وخلي القطعة على ستايلك.</p>
    </div>
    <nav class="filters" aria-label="فئات المنتجات" lang="en">
        <a class="{{ !$category ? 'selected' : '' }}" href="{{ route('shop') }}">All pieces</a>
        @foreach(config('vibe.categories') as $key => $label)
            <a class="{{ $key === $category ? 'selected' : '' }}" href="{{ route('shop', ['category' => $key]) }}">{{ $label }}</a>
        @endforeach
    </nav>
    @if($products->count())
        <div class="product-grid">@foreach($products as $product)<x-product-card :product="$product" />@endforeach</div>
        <x-pagination :paginator="$products" />
    @else
        <div class="empty" dir="rtl"><h2>مفيش قطع في القسم ده حاليًا.</h2><a class="btn primary" href="{{ route('shop') }}">شوف باقي المنتجات</a></div>
    @endif
</div>
@endsection
