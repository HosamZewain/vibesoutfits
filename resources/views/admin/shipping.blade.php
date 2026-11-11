@extends('layouts.admin')
@section('title', 'أسعار الشحن')
@section('content')
<div class="admin-page-head"><div><p class="micro accent" dir="ltr">OPERATIONS / DELIVERY</p><h1>أسعار الشحن</h1><p>السعر بيتحسب وقت الطلب. تقدر توقف الشحن لأي محافظة مؤقتًا.</p></div></div>
<section class="admin-panel">
    <header><h2>محافظات مصر</h2></header>
    <div class="admin-pad shipping-grid">
        @foreach($zones as $zone)
            <form class="shipping-card fields" action="{{ route('admin.shipping.update', $zone) }}" method="post">
                @csrf @method('PATCH')
                <div class="split"><h3>{{ $zone->name }}</h3><label class="check-label"><input type="hidden" name="active" value="0"><input type="checkbox" name="active" value="1" @checked($zone->active)>متاح</label></div>
                <div class="inline-actions"><div class="field"><label for="fee-{{ $zone->id }}" class="sr-only">سعر الشحن بالجنيه</label><input id="fee-{{ $zone->id }}" type="number" name="fee" value="{{ $zone->fee }}" min="0" max="10000" required></div><span>ج.م.</span><button class="btn subtle compact" type="submit">حفظ</button></div>
            </form>
        @endforeach
    </div>
</section>
@endsection
