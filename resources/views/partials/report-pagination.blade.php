<div class="d-flex flex-wrap justify-content-between align-items-center mt-3">
    <span class="text-muted">Showing {{ $paginator->firstItem() ?? 0 }}–{{ $paginator->lastItem() ?? 0 }} of {{ $paginator->total() }} receipts</span>
    <nav aria-label="Report pages">
        <ul class="pagination mb-0">
            <li class="page-item {{ $paginator->onFirstPage() ? 'disabled' : '' }}">
                @if($paginator->onFirstPage())<span class="page-link">Previous</span>
                @else<a class="page-link" rel="prev" href="{{ $paginator->previousPageUrl() }}">Previous</a>@endif
            </li>
            <li class="page-item"><span class="page-link">Page {{ $paginator->currentPage() }} / {{ $paginator->lastPage() }}</span></li>
            <li class="page-item {{ $paginator->hasMorePages() ? '' : 'disabled' }}">
                @if($paginator->hasMorePages())<a class="page-link" rel="next" href="{{ $paginator->nextPageUrl() }}">Next</a>
                @else<span class="page-link">Next</span>@endif
            </li>
        </ul>
    </nav>
</div>
