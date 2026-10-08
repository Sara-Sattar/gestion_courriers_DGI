@if ($courriers->isEmpty())
    <div class="text-center text-muted py-5">
        <i class="bi bi-inbox fs-1 d-block mb-2"></i>
        Aucun courrier enregistré pour le moment.
    </div>
@else
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0 dashboard-courriers-table">
            <thead class="table-light">
                <tr>
                    <th style="width: 14%;">N° Courrier</th>
                    <th style="width: 22%;">Contribuable</th>
                    <th style="width: 14%;">Catégorie</th>
                    <th style="width: 14%;">Date dépôt</th>
                    <th style="width: 18%;">Destinataire</th>
                    <th style="width: 18%;">Statut</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($courriers as $courrier)
                    <tr
                        class="dashboard-courrier-row"
                        onclick="window.location='{{ route('courriers.show', $courrier) }}'"
                    >
                        <td>
                            <a href="{{ route('courriers.show', $courrier) }}" class="fw-semibold text-decoration-none dashboard-courrier-numero" onclick="event.stopPropagation()">
                                {{ $courrier->numero }}
                            </a>
                        </td>
                        <td class="fw-medium">{{ $courrier->contribuable->nom_raison_sociale }}</td>
                        <td>{{ $courrier->categorie->nom }}</td>
                        <td>{{ $courrier->date_depot->format('d/m/Y H:i') }}</td>
                        <td>
                            @if ($destinataire = $courrier->destinataireAffiche())
                                {{ $destinataire->name }}
                            @elseif ($courrier->uniteActuelle)
                                {{ $courrier->uniteActuelle->nom }}
                            @else
                                <span class="text-muted">N/A</span>
                            @endif
                        </td>
                        <td>
                            <span class="courrier-statut courrier-statut--{{ $courrier->statut->value }}">
                                <i class="bi {{ $courrier->statut->icon() }}"></i>
                                {{ $courrier->statut->label() }}
                            </span>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    <div class="dashboard-pagination">
        {{ $courriers->withQueryString()->links() }}
    </div>
@endif
