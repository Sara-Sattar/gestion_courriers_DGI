@php
    use App\Enums\StatutCourrier;

    $showReceptionDate = $showReceptionDate ?? false;
    $mode = $mode ?? 'attente';
@endphp

<div data-reception-bloc="{{ $formId }}">
    @if ($mode === 'attente')
        <div class="reception-toolbar px-4 py-3 border-bottom">
            <div class="form-check mb-0">
                <input type="checkbox" id="{{ $selectAllId }}" class="form-check-input" data-select-all="{{ $formId }}">
                <label for="{{ $selectAllId }}" class="form-check-label text-muted small">Tout sélectionner</label>
            </div>
            <button type="button" class="btn btn-primary btn-sm" data-confirm-bulk="{{ $formId }}">
                <i class="bi bi-check2-circle me-1"></i>Confirmer la réception
            </button>
        </div>
    @endif

    <div class="table-responsive">
        <table class="table table-hover reception-table mb-0 align-middle">
            <thead class="table-light">
                <tr>
                    @if ($mode === 'attente')
                        <th scope="col" style="width: 2.5rem;"></th>
                    @endif
                    <th scope="col">N°</th>
                    <th scope="col">Catégorie / Type</th>
                    <th scope="col">Objet</th>
                    <th scope="col">Contribuable</th>
                    <th scope="col">Unité destinataire</th>
                    <th scope="col">{{ $showReceptionDate ? 'Date de réception' : 'Date d\'arrivée' }}</th>
                    <th scope="col">Statut</th>
                    <th scope="col" class="text-end">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($courriers as $courrier)
                    <tr>
                        @if ($mode === 'attente')
                            <td>
                                <input
                                    type="checkbox"
                                    value="{{ $courrier->id }}"
                                    class="form-check-input"
                                    data-courrier-checkbox="{{ $formId }}"
                                >
                            </td>
                        @endif
                        <td class="text-nowrap fw-semibold">
                            <a href="{{ route('courriers.show', $courrier) }}" class="text-decoration-none">
                                {{ $courrier->numero }}
                            </a>
                        </td>
                        <td>
                            <span class="fw-medium">{{ $courrier->categorie?->nom ?? '—' }}</span>
                            <span class="reception-precision">{{ $courrier->typeCourrier?->nom ?? '—' }}</span>
                        </td>
                        <td>
                            <span class="reception-objet d-inline-block text-truncate" title="{{ $courrier->objet }}">
                                {{ $courrier->objet }}
                            </span>
                        </td>
                        <td>{{ $courrier->contribuable?->nom_raison_sociale ?? '—' }}</td>
                        <td>
                            {{ $courrier->uniteActuelle?->nom ?? $courrier->uniteDestinataire?->nom ?? '—' }}
                            @if ($courrier->uniteActuelle?->code || $courrier->uniteDestinataire?->code)
                                <span class="reception-precision">
                                    {{ $courrier->uniteActuelle?->code ?? $courrier->uniteDestinataire?->code }}
                                </span>
                            @endif
                        </td>
                        <td class="text-nowrap">
                            @if ($showReceptionDate)
                                @if ($courrier->date_reception)
                                    {{ $courrier->date_reception->format('d/m/Y') }}
                                    @if ($courrier->heure_reception)
                                        <span class="reception-precision">
                                            {{ \Illuminate\Support\Carbon::parse($courrier->heure_reception)->format('H:i') }}
                                        </span>
                                    @endif
                                @else
                                    —
                                @endif
                            @else
                                @php $dateArrivee = $courrier->dateArriveeUnite(); @endphp
                                {{ $dateArrivee->format('d/m/Y') }}
                                <span class="reception-precision">{{ $dateArrivee->format('H:i') }}</span>
                                @if ($courrier->derniere_reaffectation_at)
                                    <span class="reception-precision">Réaffecté</span>
                                @endif
                            @endif
                        </td>
                        <td>
                            <span class="badge bg-{{ $courrier->statut->badgeColor() }}-subtle text-{{ $courrier->statut->badgeColor() }}-emphasis border border-{{ $courrier->statut->badgeColor() }}-subtle">
                                <i class="bi {{ $courrier->statut->icon() }} me-1"></i>{{ $courrier->statut->label() }}
                            </span>
                        </td>
                        <td class="text-end">
                            <div class="reception-actions">
                                @if ($mode === 'attente' && in_array($courrier->statut, [StatutCourrier::Depose, StatutCourrier::Reaffecte], true))
                                    <form action="{{ route('courriers.confirmer-reception', $courrier) }}" method="POST" onsubmit="return confirm('Confirmer la réception de {{ $courrier->numero }} ?');">
                                        @csrf
                                        <button type="submit" class="btn btn-sm btn-outline-success" title="Accepter / confirmer la réception">
                                            <i class="bi bi-check2-circle"></i>
                                        </button>
                                    </form>
                                    @can('courriers.refuser_reception')
                                        @if ($courrier->statut === StatutCourrier::Reaffecte)
                                            <button
                                                type="button"
                                                class="btn btn-sm btn-outline-warning"
                                                title="Refuser la réaffectation (retour n−1)"
                                                data-refus-reaffectation
                                                data-courrier-id="{{ $courrier->id }}"
                                                data-courrier-numero="{{ $courrier->numero }}"
                                            >
                                                <i class="bi bi-arrow-return-left"></i>
                                            </button>
                                        @else
                                            <button
                                                type="button"
                                                class="btn btn-sm btn-outline-danger"
                                                title="Refuser la réception (retour B.O.)"
                                                data-refus-reception
                                                data-courrier-id="{{ $courrier->id }}"
                                                data-courrier-numero="{{ $courrier->numero }}"
                                            >
                                                <i class="bi bi-slash-circle"></i>
                                            </button>
                                        @endif
                                    @endcan
                                @endif

                                @if ($mode === 'receptionne')
                                    @if ($courrier->statut === StatutCourrier::Receptionne)
                                        <form action="{{ route('courriers.annuler-reception', $courrier) }}" method="POST" onsubmit="return confirm('Annuler la réception de {{ $courrier->numero }} ? Le statut repassera à Déposé.');">
                                            @csrf
                                            <button type="submit" class="btn btn-sm btn-outline-danger" title="Annuler la réception">
                                                <i class="bi bi-x-circle"></i>
                                            </button>
                                        </form>
                                    @endif
                                    <button
                                        type="button"
                                        class="btn btn-sm btn-outline-secondary"
                                        title="Réaffecter"
                                        data-reaffecter
                                        data-courrier-id="{{ $courrier->id }}"
                                        data-courrier-numero="{{ $courrier->numero }}"
                                    >
                                        <i class="bi bi-arrow-left-right"></i>
                                    </button>
                                @endif
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="{{ $mode === 'attente' ? 9 : 8 }}">
                            <div class="reception-vide">
                                <i class="bi bi-inbox" aria-hidden="true"></i>
                                <p class="mb-0">{{ $emptyMessage }}</p>
                            </div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
