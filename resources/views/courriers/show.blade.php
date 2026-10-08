@extends('layouts.app')

@section('title', $courrier->numero)

@section('page-header')
    <div class="col">
        <div class="d-flex flex-wrap align-items-center gap-2 mb-1">
            <h2 class="h3 mb-0 fw-bold">{{ $courrier->numero }}</h2>
            <span class="badge bg-{{ $courrier->statut->badgeColor() }} fs-6">
                <i class="bi {{ $courrier->statut->icon() }} me-1"></i>{{ $courrier->statut->label() }}
            </span>
        </div>
        <p class="text-muted mb-0 small">
            <i class="bi bi-calendar3 me-1"></i>Enregistré le {{ $courrier->date_depot->format('d/m/Y à H:i') }}
            par {{ $courrier->utilisateur->name }}
        </p>
    </div>
    <div class="col-auto">
        <div class="d-flex flex-wrap gap-2">
            @can('courriers.annuler_depot')
                @if (in_array($courrier->statut, [\App\Enums\StatutCourrier::Depose, \App\Enums\StatutCourrier::Reaffecte], true))
                    <button type="button" class="btn btn-outline-danger" data-bs-toggle="modal" data-bs-target="#annulerDepotModal">
                        <i class="bi bi-x-circle me-1"></i>Annuler le dépôt
                    </button>
                @endif
            @endcan
            <a href="{{ route('courriers.recu', $courrier) }}" class="btn btn-outline-primary" target="_blank" id="lien-recu">
                <i class="bi bi-printer me-1"></i>Imprimer le reçu
            </a>
            <a href="{{ route('courriers.recu.telecharger', $courrier) }}" class="btn btn-primary">
                <i class="bi bi-download me-1"></i>Télécharger le reçu
            </a>
        </div>
    </div>
@endsection

@push('styles')
<link rel="stylesheet" href="{{ asset('css/suivi-dossier.css') }}">
<style>
    .detail-section {
        padding-bottom: 1.25rem;
        margin-bottom: 1.25rem;
        border-bottom: 1px solid var(--bs-border-color-translucent);
    }

    .detail-section:last-child {
        padding-bottom: 0;
        margin-bottom: 0;
        border-bottom: none;
    }

    .detail-section-title {
        color: var(--bs-secondary-color);
        font-size: 0.7rem;
        font-weight: 600;
        letter-spacing: 0.06em;
        margin-bottom: 0.85rem;
        text-transform: uppercase;
    }

    .detail-champ + .detail-champ {
        margin-top: 0.75rem;
    }

    .detail-champ-label {
        color: var(--bs-secondary-color);
        display: block;
        font-size: 0.75rem;
        margin-bottom: 0.15rem;
    }

    .detail-champ-value {
        font-weight: 600;
        line-height: 1.4;
        word-break: break-word;
    }

    .detail-identifiants {
        display: grid;
        gap: 0.75rem;
        grid-template-columns: repeat(2, minmax(0, 1fr));
    }

    .detail-objet {
        background-color: var(--bs-light);
        border: 1px solid var(--bs-border-color-translucent);
        border-radius: 0.5rem;
        font-weight: 600;
        line-height: 1.5;
        padding: 0.85rem 1rem;
    }

    .detail-badge-list {
        display: flex;
        flex-wrap: wrap;
        gap: 0.4rem;
    }

    .detail-badge-list .badge {
        font-size: 0.8rem;
        font-weight: 500;
        padding: 0.4em 0.75em;
    }

    .detail-empty {
        color: var(--bs-secondary-color);
        font-size: 0.9rem;
        padding: 1.5rem 1rem;
        text-align: center;
    }

    .detail-empty i {
        display: block;
        font-size: 1.75rem;
        margin-bottom: 0.5rem;
        opacity: 0.45;
    }

    .contribuable-nom {
        font-size: 1.05rem;
        font-weight: 700;
        line-height: 1.35;
        margin-bottom: 1rem;
    }

    @media (max-width: 575.98px) {
        .detail-identifiants {
            grid-template-columns: 1fr;
        }
    }
</style>
@endpush

@section('content')
@php
    $contribuable = $courrier->contribuable;
    $identifiants = collect([
        'IFU' => $contribuable->ifu,
        'CIN' => $contribuable->cin,
        'ICE' => $contribuable->ice,
        'N° TP' => $contribuable->numero_tp,
        'N° TH' => $contribuable->numero_th,
    ])->filter(fn ($valeur) => filled($valeur));

    $coordonnees = collect([
        'Ville' => $contribuable->ville,
        'Adresse' => $contribuable->adresse,
        'Téléphone' => $contribuable->telephone,
        'Email' => $contribuable->email,
    ])->filter(fn ($valeur) => filled($valeur));
@endphp

<div class="row g-4">
    <div class="col-lg-4">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-header bg-white">
                <h5 class="mb-0"><i class="bi bi-person-vcard me-2 text-primary"></i>Contribuable</h5>
            </div>
            <div class="card-body">
                <div class="contribuable-nom">{{ $contribuable->nom_raison_sociale }}</div>

                @if ($identifiants->isNotEmpty())
                    <div class="detail-section">
                        <div class="detail-section-title">Identifiants</div>
                        <div class="detail-identifiants">
                            @foreach ($identifiants as $libelle => $valeur)
                                <div class="detail-champ">
                                    <span class="detail-champ-label">{{ $libelle }}</span>
                                    <span class="detail-champ-value">{{ $valeur }}</span>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif

                @if ($coordonnees->isNotEmpty())
                    <div class="detail-section">
                        <div class="detail-section-title">Coordonnées</div>
                        @foreach ($coordonnees as $libelle => $valeur)
                            <div class="detail-champ">
                                <span class="detail-champ-label">{{ $libelle }}</span>
                                <span class="detail-champ-value">{{ $valeur }}</span>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>
    </div>

    <div class="col-lg-8">
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white">
                <h5 class="mb-0"><i class="bi bi-envelope-open me-2 text-primary"></i>Informations du courrier</h5>
            </div>
            <div class="card-body">
                <div class="detail-section">
                    <div class="detail-section-title">Classification</div>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <div class="detail-champ">
                                <span class="detail-champ-label">Catégorie</span>
                                <span class="detail-champ-value">{{ $courrier->categorie->nom }}</span>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="detail-champ">
                                <span class="detail-champ-label">Type de courrier</span>
                                <span class="detail-champ-value">{{ $courrier->typeCourrier->nom }}</span>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="detail-section">
                    <div class="detail-section-title">Objet</div>
                    <div class="detail-objet">{{ $courrier->objet ?: '—' }}</div>
                </div>

                <div class="detail-section">
                    <div class="detail-section-title">Affectation</div>
                    <div class="row g-3">
                        <div class="col-md-4">
                            <div class="detail-champ">
                                <span class="detail-champ-label">Type d'unité</span>
                                <span class="detail-champ-value">{{ $courrier->uniteDestinataire->type_unite }}</span>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="detail-champ">
                                <span class="detail-champ-label">Unité destinataire</span>
                                <span class="detail-champ-value">{{ $courrier->uniteDestinataire->nom }}</span>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="detail-champ">
                                <span class="detail-champ-label">Code unité</span>
                                <span class="detail-champ-value"><code>{{ $courrier->code_unite }}</code></span>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="detail-section">
                    <div class="detail-section-title">Périmètre fiscal</div>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <div class="detail-champ">
                                <span class="detail-champ-label">Impôts concernés</span>
                                <div class="detail-badge-list">
                                    @foreach ($courrier->impots as $impot)
                                        <span class="badge rounded-pill text-bg-light border">{{ $impot->impot->label() }}</span>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="detail-champ">
                                <span class="detail-champ-label">Années concernées</span>
                                <div class="detail-badge-list">
                                    @foreach ($courrier->annees->sortByDesc('annee') as $annee)
                                        <span class="badge rounded-pill text-bg-light border">{{ $annee->annee }}</span>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                @if (filled($courrier->remarque))
                    <div class="detail-section">
                        <div class="detail-section-title">Remarque</div>
                        <div class="detail-champ-value">{{ $courrier->remarque }}</div>
                    </div>
                @endif
            </div>
        </div>

        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white d-flex justify-content-between align-items-center">
                <h5 class="mb-0"><i class="bi bi-paperclip me-2 text-primary"></i>Pièces jointes</h5>
                @if ($courrier->piecesJointes->isNotEmpty())
                    <span class="badge text-bg-light border">{{ $courrier->piecesJointes->count() }}</span>
                @endif
            </div>
            <div class="card-body">
                @if ($courrier->piecesJointes->isEmpty())
                    <div class="detail-empty">
                        <i class="bi bi-file-earmark"></i>
                        Aucune pièce jointe n'a été ajoutée à ce courrier.
                    </div>
                @else
                    <div class="list-group list-group-flush">
                        @foreach ($courrier->piecesJointes as $piece)
                            <div class="list-group-item px-0">
                                <div class="d-flex flex-wrap justify-content-between align-items-center gap-2">
                                    <div>
                                        <div class="fw-semibold">
                                            <i class="bi {{ $piece->iconeBootstrap() }} me-2 text-primary"></i>{{ $piece->nom_original }}
                                        </div>
                                        <div class="small text-muted">
                                            {{ strtoupper($piece->extension) }} · {{ number_format($piece->taille / 1024, 2, ',', ' ') }} Ko
                                        </div>
                                    </div>
                                    <div class="d-flex gap-2">
                                        @if ($piece->estPrevisualisable())
                                            <a href="{{ route('courriers.pieces-jointes.show', [$courrier, $piece]) }}" class="btn btn-sm btn-outline-secondary" target="_blank">
                                                <i class="bi bi-eye me-1"></i>Voir
                                            </a>
                                        @endif
                                        <a href="{{ route('courriers.pieces-jointes.telecharger', [$courrier, $piece]) }}" class="btn btn-sm btn-outline-primary">
                                            <i class="bi bi-download me-1"></i>Télécharger
                                        </a>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>

        @include('courriers._suivi-dossier', ['courrier' => $courrier])
    </div>
</div>

@can('courriers.annuler_depot')
    @if (in_array($courrier->statut, [\App\Enums\StatutCourrier::Depose, \App\Enums\StatutCourrier::Reaffecte], true))
        <div class="modal fade" id="annulerDepotModal" tabindex="-1" aria-labelledby="annulerDepotModalLabel" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <form method="POST" action="{{ route('courriers.annuler-depot', $courrier) }}">
                        @csrf
                        <div class="modal-header">
                            <h5 class="modal-title" id="annulerDepotModalLabel">Annuler le dépôt</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fermer"></button>
                        </div>
                        <div class="modal-body">
                            <p class="small text-muted">
                                Le dossier <strong>{{ $courrier->numero }}</strong> sera marqué annulé (conservé en base).
                            </p>
                            <label for="motif_annulation" class="form-label">Motif <x-required /></label>
                            <textarea name="motif" id="motif_annulation" rows="3" class="form-control" required minlength="5"
                                      placeholder="Indiquez le motif d'annulation…"></textarea>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-light border" data-bs-dismiss="modal">Fermer</button>
                            <button type="submit" class="btn btn-danger">Confirmer l'annulation</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif
@endcan
@endsection

@if (session('ouvrir_recu'))
    @push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            window.open(@json(route('courriers.recu', $courrier)), '_blank');
        });
    </script>
    @endpush
@endif
