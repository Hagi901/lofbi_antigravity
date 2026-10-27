@if ($paginator->hasPages())
    <nav class="d-flex flex-column flex-md-row justify-content-between align-items-center gap-2 w-100" aria-label="Paginasi">
        <div>
            <p class="small text-muted mb-0">
                Menampilkan
                <span class="fw-semibold">{{ $paginator->firstItem() }}</span>
                sampai
                <span class="fw-semibold">{{ $paginator->lastItem() }}</span>
                dari
                <span class="fw-semibold">{{ $paginator->total() }}</span>
                data
            </p>
        </div>

        <div>
            <ul class="pagination pagination-sm mb-0 flex-wrap justify-content-center">
                {{-- Previous Page Link --}}
                @if ($paginator->onFirstPage())
                    <li class="page-item disabled" aria-disabled="true" aria-label="Sebelumnya">
                        <span class="page-link" aria-hidden="true">&lsaquo;</span>
                        <span class="visually-hidden">Sebelumnya</span>
                    </li>
                @else
                    <li class="page-item">
                        <a class="page-link" href="{{ $paginator->previousPageUrl() }}" rel="prev" aria-label="Sebelumnya">
                            <span aria-hidden="true">&lsaquo;</span>
                            <span class="visually-hidden">Sebelumnya</span>
                        </a>
                    </li>
                @endif

                {{-- Pagination Elements --}}
                @foreach ($elements as $element)
                    {{-- "Three Dots" Separator --}}
                    @if (is_string($element))
                        <li class="page-item disabled" aria-disabled="true"><span class="page-link">{{ $element }}</span></li>
                    @endif

                    {{-- Array Of Links --}}
                    @if (is_array($element))
                        @foreach ($element as $page => $url)
                            @if ($page == $paginator->currentPage())
                                <li class="page-item active" aria-current="page"><span class="page-link">{{ $page }}</span></li>
                            @else
                                <li class="page-item"><a class="page-link" href="{{ $url }}">{{ $page }}</a></li>
                            @endif
                        @endforeach
                    @endif
                @endforeach

                {{-- Next Page Link --}}
                @if ($paginator->hasMorePages())
                    <li class="page-item">
                        <a class="page-link" href="{{ $paginator->nextPageUrl() }}" rel="next" aria-label="Berikutnya">
                            <span aria-hidden="true">&rsaquo;</span>
                            <span class="visually-hidden">Berikutnya</span>
                        </a>
                    </li>
                @else
                    <li class="page-item disabled" aria-disabled="true" aria-label="Berikutnya">
                        <span class="page-link" aria-hidden="true">&rsaquo;</span>
                        <span class="visually-hidden">Berikutnya</span>
                    </li>
                @endif
            </ul>
        </div>
    </nav>
@endif
