@extends('layouts.store')
@section('title', 'طلب '.$order->number)
@section('content')
<div class="wrap">
    <div class="page-intro"><p class="micro accent"><span class="square"></span> {{ session('placed') ? 'ORDER RECEIVED' : 'ORDER TRACKING' }}</p><h1 class="display order-heading" lang="en">{{ $order->number }}</h1><p dir="rtl">{{ session('placed') ? 'استلمنا طلبك! احتفظ برقم الطلب عشان تتابعه بالموبايل.' : 'آخر تحديث لطلبك.' }}</p></div>
    <div class="commerce-grid" dir="rtl">
        <div class="fields">
            <section class="panel fields">
                <div class="split"><h2>حالة الطلب</h2><span class="pill">{{ $order->label() }}</span></div>
                @if($order->status !== 'cancelled')
                    @php($steps = ['new' => 'الطلب', 'production' => 'الإنتاج', 'shipped' => 'الشحن', 'delivered' => 'التسليم'])
                    <ol class="steps">
                        @foreach($steps as $key => $label)<li @class(['done' => array_search($key, array_keys($steps), true) <= array_search($order->status, array_keys($steps), true)])><span>{{ $loop->iteration }}</span>{{ $label }}</li>@endforeach
                    </ol>
                @endif
                @if($order->carrier)<p>شركة الشحن: <strong>{{ $order->carrier }}</strong> · رقم الشحنة: <strong><bdi>{{ $order->shipment_code }}</bdi></strong></p>@endif
            </section>
            @if($order->status === 'new' && $order->paid_amount < $order->deposit_due)
                <section class="panel transfer-note">
                    <h2>كمّل التحويل عشان نأكد الطلب</h2>
                    <p>المتبقي من الدفعة الأولى <strong>{{ number_format($order->deposit_due - $order->paid_amount) }} ج.م.</strong></p>
                    @if(config('vibe.transfer_recipient') && config('vibe.transfer_details'))
                        <p>إلى: <strong><bdi>{{ config('vibe.transfer_recipient') }}</bdi></strong></p><p class="preline">{{ config('vibe.transfer_details') }}</p>
                    @else
                        <p>التحويل تجريبي على النسخة المحلية. تسجيل المقدم من لوحة الإدارة.</p>
                    @endif
                    <small>الحالة بتتحدّث بعد مراجعة التحويل.</small>
                </section>
            @endif
            <section class="panel fields">
                <h2>القطع والتصميمات</h2>
                @foreach($order->items as $item)
                    @php($params = ['order' => $order->number, 'item' => $item->id])
                    <article class="tracking-item">
                        <div class="split"><h3><bdi>{{ $item->product_name }}</bdi> × {{ $item->quantity }}</h3>@if($item->design_status)<span class="pill">{{ config('vibe.design_statuses.'.$item->design_status) }}</span>@endif</div>
                        <p class="muted small">{{ config('vibe.colors.'.$item->color.'.0') }} · <bdi>{{ $item->size }}</bdi> · {{ config('vibe.modes.'.$item->design_mode) }} · طباعة {{ config('vibe.placements.'.$item->placement) }}</p>
                        @if($item->preview_path)
                            <figure class="preview"><img src="{{ route('tracking.preview', $params + ['v' => $item->preview_version]) }}" alt="معاينة تصميم {{ $item->product_name }}" loading="lazy"><figcaption>المعاينة رقم {{ $item->preview_version }}</figcaption></figure>
                        @endif
                        @if($item->design_status === 'preview' && $order->status === 'new')
                            <div class="fields">
                                <form method="post" action="{{ route('tracking.review', $params) }}">
                                    @csrf<input type="hidden" name="version" value="{{ $item->preview_version }}"><input type="hidden" name="decision" value="approve">
                                    <button class="btn primary" type="submit"><x-icon name="check" /> أوافق على التصميم للطباعة</button>
                                </form>
                                <details><summary>عايز تعديل؟</summary>
                                    <form class="fields" method="post" action="{{ route('tracking.review', $params) }}">
                                        @csrf<input type="hidden" name="version" value="{{ $item->preview_version }}"><input type="hidden" name="decision" value="changes">
                                        <div class="field"><label for="reason-{{ $item->id }}">اكتب التعديل المطلوب</label><textarea id="reason-{{ $item->id }}" name="reason" maxlength="1000" required rows="3"></textarea></div>
                                        <button class="btn subtle" type="submit">اطلب تعديل</button>
                                    </form>
                                </details>
                            </div>
                        @endif
                    </article>
                @endforeach
            </section>
        </div>
        <aside class="fields">
            <section class="panel summary">
                <h2>الدفع</h2>
                <dl class="totals">
                    <div><dt>القطع</dt><dd>{{ number_format($order->subtotal) }} ج.م.</dd></div>
                    <div><dt>الشحن</dt><dd>{{ number_format($order->shipping_fee) }} ج.م.</dd></div>
                    <div class="grand-total"><dt>الإجمالي</dt><dd>{{ number_format($order->total) }} ج.م.</dd></div>
                    <div><dt>المدفوع</dt><dd>{{ number_format($order->paid_amount) }} ج.م.</dd></div>
                    @if($order->status !== 'cancelled')<div><dt>المتبقي</dt><dd>{{ number_format($order->balance()) }} ج.م.</dd></div>@endif
                    @if($order->refunded_amount)<div><dt>تم استرداده</dt><dd>{{ number_format($order->refunded_amount) }} ج.م.</dd></div>@endif
                </dl>
            </section>
            <section class="panel"><h2>آخر التحديثات</h2><ol class="timeline">@foreach($order->events as $event)<li><p>{{ $event->message }}</p><time datetime="{{ $event->created_at->toIso8601String() }}">{{ $event->created_at->timezone('Africa/Cairo')->format('d/m/Y · H:i') }}</time></li>@endforeach</ol></section>
        </aside>
    </div>
</div>
@endsection
