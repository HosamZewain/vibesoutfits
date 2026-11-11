@extends('layouts.admin')
@section('title', 'دخول الإدارة')
@section('content')
<div class="login-box">
    <div dir="ltr"><x-brand /></div>
    <p class="micro accent" lang="en" dir="ltr">OPERATIONS / STUDIO</p>
    <h1>دخول الإدارة</h1>
    <p class="muted">سجّل دخولك عشان تدير متجر <bdi>{{ config('app.name') }}</bdi>.</p>
    <x-messages />
    <form method="post" action="{{ route('admin.authenticate') }}" class="fields">
        @csrf
        <div class="field"><label for="email">الإيميل</label><input id="email" name="email" type="email" dir="ltr" autocomplete="username" required value="{{ old('email') }}"></div>
        <div class="field"><label for="password">كلمة المرور</label><input id="password" name="password" type="password" dir="ltr" autocomplete="current-password" required></div>
        <button class="btn primary full">دخول</button>
        <a class="text-link" href="{{ route('home') }}">العودة للمتجر</a>
    </form>
</div>
@endsection
