@extends('layouts.admin')
@section('title', 'طلب '.$order->number)
@section('content')
@php
    $orderParams = ['order' => $order->number];
    $canProduce = $order->allApproved() && $order->paid_amount >= $order->deposit_due;
@endphp
<div class="admin-page-head">
    <div><a href="{{ route('admin.orders') }}" class="table-link">الطلبات ↗</a><h1><bdi>{{ $order->number }}</bdi></h1><p>{{ $order->name }} · {{ $order->created_at->timezone('Africa/Cairo')->format('d/m/Y H:i') }}</p></div>
    <span class="pill status-{{ $order->status }}">{{ $order->label() }}</span>
</div>
<div class="admin-columns order-columns">
    <div class="fields">
        <section class="admin-panel">
            <header><h2>القطع والتصميمات</h2></header>
            <div class="admin-pad fields">
                @foreach($order->items as $item)
                    @php($params = $orderParams + ['item' => $item->id])
                    <article class="admin-line fields">
                        <div class="split"><h3><bdi>{{ $item->product_name }}</bdi> × {{ $item->quantity }}</h3><strong>{{ number_format($item->unit_price * $item->quantity) }} ج.م.</strong></div>
                        <p class="muted small">{{ config('vibe.colors.'.$item->color.'.0') }} · <bdi>{{ $item->size }}</bdi> · {{ config('vibe.modes.'.$item->design_mode) }} · طباعة {{ config('vibe.placements.'.$item->placement) }}</p>
                        @if($item->design_mode !== 'ready')
                            <div class="split"><span class="pill">{{ config('vibe.design_statuses.'.$item->design_status) }}</span>@if($item->approved_at)<small>وقت الموافقة: {{ $item->approved_at->timezone('Africa/Cairo')->format('d/m/Y H:i') }}</small>@endif</div>
                            @if($item->idea)<div class="order-text"><b>فكرة العميل</b><p class="preline">{{ $item->idea }}</p></div>@endif
                            @if($item->change_request)<div class="order-text"><b>آخر تعديل مطلوب</b><p class="preline">{{ $item->change_request }}</p></div>@endif
                            <div class="inline-actions">
                                @if($item->upload_path)<a class="btn subtle compact" href="{{ route('admin.orders.file', $params + ['kind' => 'upload']) }}">تنزيل ملف العميل</a>@endif
                                @if($item->production_path)<a class="btn subtle compact" href="{{ route('admin.orders.file', $params + ['kind' => 'production']) }}">تنزيل ملف الطباعة</a>@endif
                            </div>
                            @if($item->preview_path)<figure class="preview"><img src="{{ route('admin.orders.file', $params + ['kind' => 'preview', 'v' => $item->preview_version]) }}" alt="معاينة التصميم" loading="lazy"><figcaption>المعاينة رقم {{ $item->preview_version }}</figcaption></figure>@endif
                            @if($order->status === 'new' && $item->design_status !== 'approved')
                                <form class="artwork-form" action="{{ route('admin.orders.artwork', $params) }}" method="post" enctype="multipart/form-data">
                                    @csrf
                                    <div class="field"><label for="kind-{{ $item->id }}">نوع الملف</label><select name="kind" id="kind-{{ $item->id }}"><option value="preview">معاينة للعميل</option>@if($item->design_status === 'preview')<option value="production">ملف الطباعة (داخلي)</option>@endif</select></div>
                                    <div class="field"><label for="art-{{ $item->id }}">صورة حتى ١٠ ميجابايت</label><input id="art-{{ $item->id }}" name="artwork" type="file" accept="image/png,image/jpeg,image/webp" required></div>
                                    <button class="btn primary" type="submit">رفع الملف</button>
                                </form>
                                <p class="small muted">ارفع المعاينة أولًا. لو فيه ملف طباعة منفصل، ارفعه قبل موافقة العميل. المعاينة الجديدة بتستبدل ملفات النسخة السابقة.</p>
                            @endif
                        @endif
                    </article>
                @endforeach
            </div>
        </section>
        <section class="admin-panel">
            <header><h2>التنفيذ والشحن</h2></header>
            <div class="admin-pad fields">
                @if($order->status === 'new')
                    <form method="post" action="{{ route('admin.orders.status', $orderParams) }}">
                        @csrf @method('PATCH') <input name="status" type="hidden" value="production">
                        <button class="btn primary" type="submit" @disabled(!$canProduce)>ابدأ الإنتاج</button>
                        @unless($canProduce)<p class="small muted">سجّل المقدم واستنى موافقة العميل على كل التصميمات.</p>@endunless
                    </form>
                @elseif($order->status === 'production')
                    <form class="fields" method="post" action="{{ route('admin.orders.status', $orderParams) }}">
                        @csrf @method('PATCH') <input name="status" type="hidden" value="shipped">
                        <div class="field-row"><div class="field"><label for="carrier">شركة الشحن</label><input id="carrier" name="carrier" required maxlength="80" value="{{ old('carrier') }}"></div><div class="field"><label for="shipment_code">رقم الشحنة</label><input id="shipment_code" name="shipment_code" required maxlength="100" value="{{ old('shipment_code') }}"></div></div>
                        <button class="btn primary" type="submit">تأكيد الشحن</button>
                    </form>
                @elseif($order->status === 'shipped')
                    <form method="post" action="{{ route('admin.orders.status', $orderParams) }}">
                        @csrf @method('PATCH') <input name="status" type="hidden" value="delivered">
                        <button class="btn primary" type="submit" @disabled($order->balance() > 0)>تأكيد التسليم</button>
                        @if($order->balance() > 0)<p class="small muted">سجّل تحصيل باقي المبلغ الأول.</p>@endif
                    </form>
                @else
                    <p>{{ config('vibe.statuses.'.$order->status) }}</p>
                @endif
                @if($order->carrier)<p>شركة الشحن: {{ $order->carrier }} · <bdi>{{ $order->shipment_code }}</bdi></p>@endif
                @if(in_array($order->status, ['new', 'production'], true))
                    <details><summary>إلغاء الطلب</summary><form method="post" class="fields" action="{{ route('admin.orders.status', $orderParams) }}">@csrf @method('PATCH')<input name="status" type="hidden" value="cancelled"><p class="small muted">{{ $order->status === 'new' ? 'المخزون المحجوز هيرجع.' : 'القطع اللي دخلت الإنتاج مش هترجع للمخزون تلقائيًا.' }} لو فيه تحصيل، سجّل الاسترداد بعد الإلغاء.</p><button class="btn danger" type="submit">تأكيد إلغاء الطلب</button></form></details>
                @endif
            </div>
        </section>
        <section class="admin-panel"><header><h2>سجل الطلب</h2></header><div class="admin-pad"><ol class="timeline">@foreach($order->events as $event)<li><p>{{ $event->message }} @unless($event->visible)<small class="muted">(داخلي)</small>@endunless</p><time datetime="{{ $event->created_at->toIso8601String() }}">{{ $event->created_at->timezone('Africa/Cairo')->format('d/m/Y · H:i') }}</time></li>@endforeach</ol></div></section>
    </div>
    <aside class="fields">
        <section class="admin-panel">
            <header><h2>بيانات العميل والتوصيل</h2></header>
            <div class="admin-pad fields">
                <p><b>{{ $order->name }}</b><br><a href="tel:{{ $order->phone }}" class="table-link" dir="ltr">{{ $order->phone }}</a>@if($order->email)<br><bdi>{{ $order->email }}</bdi>@endif</p>
                <p>{{ $order->governorate }} · {{ $order->city }}<br><span class="preline">{{ $order->address }}</span></p>
                @if($order->notes)<p class="preline"><b>ملاحظة:</b> {{ $order->notes }}</p>@endif
            </div>
        </section>
        <section class="admin-panel">
            <header><h2>الدفع والتحصيل</h2><span class="pill">{{ $order->payment_method === 'cod' ? 'عند الاستلام' : 'تحويل' }}</span></header>
            <div class="admin-pad">
                <dl class="totals">
                    <div><dt>القطع</dt><dd>{{ number_format($order->subtotal) }} ج.م.</dd></div>
                    <div><dt>الشحن</dt><dd>{{ number_format($order->shipping_fee) }} ج.م.</dd></div>
                    <div class="grand-total"><dt>الإجمالي</dt><dd>{{ number_format($order->total) }} ج.م.</dd></div>
                    <div><dt>الدفعة الأولى المطلوبة</dt><dd>{{ number_format($order->deposit_due) }} ج.م.</dd></div>
                    <div><dt>المحصّل</dt><dd>{{ number_format($order->paid_amount) }} ج.م.</dd></div>
                    @if($order->status === 'cancelled')
                        <div><dt>تم استرداده</dt><dd>{{ number_format($order->refunded_amount) }} ج.م.</dd></div>
                    @else
                        <div><dt>المتبقي</dt><dd>{{ number_format($order->balance()) }} ج.م.</dd></div>
                    @endif
                </dl>
                @php($limit = $order->status === 'cancelled' ? $order->paid_amount - $order->refunded_amount : $order->balance())
                @if($limit > 0)
                    <form class="fields" method="post" action="{{ route('admin.orders.payment', $orderParams) }}">
                        @csrf <input name="action" type="hidden" value="{{ $order->status === 'cancelled' ? 'refund' : 'collect' }}">
                        <input type="hidden" name="recorded" value="{{ $order->status === 'cancelled' ? $order->refunded_amount : $order->paid_amount }}">
                        <div class="field"><label for="amount">المبلغ اللي {{ $order->status === 'cancelled' ? 'اتردّ' : 'اتحصّل' }} (ج.م.)</label><input id="amount" name="amount" type="number" min="1" max="{{ $limit }}" required value="{{ old('amount') }}"></div>
                        <div class="field"><label for="reference">مرجع العملية <small>اختياري</small></label><input id="reference" name="reference" maxlength="150" value="{{ old('reference') }}"></div>
                        <button class="btn primary" type="submit">{{ $order->status === 'cancelled' ? 'سجّل الاسترداد' : 'سجّل التحصيل' }}</button>
                    </form>
                @endif
            </div>
        </section>
    </aside>
</div>
@endsection
