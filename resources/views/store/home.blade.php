@extends('layouts.store')

@section('content')
<section class="hero" aria-labelledby="hero-title">
    <picture class="hero-photo"><img src="{{ asset('assets/img/campaign.jpg') }}" width="1536" height="1024" fetchpriority="high" alt="شاب بتيشيرت أسود بطباعة حمرا وبنت بهودي نبيتي في شارع بالقاهرة"></picture>
    <div class="hero-shade"></div>
    <div class="wrap hero-inner">
        <div class="hero-content">
            <p class="hero-eyebrow" lang="en"><span></span> DROP 01 <i>/</i> CAIRO, EGYPT</p>
            <h1 id="hero-title" lang="en">YOUR VIBE,<br><em>YOUR CHOICE</em></h1>
            <p class="hero-desc" dir="rtl">اختار قطعة من الكوليكشن، أو حوّل فكرتك لتصميم تلبسه.</p>
            <div class="hero-actions">
                <a class="btn primary" href="{{ route('shop') }}" lang="en">SHOP THE DROP <x-icon name="up-right" width="17" height="17" /></a>
                <a class="btn outline" href="{{ route('customize') }}" lang="en"><x-icon name="spark" width="17" height="17" /> CREATE YOUR VIBE</a>
            </div>
        </div>
        <div class="hero-index" lang="en"><i>01</i><span></span> THE FIRST DROP</div>
    </div>
</section>
<div class="vibe-tape" aria-label="ستريت وير. يتعمل مخصوص ليك. من مصر.">
    <div class="tape-content" lang="en" aria-hidden="true">
        @for($i=0;$i<3;$i++)
            <x-icon name="spark" width="18" height="18" /><span>STREETWEAR</span><x-icon name="spark" width="18" height="18" /><span>MADE TO ORDER</span><x-icon name="spark" width="18" height="18" /><span>DESIGNED BY YOU</span><x-icon name="spark" width="18" height="18" /><span>FROM CAIRO</span>
        @endfor
    </div>
</div>
<section class="wrap section-space" aria-labelledby="drop-title">
    <div class="section-heading">
        <div><p class="micro accent"><span class="square"></span> THE COLLECTION / 01</p><h2 id="drop-title" class="display" lang="en">THE FIRST DROP</h2></div>
        <a class="text-link" href="{{ route('shop') }}"><span>شوف كل المنتجات</span> <x-icon name="up-right" width="16" height="16" /></a>
    </div>
    @if($products->isNotEmpty())
        <div class="product-grid">@foreach($products as $product)<x-product-card :product="$product" />@endforeach</div>
    @else
        <div class="empty" dir="rtl">القطع الجديدة هتنزل قريب.</div>
    @endif
</section>
<section class="wrap section-space custom-section" id="how-it-works">
    <div class="custom-banner">
        <div class="custom-graphic" aria-hidden="true"><x-icon name="spark" /><span>VIBES<br>STUDIO</span></div>
        <div class="custom-copy" dir="rtl">
            <p class="micro accent" dir="ltr">THE CUSTOM STUDIO / 02</p>
            <h2 class="display" lang="en" dir="ltr">MAKE IT YOURS</h2>
            <p>ارفع صورة أو احكيلنا الفكرة. نجهّز لك معاينة، وبعد موافقتك نطبعها على القطعة اللي اخترتها.</p>
            <a class="btn primary" href="{{ route('customize') }}">ابدأ التصميم <x-icon name="up-right" width="17" height="17" /></a>
        </div>
    </div>
    <div class="how-grid" dir="rtl">
        <div><span>01 /</span><h3>اختار القطعة</h3><p>تيشيرت أو هودي، بالمقاس واللون اللي يناسبوك.</p></div>
        <div><span>02 /</span><h3>ابعت فكرتك</h3><p>ارفع التصميم أو اكتب وصفه، وحدد مكان الطباعة.</p></div>
        <div><span>03 /</span><h3>راجع المعاينة</h3><p>وافق على التصميم قبل الطباعة وتابع شحن طلبك من الموقع.</p></div>
    </div>
</section>
@endsection
