@props(['paginator'])
@if($paginator->hasPages())
    <nav class="pagination" aria-label="الصفحات" dir="rtl">
        @if($paginator->onFirstPage())<span>السابق</span>@else<a href="{{ $paginator->previousPageUrl() }}">السابق</a>@endif
        <b>{{ $paginator->currentPage() }} / {{ $paginator->lastPage() }}</b>
        @if($paginator->hasMorePages())<a href="{{ $paginator->nextPageUrl() }}">التالي</a>@else<span>التالي</span>@endif
    </nav>
@endif
