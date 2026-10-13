@if ($paginator->hasPages())
    <nav class="pagination" aria-label="{{ __('Pagination') }}">
        @if ($paginator->onFirstPage())
            <span class="btn btn--ghost btn--sm is-disabled">← {{ __('Previous') }}</span>
        @else
            <a href="{{ $paginator->previousPageUrl() }}" class="btn btn--ghost btn--sm" rel="prev">← {{ __('Previous') }}</a>
        @endif

        <span class="muted">{{ __('Page :current of :last', ['current' => $paginator->currentPage(), 'last' => $paginator->lastPage()]) }}</span>

        @if ($paginator->hasMorePages())
            <a href="{{ $paginator->nextPageUrl() }}" class="btn btn--ghost btn--sm" rel="next">{{ __('Next') }} →</a>
        @else
            <span class="btn btn--ghost btn--sm is-disabled">{{ __('Next') }} →</span>
        @endif
    </nav>
@endif
