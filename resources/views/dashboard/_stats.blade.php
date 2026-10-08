{{-- Cartes statistiques — grille responsive simple --}}
<div class="dashboard-stats">
    @php
        $cards = [
            [
                'id' => 'filter-total',
                'filter' => null,
                'active' => ! $filter,
                'label' => 'Total courriers',
                'hint' => 'Ensemble du parc',
                'value' => $stats['total'],
                'icon' => 'bi-envelope-fill',
                'theme' => 'primary',
            ],
            [
                'id' => 'filter-entrants',
                'filter' => 'entrants',
                'active' => $filter === 'entrants',
                'label' => 'Entrants',
                'hint' => 'À traiter dans le périmètre',
                'value' => $stats['entrants'],
                'icon' => 'bi-box-arrow-in-down',
                'theme' => 'info',
            ],
            [
                'id' => 'filter-sortants',
                'filter' => 'sortants',
                'active' => $filter === 'sortants',
                'label' => 'Sortants',
                'hint' => 'Traités / clôturés / archivés',
                'value' => $stats['sortants'],
                'icon' => 'bi-box-arrow-up',
                'theme' => 'success',
            ],
            [
                'id' => 'filter-today',
                'filter' => 'today',
                'active' => $filter === 'today',
                'label' => "Aujourd'hui",
                'hint' => now()->locale('fr')->translatedFormat('j F Y'),
                'value' => $stats['aujourdhui'],
                'icon' => 'bi-calendar-day-fill',
                'theme' => 'warning',
            ],
            [
                'id' => 'filter-month',
                'filter' => 'month',
                'active' => $filter === 'month',
                'label' => 'Ce mois',
                'hint' => now()->locale('fr')->translatedFormat('F Y'),
                'value' => $stats['mois'],
                'icon' => 'bi-calendar-month-fill',
                'theme' => 'secondary',
            ],
        ];
    @endphp

    @foreach ($cards as $card)
        @php
            $params = $card['filter']
                ? array_merge(request()->except(['filter', 'page']), ['filter' => $card['filter']])
                : request()->except(['filter', 'page']);
        @endphp
        <a
            href="{{ route('dashboard', $params) }}"
            class="stat-card-link"
            id="{{ $card['id'] }}"
            aria-current="{{ $card['active'] ? 'true' : 'false' }}"
            title="Voir la liste sur Accueil"
        >
            <article class="stat-card stat-card--{{ $card['theme'] }} {{ $card['active'] ? 'is-active' : '' }}">
                <div class="stat-card-icon">
                    <i class="bi {{ $card['icon'] }}"></i>
                </div>
                <div class="stat-card-info">
                    <span class="stat-card-label">{{ $card['label'] }}</span>
                    <span class="stat-card-value">{{ number_format($card['value'], 0, ',', ' ') }}</span>
                    <span class="stat-card-hint">{{ $card['hint'] }}</span>
                </div>
            </article>
        </a>
    @endforeach
</div>
