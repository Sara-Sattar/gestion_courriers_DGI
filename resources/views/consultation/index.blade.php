@extends('layouts.app')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/consultation.css') }}">
@endpush

@section('title', 'Statistiques')

@section('page-header')
    <div class="col">
        <h2 class="h3 mb-0 fw-bold">Statistiques</h2>
        <p class="text-muted mb-0 mt-1">
            Indicateurs et répartitions (type, BO, US, mois et axes complémentaires) —
            <span class="fw-medium text-body">{{ $libellePeriode }}</span>
            @if ($debut && $fin && $periode === 'personnalise')
                <span class="text-muted">({{ $debut->format('d/m/Y') }} → {{ $fin->format('d/m/Y') }})</span>
            @endif
        </p>
    </div>
    <div class="col-auto d-flex flex-wrap gap-2">
        @can('stats.exporter')
            @php $queryExport = request()->except('axe'); @endphp
            <div class="dropdown">
                <button class="btn btn-outline-success btn-sm dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                    <i class="bi bi-download me-1"></i>Exporter
                </button>
                <ul class="dropdown-menu dropdown-menu-end">
                    <li><a class="dropdown-item" href="{{ route('consultation.export', array_merge($queryExport, ['format' => 'csv'])) }}">CSV</a></li>
                    <li><a class="dropdown-item" href="{{ route('consultation.export', array_merge($queryExport, ['format' => 'xlsx'])) }}">Excel (XLSX)</a></li>
                    <li><a class="dropdown-item" href="{{ route('consultation.export', array_merge($queryExport, ['format' => 'json'])) }}">JSON</a></li>
                    <li><a class="dropdown-item" href="{{ route('consultation.export', array_merge($queryExport, ['format' => 'pdf'])) }}">PDF</a></li>
                </ul>
            </div>
        @endcan
    </div>
@endsection

@section('content')
    <form method="GET" action="{{ route('consultation.index') }}" class="consultation-toolbar mb-4" id="form-stats">
        <input type="hidden" name="axe" value="{{ $axe }}">
        <div class="d-flex flex-wrap align-items-end gap-3">
            <div>
                <span class="form-label small text-muted mb-1 d-block">Période</span>
                <div class="btn-group flex-wrap" role="group" aria-label="Période">
                    @foreach ([
                        'semaine' => 'Semaine',
                        'mois' => 'Mois',
                        'annee' => 'Année',
                        'tout' => 'Tout',
                        'personnalise' => 'Personnalisé',
                    ] as $valeur => $libelle)
                        <input
                            type="radio"
                            class="btn-check"
                            name="periode"
                            id="periode-{{ $valeur }}"
                            value="{{ $valeur }}"
                            autocomplete="off"
                            @checked($periode === $valeur)
                            @if ($valeur !== 'personnalise')
                                onchange="this.form.requestSubmit()"
                            @endif
                        >
                        <label class="btn btn-outline-secondary btn-sm" for="periode-{{ $valeur }}">{{ $libelle }}</label>
                    @endforeach
                </div>
            </div>

            <div class="consultation-dates @if ($periode !== 'personnalise') d-none @endif" id="bloc-dates-perso">
                <div class="d-flex flex-wrap gap-2 align-items-end">
                    <div>
                        <label for="debut" class="form-label small text-muted mb-1">Du</label>
                        <input type="date" name="debut" id="debut" class="form-control form-control-sm" value="{{ $debut?->format('Y-m-d') }}">
                    </div>
                    <div>
                        <label for="fin" class="form-label small text-muted mb-1">Au</label>
                        <input type="date" name="fin" id="fin" class="form-control form-control-sm" value="{{ $fin?->format('Y-m-d') }}">
                    </div>
                    <button type="submit" class="btn btn-primary btn-sm">Appliquer</button>
                </div>
            </div>
        </div>
    </form>

    {{-- Indicateurs de performance (spécifiques aux statistiques) --}}
    <div class="consultation-indicateurs mb-4">
        <div class="consultation-ind">
            <span class="consultation-ind-label">Total période</span>
            <span class="consultation-ind-value">{{ number_format($indicateurs['total'], 0, ',', ' ') }}</span>
        </div>
        <div class="consultation-ind">
            <span class="consultation-ind-label">En cours</span>
            <span class="consultation-ind-value">{{ number_format($indicateurs['en_cours'], 0, ',', ' ') }}</span>
        </div>
        <div class="consultation-ind">
            <span class="consultation-ind-label">Terminés</span>
            <span class="consultation-ind-value">{{ number_format($indicateurs['termines'], 0, ',', ' ') }}</span>
            <span class="consultation-ind-hint">{{ $indicateurs['taux_cloture'] }} % du total</span>
        </div>
        <div class="consultation-ind">
            <span class="consultation-ind-label">Nécessite réponse</span>
            <span class="consultation-ind-value">{{ number_format($indicateurs['necessite_reponse'], 0, ',', ' ') }}</span>
        </div>
        <div class="consultation-ind">
            <span class="consultation-ind-label">Délai réception</span>
            <span class="consultation-ind-value">
                {{ $indicateurs['delai_reception_jours'] !== null ? $indicateurs['delai_reception_jours'].' j' : '—' }}
            </span>
            <span class="consultation-ind-hint">moyenne dépôt → réception</span>
        </div>
        <div class="consultation-ind">
            <span class="consultation-ind-label">Délai traitement</span>
            <span class="consultation-ind-value">
                {{ $indicateurs['delai_traitement_jours'] !== null ? $indicateurs['delai_traitement_jours'].' j' : '—' }}
            </span>
            <span class="consultation-ind-hint">moyenne réception → traitement</span>
        </div>
        <div class="consultation-ind">
            <span class="consultation-ind-label">Délai délivrance</span>
            <span class="consultation-ind-value">
                {{ $indicateurs['delai_delivrance_jours'] !== null ? $indicateurs['delai_delivrance_jours'].' j' : '—' }}
            </span>
            <span class="consultation-ind-hint">moyenne traitement → délivrance</span>
        </div>
        <div class="consultation-ind">
            <span class="consultation-ind-label">Rejetés / annulés</span>
            <span class="consultation-ind-value">
                {{ number_format($indicateurs['rejetes'] + $indicateurs['annules'], 0, ',', ' ') }}
            </span>
            <span class="consultation-ind-hint">
                {{ $indicateurs['rejetes'] }} rejet · {{ $indicateurs['annules'] }} annul.
            </span>
        </div>
    </div>

    @if ($comparaison['disponible'])
        <div class="consultation-comparaison mb-4">
            <div>
                <span class="consultation-ind-label">Évolution vs période précédente</span>
                <div class="d-flex flex-wrap align-items-baseline gap-3 mt-1">
                    <span class="consultation-ind-value
                        @if ($comparaison['variation_pct'] > 0) text-success
                        @elseif ($comparaison['variation_pct'] < 0) text-danger
                        @endif">
                        @if ($comparaison['variation_pct'] > 0)+@endif{{ $comparaison['variation_pct'] }}&nbsp;%
                    </span>
                    <span class="small text-muted">
                        Période préc. : <strong class="text-body">{{ number_format($comparaison['total_precedent'], 0, ',', ' ') }}</strong>
                        courriers
                        <span class="d-none d-md-inline">({{ $comparaison['libelle'] }})</span>
                    </span>
                </div>
            </div>
            <a href="{{ route('dashboard') }}" class="btn btn-sm btn-outline-secondary align-self-center">
                Voir Accueil
            </a>
        </div>
    @endif

    <div class="card shadow-sm border-0">
        <div class="card-header bg-white px-4 pt-3 pb-0 border-0">
            <ul class="nav nav-tabs consultation-axes flex-wrap" role="tablist">
                <li class="nav-item" role="presentation">
                    <a
                        class="nav-link @if ($axe === 'synthese') active @endif"
                        href="{{ route('consultation.index', array_merge(request()->except('axe'), ['axe' => 'synthese'])) }}"
                    >Synthèse</a>
                </li>
            </ul>
            <div class="small text-muted mt-3 mb-2">Répartitions principales</div>
            <ul class="nav nav-tabs consultation-axes flex-wrap" role="tablist">
                @foreach ([
                    'type' => 'Type',
                    'bo' => 'BO',
                    'us' => 'US',
                    'mois' => 'Mois',
                ] as $valeur => $libelle)
                    <li class="nav-item" role="presentation">
                        <a
                            class="nav-link @if ($axe === $valeur) active @endif"
                            href="{{ route('consultation.index', array_merge(request()->except('axe'), ['axe' => $valeur])) }}"
                        >{{ $libelle }}</a>
                    </li>
                @endforeach
            </ul>
            <div class="small text-muted mt-3 mb-2">Autres répartitions</div>
            <ul class="nav nav-tabs consultation-axes consultation-axes-secondary flex-wrap border-0" role="tablist">
                @foreach ([
                    'categorie' => 'Catégorie',
                    'statut' => 'Statut',
                    'priorite' => 'Priorité',
                    'reponse' => 'Réponse',
                    'livraison' => 'Délivrance',
                ] as $valeur => $libelle)
                    <li class="nav-item" role="presentation">
                        <a
                            class="nav-link @if ($axe === $valeur) active @endif"
                            href="{{ route('consultation.index', array_merge(request()->except('axe'), ['axe' => $valeur])) }}"
                        >{{ $libelle }}</a>
                    </li>
                @endforeach
            </ul>
        </div>

        <div class="card-body px-4 pb-4 pt-3">
            <h3 class="h6 fw-semibold mb-3">{{ $titreAxe }}</h3>

            @if ($axe === 'synthese')
                @include('consultation._synthese', [
                    'statistiques' => $statistiques,
                    'croisement' => $croisement,
                    'maxMois' => $maxMois,
                ])
            @elseif ($lignesAxe->isEmpty())
                <div class="text-center text-muted py-5">
                    <i class="bi bi-inbox fs-1 d-block mb-2"></i>
                    Aucune donnée pour cette période.
                </div>
            @else
                @if ($axe === 'mois')
                    <div class="consultation-bars mb-4" aria-hidden="true">
                        @foreach ($lignesAxe as $ligne)
                            @php $h = max(6, (int) round(($ligne['total'] / $maxAxe) * 100)); @endphp
                            <div class="consultation-bars-item" title="{{ $ligne['libelle'] }} : {{ $ligne['total'] }}">
                                <span class="consultation-bars-value">{{ $ligne['total'] }}</span>
                                <div class="consultation-bars-col">
                                    <div class="consultation-bars-fill" style="height: {{ $h }}%"></div>
                                </div>
                                <span class="consultation-bars-label">{{ $ligne['code'] ?? $ligne['libelle'] }}</span>
                            </div>
                        @endforeach
                    </div>
                @endif

                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0 consultation-table">
                        <thead class="table-light">
                            <tr>
                                <th style="width: 3rem">#</th>
                                <th>Libellé</th>
                                @if (! in_array($axe, ['mois', 'categorie', 'type'], true))
                                    <th>Code</th>
                                @elseif ($axe === 'mois')
                                    <th>Période</th>
                                @endif
                                <th class="text-end">Nombre</th>
                                <th style="width: 35%">Répartition</th>
                                @if ($axe === 'statut')
                                    <th></th>
                                @endif
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($lignesAxe as $index => $ligne)
                                <tr>
                                    <td class="text-muted">{{ $index + 1 }}</td>
                                    <td class="fw-medium">{{ $ligne['libelle'] }}</td>
                                    @if ($axe === 'mois')
                                        <td class="text-muted">{{ $ligne['code'] ?? '—' }}</td>
                                    @elseif (! in_array($axe, ['categorie', 'type'], true))
                                        <td class="text-muted">{{ $ligne['code'] ?? '—' }}</td>
                                    @endif
                                    <td class="text-end text-nowrap fw-semibold">
                                        {{ number_format($ligne['total'], 0, ',', ' ') }}
                                        <span class="text-muted fw-normal small">({{ $ligne['pourcentage'] }}&nbsp;%)</span>
                                    </td>
                                    <td>
                                        <div class="progress consultation-progress" role="progressbar" aria-valuenow="{{ $ligne['pourcentage'] }}" aria-valuemin="0" aria-valuemax="100">
                                            <div class="progress-bar" style="width: {{ min(100, $ligne['pourcentage']) }}%"></div>
                                        </div>
                                    </td>
                                    @if ($axe === 'statut' && ! empty($ligne['lien']))
                                        <td class="text-end">
                                            <a href="{{ $ligne['lien'] }}" class="btn btn-sm btn-outline-primary">Liste</a>
                                        </td>
                                    @endif
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    </div>
@endsection

@push('scripts')
<script>
document.getElementById('periode-personnalise')?.addEventListener('change', (e) => {
    const bloc = document.getElementById('bloc-dates-perso');
    if (e.target.checked && bloc) {
        bloc.classList.remove('d-none');
    }
});
</script>
@endpush
