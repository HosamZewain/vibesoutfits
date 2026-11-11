<div dir="rtl" lang="ar">
    <h1>استلمنا طلبك يا {{ $order->name }}!</h1>
    <p>رقم الطلب: <b dir="ltr">{{ $order->number }}</b></p>
    <p>إجمالي الطلب: {{ number_format($order->total) }} جنيه.</p>
    @if($order->deposit_due)
        <p>المطلوب تحويله لتأكيد الطلب: {{ number_format($order->deposit_due) }} جنيه. تفاصيل التحويل في صفحة الطلب.</p>
    @endif
    <p><a href="{{ route('tracking', ['order' => $order->number]) }}">تابع طلبك</a> باستخدام رقم الطلب ورقم موبايلك.</p>
</div>
