@if ($paginator->total() > 0)
    <nav class="dashboard-pagination-inner" aria-label="Pagination des courriers">
        <p class="dashboard-pagination-summary mb-0">
            Affichage de
            <strong>{{ $paginator->firstItem() }}</strong>
            à
            <strong>{{ $paginator->lastItem() }}</strong>
            sur
            <strong>{{ $paginator->total() }}</strong>
            courrier{{ $paginator->total() > 1 ? 's' : '' }}
        </p>

        @if ($paginator->hasPages())
            <ul class="pagination mb-0">
                {{-- Précédent --}}
                @if ($paginator->onFirstPage())
                    <li class="page-item disabled">
                        <span class="page-link">
                            <i class="bi bi-chevron-left"></i>
                            <span class="d-none d-sm-inline ms-1">Précédent</span>
                        </span>
                    </li>
                @else
                    <li class="page-item">
                        <a class="page-link" href="{{ $paginator->previousPageUrl() }}" rel="prev">
                            <i class="bi bi-chevron-left"></i>
                            <span class="d-none d-sm-inline ms-1">Précédent</span>
                        </a>
                    </li>
                @endif

                {{-- Numéros de page --}}
                @foreach ($elements as $element)
                    @if (is_string($element))
                        <li class="page-item disabled"><span class="page-link">{{ $element }}</span></li>
                    @endif

                    @if (is_array($element))
                        @foreach ($element as $page => $url)
                            @if ($page == $paginator->currentPage())
                                <li class="page-item active" aria-current="page">
                                    <span class="page-link">{{ $page }}</span>
                                </li>
                            @else
                                <li class="page-item">
                                    <a class="page-link" href="{{ $url }}">{{ $page }}</a>
                                </li>
                            @endif
                        @endforeach
                    @endif
                @endforeach

                {{-- Suivant --}}
                @if ($paginator->hasMorePages())
                    <li class="page-item">
                        <a class="page-link" href="{{ $paginator->nextPageUrl() }}" rel="next">
                            <span class="d-none d-sm-inline me-1">Suivant</span>
                            <i class="bi bi-chevron-right"></i>
                        </a>
                    </li>
                @else
                    <li class="page-item disabled">
                        <span class="page-link">
                            <span class="d-none d-sm-inline me-1">Suivant</span>
                            <i class="bi bi-chevron-right"></i>
                        </span>
                    </li>
                @endif
            </ul>
        @endif
    </nav>
@endif
