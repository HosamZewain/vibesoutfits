@extends('layouts.admin')
@section('title', 'نظرة عامة')
@section('content')
<div class="admin-page-head">
    <div><p class="micro accent" dir="ltr">STORE OVERVIEW</p><h1>نظرة عامة</h1><p>تابع الطلبات والتحصيل والمخزون من مكان واحد.</p></div>
    <nav class="periods" aria-label="فترة التقرير">@foreach([7, 30, 90] as $period)<a class="{{ $days === $period ? 'current' : '' }}" href="{{ route('admin.dashboard', ['days' => $period]) }}">{{ $period }} يوم</a>@endforeach</nav>
</div>
<div class="kpi-grid">
    @foreach([
        ['bag', 'الطلبات', $stats['orders'], 'طلب', 'كل الطلبات المسجلة في الفترة'],
        ['box', 'مبيعات مسلّمة', $stats['sales'], 'ج.م.', 'إجمالي الطلبات المسلّمة شامل الشحن'],
        ['cash', 'صافي التحصيل', $stats['cash'], 'ج.م.', 'المدفوع بعد الاسترداد للطلبات في الفترة'],
        ['dashboard', 'متوسط الطلب', $stats['aov'], 'ج.م.', 'متوسط الطلبات غير الملغية']
    ] as [$icon, $label, $value, $unit, $hint])
        <div class="kpi"><span class="kpi-icon"><x-icon :name="$icon" width="17" height="17" /></span><h2>{{ $label }}</h2><p><bdi>{{ number_format($value) }}</bdi> <small>{{ $unit }}</small></p><span class="muted">{{ $hint }}</span></div>
    @endforeach
</div>
<div class="admin-columns">
    <section class="admin-panel">
        <header><h2>آخر الطلبات</h2><a class="table-link" href="{{ route('admin.orders') }}">كل الطلبات ←</a></header>
        <x-admin-orders-table :orders="$orders" />
    </section>
    <section class="admin-panel">
        <header><h2>حالات الطلبات</h2><span class="small muted">{{ $days }} يوم</span></header>
        <div class="status-bars">
            @foreach(config('vibe.statuses') as $key => $label)
                @php($n = $counts->get($key, 0))
                <div><span>{{ $label }}</span><b>{{ $n }}</b><div class="bar" aria-hidden="true"><i style="--value:{{ $stats['orders'] ? 100 * $n / $stats['orders'] : 0 }}%"></i></div></div>
            @endforeach
        </div>
    </section>
</div>
<section class="admin-panel">
    <header><h2>مخزون منخفض</h2><a class="table-link" href="{{ route('admin.products') }}">{{ $catalogCount }} منتج ظاهر ←</a></header>
    @if($lowStock->isEmpty())<p class="admin-empty">المخزون المتاح كويس.</p>@else
        <div class="stock-chips">@foreach($lowStock as $variant)<a href="{{ route('admin.products.edit', $variant->product) }}#stock"><bdi>{{ $variant->product->name }} · {{ $variant->size }}</bdi><span>{{ config('vibe.colors.'.$variant->color.'.0') }}</span><b>{{ $variant->stock }} متبقي</b></a>@endforeach</div>
    @endif
</section>
@endsection
