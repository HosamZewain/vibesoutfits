<!doctype html>
<html lang="ar" dir="rtl" class="admin-html">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex, nofollow">
    <meta name="referrer" content="same-origin">
    <title>@yield('title', 'لوحة الإدارة') | {{ config('app.name') }}</title>
    <link rel="icon" href="{{ asset('assets/img/mark.svg') }}" type="image/svg+xml">
    <link rel="stylesheet" href="{{ asset('assets/fonts/fonts.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/store.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/admin.css') }}">
</head>
<body class="admin-body">
@if(request()->routeIs('admin.login'))
    <main class="admin-login">@yield('content')</main>
@else
    <div class="admin-shell">
        <aside class="admin-sidebar">
            <div class="admin-brand" dir="ltr"><x-brand :href="route('admin.dashboard')" /><span lang="en">OPERATIONS / STUDIO</span></div>
            <nav class="admin-nav" aria-label="إدارة المتجر">
                @foreach([
                    ['dashboard', 'dashboard', 'نظرة عامة'],
                    ['orders', 'bag', 'الطلبات'],
                    ['products', 'box', 'المنتجات والمخزون'],
                    ['customers', 'users', 'العملاء'],
                    ['shipping', 'truck', 'أسعار الشحن']
                ] as [$key, $icon, $label])
                    <a class="{{ request()->routeIs('admin.'.$key, 'admin.'.$key.'.*') ? 'current' : '' }}" href="{{ route('admin.'.$key) }}"><x-icon :name="$icon" width="18" height="18" />{{ $label }}</a>
                @endforeach
            </nav>
            <div class="admin-sidebar-end"><a href="{{ route('home') }}"><x-icon name="up-right" width="16" height="16" /> افتح المتجر</a></div>
        </aside>
        <div class="admin-content">
            <header class="admin-top"><span><x-icon name="spark" width="17" height="17" /> لوحة الإدارة</span><div>{{ auth()->user()->name }} <form action="{{ route('admin.logout') }}" method="post">@csrf<button class="link-button" type="submit">خروج</button></form></div></header>
            <main class="admin-main">
                <x-messages />
                @yield('content')
            </main>
        </div>
    </div>
@endif
</body>
</html>
