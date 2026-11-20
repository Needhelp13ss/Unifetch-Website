@if ($paginator->hasPages())
<nav class="pager" role="navigation" aria-label="Pagination">
    @if ($paginator->onFirstPage()) <span>‹ Prev</span> @else <a href="{{ $paginator->previousPageUrl() }}">‹ Prev</a> @endif
    <span class="cur">{{ $paginator->currentPage() }} / {{ $paginator->lastPage() }}</span>
    @if ($paginator->hasMorePages()) <a href="{{ $paginator->nextPageUrl() }}">Next ›</a> @else <span>Next ›</span> @endif
</nav>
@endif
