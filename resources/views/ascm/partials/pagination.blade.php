{{--
    Compact pagination control.

    Deliberately NOT using Laravel's default $paginator->links() view: that
    view's prev/next icons rely on Tailwind utility classes (w-5 h-5, etc)
    to size correctly. This app's CSS is hand-rolled rather than a real
    Tailwind build, so those classes don't exist and the SVGs render at
    their raw, unclamped intrinsic size (the giant chevrons). This partial
    renders plain links styled with this app's own .pager CSS instead, so
    it always renders at a sane, predictable size.

    Usage: @include('ascm.partials.pagination', ['paginator' => $cases])
--}}
@if ($paginator && $paginator->hasPages())
    <nav class="pager" role="navigation" aria-label="Pagination">
        @if ($paginator->onFirstPage())
            <span class="pager-btn pager-btn-disabled" aria-disabled="true" aria-label="Previous page">‹</span>
        @else
            <a href="{{ $paginator->previousPageUrl() }}" class="pager-btn" rel="prev" aria-label="Previous page">‹</a>
        @endif

        @php
            $start = max(1, $paginator->currentPage() - 2);
            $end = min($paginator->lastPage(), $paginator->currentPage() + 2);
        @endphp

        @if ($start > 1)
            <a href="{{ $paginator->url(1) }}" class="pager-btn">1</a>
            @if ($start > 2)
                <span class="pager-ellipsis">…</span>
            @endif
        @endif

        @for ($page = $start; $page <= $end; $page++)
            @if ($page == $paginator->currentPage())
                <span class="pager-btn pager-btn-active" aria-current="page">{{ $page }}</span>
            @else
                <a href="{{ $paginator->url($page) }}" class="pager-btn">{{ $page }}</a>
            @endif
        @endfor

        @if ($end < $paginator->lastPage())
            @if ($end < $paginator->lastPage() - 1)
                <span class="pager-ellipsis">…</span>
            @endif
            <a href="{{ $paginator->url($paginator->lastPage()) }}" class="pager-btn">{{ $paginator->lastPage() }}</a>
        @endif

        @if ($paginator->hasMorePages())
            <a href="{{ $paginator->nextPageUrl() }}" class="pager-btn" rel="next" aria-label="Next page">›</a>
        @else
            <span class="pager-btn pager-btn-disabled" aria-disabled="true" aria-label="Next page">›</span>
        @endif
    </nav>
@endif