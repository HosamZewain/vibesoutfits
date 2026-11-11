@extends('layouts.admin')
@section('title', 'المنتجات والمخزون')
@section('content')
<div class="admin-page-head"><div><p class="micro accent" dir="ltr">CATALOG / INVENTORY</p><h1>المنتجات والمخزون</h1><p>حدّث الكوليكشن والأسعار والكمية المتاحة لكل مقاس ولون.</p></div><a class="btn primary" href="{{ route('admin.products.create') }}">+ منتج جديد</a></div>
<section class="admin-panel">
    @if($products->count())
        <div class="table-scroll"><table class="admin-table">
            <thead><tr><th>المنتج</th><th>الفئة</th><th>السعر</th><th>المخزون</th><th>الظهور</th><th></th></tr></thead>
            <tbody>@foreach($products as $product)
                <tr>
                    <td><div class="table-product"><img src="{{ $product->imageUrl() }}" alt="" width="40" height="40"><a class="table-link" href="{{ route('admin.products.edit', $product) }}"><bdi>{{ $product->name }}</bdi></a></div></td>
                    <td><bdi>{{ config('vibe.categories.'.$product->category) }}</bdi></td><td>{{ number_format($product->price) }} ج.م.</td><td>{{ $product->active_stock ?? 0 }}</td>
                    <td><span class="pill {{ $product->active ? 'status-delivered' : 'status-cancelled' }}">{{ $product->active ? 'ظاهر' : 'مخفي' }}</span></td>
                    <td><a href="{{ route('admin.products.edit', $product) }}" class="btn subtle compact">تعديل</a></td>
                </tr>
            @endforeach</tbody>
        </table></div>
    @else
        <p class="admin-empty">ضيف أول منتج عشان يظهر في المتجر.</p>
    @endif
</section>
<x-pagination :paginator="$products" />
@endsection
