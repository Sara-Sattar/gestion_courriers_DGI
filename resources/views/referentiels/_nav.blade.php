@php
    $onglets = [
        [
            'motif' => 'categories.*',
            'url' => route('categories.index'),
            'icone' => 'bi-tags',
            'libelle' => 'Catégories',
        ],
        [
            'motif' => 'types-courrier.*',
            'url' => route('types-courrier.index'),
            'icone' => 'bi-list-ul',
            'libelle' => 'Types de courrier',
        ],
        [
            'motif' => 'unites.*',
            'url' => route('unites.index'),
            'icone' => 'bi-diagram-3',
            'libelle' => 'Unités',
        ],
    ];
@endphp

<ul class="nav nav-pills referentiels-nav mb-3">
    @foreach ($onglets as $onglet)
        <li class="nav-item">
            <a
                class="nav-link {{ request()->routeIs($onglet['motif']) ? 'active' : '' }}"
                href="{{ $onglet['url'] }}"
                @if (request()->routeIs($onglet['motif'])) aria-current="page" @endif
            >
                <i class="bi {{ $onglet['icone'] }} me-1"></i>{{ $onglet['libelle'] }}
            </a>
        </li>
    @endforeach
</ul>

@push('styles')
<link rel="stylesheet" href="{{ asset('css/referentiels.css') }}">
@endpush
