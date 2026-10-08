<div class="table-responsive">
    <table class="table table-hover mb-0">
        <thead class="table-light">
            <tr>
                <th scope="col">Référence</th>
                <th scope="col">Objet</th>
                <th scope="col">Expéditeur</th>
                <th scope="col">Date de réception</th>
                <th scope="col">Statut</th>
                <th scope="col">Actions</th>
            </tr>
        </thead>
        <tbody>
            @forelse($courriers as $courrier)
                <tr>
                    <td>{{ $courrier->numero }}</td>
                    <td>{{ $courrier->objet }}</td>
                    <td>{{ $courrier->contribuable?->nom_raison_sociale ?: 'N/A' }}</td>
                    <td>
                        @if($courrier->date_reception)
                            {{ $courrier->date_reception->format('d/m/Y') }}
                            @if($courrier->heure_reception)
                                {{ $courrier->heure_reception }}
                            @endif
                        @else
                            -
                        @endif
                    </td>
                    <td>
                        <span class="badge bg-{{ $courrier->statut->badgeColor() }}">
                            {{ $courrier->statut->label() }}
                        </span>
                    </td>
                    <td>
                        @if ($courrier->statut === \App\Enums\StatutCourrier::Suspendu)
                            <a href="{{ route('courriers.traitement.show', $courrier) }}" class="btn btn-sm btn-outline-info">
                                <i class="bi bi-arrow-clockwise me-1"></i>Reprendre
                            </a>
                        @else
                            <a href="{{ route('courriers.traitement.show', $courrier) }}" class="btn btn-sm btn-primary">
                                <i class="bi bi-gear me-1"></i>Traiter
                            </a>
                        @endif
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" class="text-center text-muted py-4">
                        @if (! empty($enRemplacement) && $sujetCourant)
                            Aucun courrier réceptionné pour {{ $sujetCourant->name }}.
                        @else
                            Aucun courrier à traiter pour le moment.
                        @endif
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>
