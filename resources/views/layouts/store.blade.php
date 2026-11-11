<!doctype html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="theme-color" content="#151414">
    <meta name="referrer" content="same-origin">
    <meta name="description" content="{{ config('app.name') }} — Egyptian streetwear, made to order. اختار من الكوليكشن أو اعمل تصميمك.">
    @hasSection('title')
        <title>@yield('title') | {{ config('app.name') }}</title>
    @else
        <title>{{ config('app.name') }} — Your Vibe, Your Choice</title>
    @endif
    <link rel="icon" href="{{ asset('assets/img/mark.svg') }}" type="image/svg+xml">
    <link rel="stylesheet" href="{{ asset('assets/fonts/fonts.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/store.css') }}?v=catalog-2">
    <script src="{{ asset('assets/store.js') }}?v=catalog-1" defer></script>
</head>
<body>
<a href="#main" class="skip-link" dir="rtl">انتقل للمحتوى</a>
<div class="topstrip" lang="en" dir="ltr"><x-icon name="spark" width="11" height="11" /> DROP 01 IS LIVE <span></span> MADE TO ORDER IN EGYPT</div>
<header class="site-header">
    <div class="wrap nav">
        <x-brand />
        <nav class="nav-links" id="nav-links" aria-label="القائمة الرئيسية">
            <a class="{{ request()->routeIs('home') ? 'current' : '' }}" href="{{ route('home') }}">الرئيسية</a>
            <a class="{{ request()->routeIs('shop', 'product') ? 'current' : '' }}" href="{{ route('shop') }}">المتجر</a>
            <a class="{{ request()->routeIs('customize') ? 'current' : '' }}" href="{{ route('customize') }}">صمّم قطعتك <x-icon name="spark" width="12" height="12" /></a>
        </nav>
        <div class="nav-tools">
            <a class="track-link" href="{{ route('tracking') }}">تتبّع طلبك</a>
            @php($count = array_sum(array_column(session('vibe.cart', []), 'quantity')))
            <a href="{{ route('cart') }}" class="cart-link" aria-label="السلة، {{ $count }} قطعة"><x-icon name="bag" /> <span>{{ $count }}</span></a>
            <button class="menu-toggle" type="button" aria-label="القائمة" aria-expanded="false" aria-controls="nav-links"><x-icon name="menu" /></button>
        </div>
    </div>
</header>
<main id="main" @unless(request()->routeIs('home')) class="page-main" @endunless>
    @if(session('success') || $errors->any())<div class="wrap"><x-messages /></div>@endif
    @yield('content')
</main>
<footer class="site-footer">
    <div class="wrap footer-grid">
        <div><x-brand :stacked="true" /><p lang="en">Your Vibe, Your Choice</p></div>
        <div class="footer-links" dir="rtl"><b>تسوّق</b><a href="{{ route('shop') }}">كل المنتجات</a><a href="{{ route('customize') }}">صمّم قطعتك</a><a href="{{ route('tracking') }}">تتبّع طلبك</a></div>
        @if(collect(config('vibe.social'))->filter()->isNotEmpty())
            <div class="footer-links" dir="rtl"><b>تابعنا</b>@foreach(array_filter(config('vibe.social')) as $label => $url)<a href="{{ $url }}" rel="noopener noreferrer" target="_blank">{{ $label }} ↗</a>@endforeach</div>
        @endif
        <div class="footer-end" lang="en">INDEPENDENT STREETWEAR<br>MADE TO ORDER • EGYPT</div>
    </div>
    <div class="wrap footer-bottom" lang="en"><span dir="ltr">© {{ date('Y') }} {{ config('app.name') }}</span><span dir="ltr">WEAR YOUR VIBE <x-icon name="spark" width="13" height="13" /></span></div>
</footer>
</body>
</html>
