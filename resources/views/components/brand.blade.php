@props(['href' => null, 'stacked' => false])
<a class="brand {{ $stacked ? 'brand-stacked' : '' }}" href="{{ $href ?? route('home') }}" aria-label="{{ config('app.name') }}">
    @if($stacked)
        <img src="{{ asset('assets/img/vibes-logo-navy.png') }}" width="146" height="74" alt="" aria-hidden="true">
    @else
        <img src="{{ asset('assets/img/vibes-wordmark-navy.png') }}" width="166" height="33" alt="" aria-hidden="true">
    @endif
</a>
