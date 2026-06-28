@if ($paginator->hasPages())
<nav class="db-pagination" role="navigation" aria-label="{{ __('Pagination Navigation') }}">
  @if ($paginator->onFirstPage())
    <span class="db-pagination-btn disabled" aria-disabled="true">← Previous</span>
  @else
    <a href="{{ $paginator->previousPageUrl() }}" class="db-pagination-btn" rel="prev">← Previous</a>
  @endif

  <span class="db-pagination-info">
    Page {{ $paginator->currentPage() }} of {{ $paginator->lastPage() }}
    <span class="db-pagination-count">({{ $paginator->total() }} total)</span>
  </span>

  @if ($paginator->hasMorePages())
    <a href="{{ $paginator->nextPageUrl() }}" class="db-pagination-btn" rel="next">Next →</a>
  @else
    <span class="db-pagination-btn disabled" aria-disabled="true">Next →</span>
  @endif
</nav>
@endif
