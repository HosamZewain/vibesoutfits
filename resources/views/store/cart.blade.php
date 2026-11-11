@extends('layouts.store')
@section('title', 'السلة')
@section('content')
<div class="wrap">
    <div class="page-intro"><p class="micro accent"><span class="square"></span> STEP 01 / BAG</p><h1 class="display" lang="en">YOUR BAG</h1><p dir="rtl">راجع القطع اللي اخترتها قبل ما تكمل.</p></div>
    @if(!$lines)
        <div class="empty" dir="rtl"><x-icon name="bag" width="32" height="32" /><h2>سلتك فاضية</h2><p>اختار من الكوليكشن أو اعمل تصميمك الخاص.</p><a class="btn primary" href="{{ route('shop') }}">ابدأ التسوق</a></div>
    @else
        <div class="commerce-grid" dir="rtl">
            <section class="panel cart-lines" aria-label="قطع السلة">
                @foreach($lines as $key => $line)
                    <article class="cart-item">
                        <img class="thumb" src="{{ $line['product']?->imageUrl() ?? asset('assets/img/placeholder.svg') }}" alt="" width="88" height="88">
                        <div class="line-info">
                            <h2><bdi>{{ $line['product']?->name ?? $line['product_name'] }}</bdi></h2>
                            @if($line['variant'])<p class="muted">{{ config('vibe.colors.'.$line['variant']->color.'.0') }} · <bdi>{{ $line['variant']->size }}</bdi> · طباعة {{ config('vibe.placements.'.$line['placement']) }}</p>@endif
                            <p class="small">{{ config('vibe.modes.'.$line['design_mode']) }} @if($line['upload_path']) · ملف مرفق @endif</p>
                            @if(!$line['available'])<p class="warning">الكمية أو الاختيار ده مش متاح. حدّث الكمية أو شيل القطعة.</p>@endif
                            <div class="inline-actions">
                                <form method="post" action="{{ route('cart.update', $key) }}">@csrf @method('PATCH')<label class="sr-only" for="qty-{{ $key }}">الكمية</label><input id="qty-{{ $key }}" type="number" name="quantity" value="{{ $line['quantity'] }}" min="1" max="10" required><button class="btn subtle compact" type="submit">تحديث</button></form>
                                <form method="post" action="{{ route('cart.update', $key) }}">@csrf @method('PATCH')<input type="hidden" name="quantity" value="0"><button type="submit" class="link-button">إزالة</button></form>
                            </div>
                        </div>
                        <strong class="line-total">{{ number_format($line['line_total']) }} ج.م.</strong>
                    </article>
                @endforeach
            </section>
            <aside class="panel summary">
                <h2>ملخص السلة</h2>
                <dl class="totals"><div><dt>إجمالي القطع</dt><dd>{{ number_format($subtotal) }} ج.م.</dd></div><div><dt>الشحن</dt><dd class="muted">حسب المحافظة</dd></div></dl>
                @if($valid)<a class="btn primary full" href="{{ route('checkout') }}">كمّل الطلب <x-icon name="arrow" class="mirror" /></a>@endif
                <a class="btn subtle full" href="{{ route('shop') }}">كمّل التسوق</a>
            </aside>
        </div>
    @endif
</div>
@endsection
