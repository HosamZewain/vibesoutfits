@extends('layouts.store')
@section('title', 'تتبّع طلبك')
@section('content')
<div class="wrap track-shell">
    <div class="page-intro"><p class="micro accent"><span class="square"></span> YOUR ORDER</p><h1 class="display" lang="en">TRACK YOUR ORDER</h1><p dir="rtl">شوف آخر تحديث، راجع التصميم، وتابع الشحنة.</p></div>
    <form class="panel fields" method="post" action="{{ route('tracking.lookup') }}" dir="rtl">
        @csrf
        <div class="field"><label for="number">رقم الطلب</label><input id="number" name="number" dir="ltr" placeholder="VB-XXXXXXXXXX" maxlength="24" value="{{ old('number', $number) }}" required></div>
        <div class="field"><label for="phone">رقم الموبايل المستخدم في الطلب</label><input id="phone" name="phone" type="tel" dir="ltr" placeholder="01XXXXXXXXX" maxlength="30" value="{{ old('phone') }}" required></div>
        <button class="btn primary full" type="submit">تتبّع الطلب <x-icon name="arrow" class="mirror" /></button>
    </form>
</div>
@endsection
