{{-- Vue synthèse : tops + croisement type × statut + évolution mensuelle --}}
<div class="row g-3 mb-4">
    @foreach ([
        'par_type' => 'Top types',
        'par_bo' => 'Top BO',
        'par_us' => 'Top US',
        'par_statut' => 'Par statut',
    ] as $cle => $titre)
        <div class="col-md-6 col-xl-3">
            <div class="consultation-top-card h-100">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <h4 class="h6 fw-semibold mb-0">{{ $titre }}</h4>
                    @php
                        $axeLien = match ($cle) {
                            'par_bo' => 'bo',
                            'par_us' => 'us',
                            'par_statut' => 'statut',
                            default => 'type',
                        };
                    @endphp
                    <a href="{{ route('consultation.index', array_merge(request()->except('axe'), ['axe' => $axeLien])) }}" class="small text-decoration-none">Voir tout</a>
                </div>
                @forelse ($statistiques[$cle]->take(5) as $i => $ligne)
                    <div class="consultation-top-row">
                        <span class="text-muted">{{ $i + 1 }}.</span>
                        <span class="flex-grow-1 text-truncate" title="{{ $ligne['libelle'] }}">{{ $ligne['libelle'] }}</span>
                        <strong>{{ number_format($ligne['total'], 0, ',', ' ') }}</strong>
                    </div>
                @empty
                    <p class="small text-muted mb-0">Aucune donnée</p>
                @endforelse
            </div>
        </div>
    @endforeach
</div>

@if ($statistiques['par_mois']->isNotEmpty())
    <h4 class="h6 fw-semibold mb-2">Évolution mensuelle</h4>
    <div class="consultation-bars mb-4" aria-hidden="true">
        @foreach ($statistiques['par_mois'] as $ligne)
            @php $h = max(6, (int) round(($ligne['total'] / $maxMois) * 100)); @endphp
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

<h4 class="h6 fw-semibold mb-2">Croisement type × statut <span class="text-muted fw-normal">(top 5 types)</span></h4>
@if (empty($croisement['types']))
    <p class="small text-muted mb-0">Aucune donnée pour le croisement.</p>
@else
    <div class="table-responsive">
        <table class="table table-sm table-bordered align-middle consultation-table mb-0">
            <thead class="table-light">
                <tr>
                    <th>Type</th>
                    @foreach ($croisement['statuts'] as $statut)
                        <th class="text-end small">{{ $statut['label'] }}</th>
                    @endforeach
                </tr>
            </thead>
            <tbody>
                @foreach ($croisement['types'] as $typeNom)
                    <tr>
                        <td class="fw-medium">{{ $typeNom }}</td>
                        @foreach ($croisement['statuts'] as $statut)
                            @php $n = $croisement['cellules'][$typeNom][$statut['value']] ?? 0; @endphp
                            <td class="text-end {{ $n === 0 ? 'text-muted' : '' }}">{{ $n }}</td>
                        @endforeach
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
@endif
