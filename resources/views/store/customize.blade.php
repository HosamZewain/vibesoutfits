@extends('layouts.store')
@section('title', 'صمّم قطعتك')
@section('content')
<div class="wrap">
    <div class="page-intro custom-intro">
        <p class="micro accent"><span class="square"></span> THE CUSTOM STUDIO</p>
        <h1 class="display" lang="en">CREATE YOUR VIBE</h1>
        <p dir="rtl">اختار القطعة اللي هتطبع عليها، وارفع صورة أو اكتب فكرتك. هتشوف المعاينة وتوافق عليها قبل الطباعة.</p>
        <x-icon name="spark" class="intro-spark" />
    </div>
    <div class="section-heading mini-heading"><h2 dir="rtl">اختار القطعة</h2><span class="micro accent" lang="en">STEP 01 / YOUR BASE</span></div>
    @if($products->isNotEmpty())
        <div class="product-grid">@foreach($products as $product)<x-product-card :product="$product" :custom="true" />@endforeach</div>
    @else
        <div class="empty" dir="rtl"><h2>التخصيص مش متاح حاليًا.</h2><a class="btn primary" href="{{ route('shop') }}">تسوّق الكوليكشن</a></div>
    @endif
</div>
@endsection
