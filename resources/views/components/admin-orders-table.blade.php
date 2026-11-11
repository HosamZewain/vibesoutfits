@props(['orders'])
@if($orders->count())
    <div class="table-scroll"><table class="admin-table">
        <thead><tr><th>الطلب</th><th>العميل</th><th>الحالة</th><th>الإجمالي</th><th>التاريخ</th></tr></thead>
        <tbody>
        @foreach($orders as $order)
            <tr>
                <td><a class="table-link" href="{{ route('admin.orders.show', ['order' => $order->number]) }}" dir="ltr">{{ $order->number }}</a></td>
                <td><b>{{ $order->name }}</b><small dir="ltr">{{ $order->phone }}</small></td>
                <td><span class="pill status-{{ $order->status }}">{{ $order->label() }}</span></td>
                <td>{{ number_format($order->total) }} ج.م.</td>
                <td><bdi>{{ $order->created_at->timezone('Africa/Cairo')->format('d/m/Y') }}</bdi></td>
            </tr>
        @endforeach
        </tbody>
    </table></div>
@else
    <p class="admin-empty">مفيش طلبات للعرض.</p>
@endif
