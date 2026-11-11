@extends('layouts.admin')
@section('title', 'العملاء')
@section('content')
<div class="admin-page-head"><div><p class="micro accent" dir="ltr">OPERATIONS / CUSTOMERS</p><h1>العملاء</h1><p>بيانات آخر طلب لكل عميل وعدد الطلبات المرتبطة بنفس رقم الموبايل.</p></div></div>
<section class="admin-panel">
    <form class="admin-filters" method="get" action="{{ route('admin.customers') }}"><div class="field"><label for="q">ابحث بالاسم أو رقم الموبايل</label><input id="q" name="q" value="{{ $search }}" maxlength="100"></div><button class="btn primary" type="submit">بحث</button></form>
    @if($customers->count())
        <div class="table-scroll"><table class="admin-table">
            <thead><tr><th>الاسم</th><th>الموبايل</th><th>المحافظة</th><th>عدد الطلبات</th><th>صافي التحصيل</th><th>آخر طلب</th></tr></thead>
            <tbody>@foreach($customers as $customer)<tr><td>{{ $customer->name }}</td><td><bdi>{{ $customer->phone }}</bdi></td><td>{{ $customer->governorate }}</td><td><a class="table-link" href="{{ route('admin.orders', ['q' => $customer->phone]) }}">{{ $customer->order_count }}</a></td><td>{{ number_format($customer->collected) }} ج.م.</td><td><a class="table-link" href="{{ route('admin.orders.show', ['order' => $customer->number]) }}"><bdi>{{ $customer->number }}</bdi></a></td></tr>@endforeach</tbody>
        </table></div>
    @else
        <p class="admin-empty">مفيش عملاء مطابقين.</p>
    @endif
</section>
<x-pagination :paginator="$customers" />
@endsection
