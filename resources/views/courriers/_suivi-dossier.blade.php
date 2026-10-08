{{-- Suivi complet du dossier (traçabilité) --}}
@php
    $evenements = $courrier->relationLoaded('suiviDossier')
        ? $courrier->suiviDossier
        : $courrier->suiviDossier()->with(['utilisateur', 'pourCompteDe'])->get();
@endphp

<div class="card border-0 shadow-sm {{ $cardClass ?? '' }}">
    <div class="card-header bg-white d-flex flex-wrap justify-content-between align-items-center gap-2">
        <div>
                <h5 class="mb-0">
                <i class="bi bi-signpost-2 me-2 text-primary"></i>Traçabilité du dossier
            </h5>
            <p class="text-muted small mb-0 mt-1">
                Toutes les actions du dossier, dans l'ordre chronologique.
            </p>
        </div>
        @if ($evenements->isNotEmpty())
            <span class="badge text-bg-light border">{{ $evenements->count() }} événement{{ $evenements->count() > 1 ? 's' : '' }}</span>
        @endif
    </div>
    <div class="card-body">
        @if ($evenements->isEmpty())
            <div class="suivi-vide text-center text-muted py-4">
                <i class="bi bi-clock-history d-block mb-2 fs-3 opacity-50"></i>
                Aucun événement enregistré pour ce dossier.
            </div>
        @else
            <ol class="suivi-timeline list-unstyled mb-0">
                @foreach ($evenements as $index => $evenement)
                    @php
                        $couleur = $evenement->action->badgeClass();
                        $estDernier = $loop->last;
                    @endphp
                    <li class="suivi-timeline-item {{ $estDernier ? 'is-latest' : '' }}">
                        <div class="suivi-timeline-marker bg-{{ $couleur }}-subtle text-{{ $couleur }}-emphasis border border-{{ $couleur }}-subtle">
                            <i class="bi {{ $evenement->action->icon() }}"></i>
                        </div>
                        <div class="suivi-timeline-content">
                            <div class="d-flex flex-wrap align-items-center gap-2 mb-1">
                                <span class="badge bg-{{ $couleur }}-subtle text-{{ $couleur }}-emphasis border border-{{ $couleur }}-subtle">
                                    {{ $evenement->action->label() }}
                                </span>
                                <span class="small text-muted">
                                    {{ $evenement->created_at->format('d/m/Y à H:i') }}
                                </span>
                                @if ($estDernier)
                                    <span class="badge text-bg-light border small">Dernière action</span>
                                @endif
                            </div>
                            <div class="fw-semibold small mb-1">
                                {{ $evenement->utilisateur?->name ?? 'Utilisateur inconnu' }}
                                @if ($evenement->pourCompteDe)
                                    <span class="text-muted fw-normal">
                                        — pour le compte de <strong>{{ $evenement->pourCompteDe->name }}</strong>
                                    </span>
                                @endif
                            </div>
                            @if (filled($evenement->commentaire))
                                <p class="mb-0 small text-muted">{{ $evenement->commentaire }}</p>
                            @endif
                        </div>
                    </li>
                @endforeach
            </ol>
        @endif
    </div>
</div>
