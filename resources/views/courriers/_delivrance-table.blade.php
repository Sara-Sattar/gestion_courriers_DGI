<div class="table-responsive">
    <table class="table table-hover align-middle mb-0">
        <thead class="table-light">
            <tr>
                <th>Référence</th>
                <th>Objet</th>
                <th>Contribuable</th>
                <th>Date traitement</th>
                <th>Document</th>
                <th>Traité par</th>
                <th></th>
            </tr>
        </thead>
        <tbody>
            @forelse ($courriers as $courrier)
                <tr>
                    <td class="fw-semibold">{{ $courrier->numero }}</td>
                    <td>{{ $courrier->objet }}</td>
                    <td>{{ $courrier->contribuable?->nom_raison_sociale ?: 'N/A' }}</td>
                    <td>{{ $courrier->date_traitement?->format('d/m/Y') ?: '-' }}</td>
                    <td>
                        @if ($courrier->document_genere)
                            <span class="badge text-bg-success">À délivrer</span>
                        @else
                            <span class="badge text-bg-secondary">Sans document</span>
                        @endif
                    </td>
                    <td>{{ $courrier->traitePar?->name ?: '-' }}</td>
                    <td class="text-nowrap">
                        <a href="{{ route('courriers.delivrance.show', $courrier) }}" class="btn btn-sm btn-primary">
                            <i class="bi bi-box-seam me-1"></i>
                            {{ $courrier->document_genere ? 'Délivrer' : 'Clôturer' }}
                        </a>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="7" class="text-center text-muted py-4">
                        Aucun document en attente de délivrance.
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>
