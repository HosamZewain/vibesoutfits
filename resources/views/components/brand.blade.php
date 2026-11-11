@props(['href' => null, 'stacked' => false])
<a class="brand {{ $stacked ? 'brand-stacked' : '' }}" href="{{ $href ?? route('home') }}" aria-label="{{ config('app.name') }}">
    @if($stacked)
        <img src="{{ asset('assets/img/vibe-logo.svg') }}" width="116" height="77" alt="" aria-hidden="true">
    @else
        <img src="{{ asset('assets/img/vibe-wordmark.svg') }}" width="150" height="34" alt="" aria-hidden="true">
    @endif
</a>
