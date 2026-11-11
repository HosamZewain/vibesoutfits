@extends('layouts.store')
@section('title', 'إتمام الطلب')
@section('content')
@php
    $hasCustom = collect($lines)->contains(fn($line) => $line['design_mode'] !== 'ready');
    $customDeposit = $hasCustom ? max(1, (int) ceil($customSubtotal * $depositPercent / 100)) : 0;
@endphp
<div class="wrap">
    <div class="page-intro"><p class="micro accent"><span class="square"></span> STEP 02 / CHECKOUT</p><h1 class="display" lang="en">CHECKOUT</h1><p dir="rtl">اكتب بيانات التوصيل وراجع الإجمالي قبل التأكيد.</p></div>
    <form method="post" action="{{ route('checkout.place') }}" class="commerce-grid" dir="rtl" data-checkout data-subtotal="{{ $subtotal }}" data-deposit="{{ $customDeposit }}">
        @csrf
        <input type="hidden" name="quoted_total" value="" data-quoted>
        <div class="fields">
            <section class="panel fields">
                <h2>١. بيانات التوصيل</h2>
                <div class="field-row">
                    <div class="field"><label for="name">الاسم بالكامل</label><input id="name" name="name" autocomplete="name" value="{{ old('name') }}" maxlength="100" required></div>
                    <div class="field"><label for="phone">رقم الموبايل</label><input id="phone" name="phone" type="tel" dir="ltr" inputmode="tel" autocomplete="tel" value="{{ old('phone') }}" placeholder="01XXXXXXXXX" maxlength="25" required></div>
                </div>
                <div class="field-row">
                    <div class="field"><label for="zone">المحافظة</label><select id="zone" name="zone" required><option value="">اختار المحافظة</option>@foreach($zones as $zone)<option value="{{ $zone->id }}" data-fee="{{ $zone->fee }}" @selected((string) old('zone') === (string) $zone->id)>{{ $zone->name }} · {{ $zone->fee }} ج.م.</option>@endforeach</select></div>
                    <div class="field"><label for="city">المدينة / المنطقة</label><input id="city" name="city" autocomplete="address-level2" value="{{ old('city') }}" maxlength="90" required></div>
                </div>
                <div class="field"><label for="address">العنوان بالتفصيل</label><textarea id="address" name="address" rows="2" autocomplete="street-address" minlength="8" maxlength="300" placeholder="الشارع، رقم العمارة، الدور والشقة" required>{{ old('address') }}</textarea></div>
                <div class="field"><label for="email">الإيميل <small>اختياري</small></label><input id="email" type="email" name="email" dir="ltr" autocomplete="email" value="{{ old('email') }}" maxlength="150">@if(config('vibe.email_confirmations'))<small>هنبعت تأكيد الطلب على الإيميل ده.</small>@endif</div>
                <div class="field"><label for="notes">ملاحظات التوصيل <small>اختياري</small></label><input id="notes" name="notes" value="{{ old('notes') }}" maxlength="500"></div>
            </section>
            <section class="panel fields">
                <h2>٢. طريقة الدفع</h2>
                @unless($hasCustom)
                    <label class="payment-option"><input type="radio" name="payment" value="cod" @checked(old('payment', 'cod') === 'cod') required><span><b>الدفع عند الاستلام</b><small>ادفع الإجمالي لما تستلم طلبك.</small></span><x-icon name="cash" /></label>
                @endunless
                @if($transfer)
                    <label class="payment-option"><input type="radio" name="payment" value="transfer" @checked(old('payment', $hasCustom ? 'transfer' : '') === 'transfer') required><span><b>{{ $hasCustom ? 'تحويل مقدم للقطع المخصصة' : 'الدفع بالتحويل' }}</b><small>{{ $hasCustom ? 'مقدم '.number_format($customDeposit).' ج.م.، والباقي عند الاستلام.' : 'حوّل إجمالي الطلب لتأكيده.' }} تفاصيل التحويل تظهر بعد التسجيل.</small></span><x-icon name="cash" /></label>
                @elseif($hasCustom)
                    <p class="warning">التحويل مش متاح حاليًا. جرّب تاني لاحقًا أو راجع القطع في السلة.</p>
                @endif
            </section>
        </div>
        <aside class="panel summary">
            <h2>ملخص الطلب</h2>
            <div class="summary-lines">
                @foreach($lines as $line)<div><span><bdi>{{ $line['product']->name }}</bdi> × {{ $line['quantity'] }}</span><b>{{ number_format($line['line_total']) }} ج.م.</b></div>@endforeach
            </div>
            <dl class="totals" aria-live="polite">
                <div><dt>إجمالي القطع</dt><dd>{{ number_format($subtotal) }} ج.م.</dd></div>
                <div><dt>الشحن</dt><dd data-shipping>اختار المحافظة</dd></div>
                <div class="grand-total"><dt>الإجمالي</dt><dd data-total>—</dd></div>
                <div><dt>المطلوب الآن</dt><dd data-now>{{ number_format($customDeposit) }} ج.م.</dd></div>
            </dl>
            @if((!$hasCustom || $transfer) && $zones->isNotEmpty())<button class="btn primary full" type="submit">تأكيد الطلب <x-icon name="check" /></button>@endif
            <a class="btn subtle full" href="{{ route('cart') }}">ارجع للسلة</a>
        </aside>
    </form>
</div>
@endsection
