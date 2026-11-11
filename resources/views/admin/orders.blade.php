@extends('layouts.admin')
@section('title', 'الطلبات')
@section('content')
<div class="admin-page-head"><div><p class="micro accent" dir="ltr">OPERATIONS / ORDERS</p><h1>الطلبات</h1><p>راجع الطلبات والتصميمات، وتابع الإنتاج والشحن.</p></div></div>
<section class="admin-panel">
    <form class="admin-filters" method="get" action="{{ route('admin.orders') }}">
        <div class="field"><label for="q">بحث بالاسم أو الموبايل أو رقم الطلب</label><input id="q" name="q" value="{{ $search }}" maxlength="100" placeholder="ابحث عن طلب"></div>
        <div class="field"><label for="status">الحالة</label><select id="status" name="status"><option value="">كل الحالات</option>@foreach(config('vibe.statuses') as $key=>$label)<option value="{{ $key }}" @selected($status === $key)>{{ $label }}</option>@endforeach</select></div>
        <button class="btn primary">بحث</button>
        @if($search || $status)<a class="btn subtle" href="{{ route('admin.orders') }}">مسح الفلاتر</a>@endif
    </form>
    <x-admin-orders-table :orders="$orders" />
</section>
<x-pagination :paginator="$orders" />
@endsection
