@if ($paginator->hasPages())
    @php
        $current = $paginator->currentPage();
        $last = $paginator->lastPage();
        $start = max(1, $current - 2);
        $end = min($last, $current + 2);
    @endphp

    <nav class="web-pagination" aria-label="{{ $isArabic ? 'ترقيم صفحات المنتجات' : 'Products pagination' }}">
        <div class="web-pagination-summary">
            {{ $isArabic
                ? 'عرض '.$paginator->firstItem().'–'.$paginator->lastItem().' من '.$paginator->total().' منتج'
                : 'Showing '.$paginator->firstItem().'–'.$paginator->lastItem().' of '.$paginator->total().' products' }}
        </div>

        <div class="web-pagination-links">
            @if ($paginator->onFirstPage())
                <span class="web-page-arrow is-disabled" aria-disabled="true">{{ $isArabic ? 'السابق' : 'Previous' }}</span>
            @else
                <a class="web-page-arrow" href="{{ $paginator->previousPageUrl() }}" rel="prev">{{ $isArabic ? 'السابق' : 'Previous' }}</a>
            @endif

            @if ($start > 1)
                <a href="{{ $paginator->url(1) }}">1</a>
                @if ($start > 2)<span class="web-page-dots">…</span>@endif
            @endif

            @foreach ($paginator->getUrlRange($start, $end) as $page => $url)
                @if ($page === $current)
                    <span class="is-current" aria-current="page">{{ $page }}</span>
                @else
                    <a href="{{ $url }}">{{ $page }}</a>
                @endif
            @endforeach

            @if ($end < $last)
                @if ($end < $last - 1)<span class="web-page-dots">…</span>@endif
                <a href="{{ $paginator->url($last) }}">{{ $last }}</a>
            @endif

            @if ($paginator->hasMorePages())
                <a class="web-page-arrow" href="{{ $paginator->nextPageUrl() }}" rel="next">{{ $isArabic ? 'التالي' : 'Next' }}</a>
            @else
                <span class="web-page-arrow is-disabled" aria-disabled="true">{{ $isArabic ? 'التالي' : 'Next' }}</span>
            @endif
        </div>
    </nav>
@endif
