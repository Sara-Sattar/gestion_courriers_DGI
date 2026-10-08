@extends('layouts.app')

@section('title', 'Traitement — '.$courrier->numero)

@push('styles')
<link rel="stylesheet" href="{{ asset('css/suivi-dossier.css') }}">
<link rel="stylesheet" href="{{ asset('css/formulaires.css') }}">
@endpush

@section('page-header')
    <div class="col">
        <x-lien-retour :href="route('courriers.traitement.index')">Traitement</x-lien-retour>
        <div class="d-flex flex-wrap align-items-center gap-2 mb-1">
            <h2 class="h3 mb-0 fw-bold">{{ $courrier->numero }}</h2>
            <span class="badge bg-{{ $courrier->statut->badgeColor() }}">
                <i class="bi {{ $courrier->statut->icon() }} me-1"></i>{{ $courrier->statut->label() }}
            </span>
        </div>
        <p class="text-muted mb-0 small">
            @if (! empty($enRemplacement) && $sujetCourant)
                Traitement pour le compte de <strong>{{ $sujetCourant->name }}</strong>
            @else
                Dossier à traiter
            @endif
        </p>
    </div>
    @if (! empty($peutReprendre))
        <div class="col-auto">
            <form method="POST" action="{{ route('courriers.reprendre', $courrier) }}">
                @csrf
                <button type="submit" class="btn btn-info text-white">
                    <i class="bi bi-arrow-clockwise me-1"></i>Reprendre le traitement
                </button>
            </form>
        </div>
    @endif
@endsection

@section('content')
@php
    $resultat = old('resultat', 'favorable');
    $agentTraitant = $sujetCourant?->name ?? auth()->user()?->name;
@endphp

<div class="row g-4">
    <div class="col-lg-4">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-header bg-white">
                <h5 class="mb-0"><i class="bi bi-person-vcard me-2 text-primary"></i>Contribuable</h5>
            </div>
            <div class="card-body">
                <dl class="row mb-0">
                    <dt class="col-sm-5">Nom</dt>
                    <dd class="col-sm-7">{{ $courrier->contribuable?->nom_raison_sociale ?: 'N/A' }}</dd>
                    <dt class="col-sm-5">IFU</dt>
                    <dd class="col-sm-7">{{ $courrier->contribuable?->ifu ?: '-' }}</dd>
                    <dt class="col-sm-5">CIN</dt>
                    <dd class="col-sm-7">{{ $courrier->contribuable?->cin ?: '-' }}</dd>
                    <dt class="col-sm-5">Ville</dt>
                    <dd class="col-sm-7">{{ $courrier->contribuable?->ville ?: '-' }}</dd>
                </dl>
            </div>
        </div>
    </div>

    <div class="col-lg-8">
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white">
                <h5 class="mb-0"><i class="bi bi-envelope-open me-2 text-primary"></i>Informations du courrier</h5>
            </div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-6">
                        <div class="small text-muted">Objet</div>
                        <div class="fw-semibold">{{ $courrier->objet ?: '—' }}</div>
                    </div>
                    <div class="col-md-3">
                        <div class="small text-muted">Unité actuelle</div>
                        <div class="fw-semibold">{{ $courrier->uniteActuelle?->nom ?: 'N/A' }}</div>
                    </div>
                    <div class="col-md-3">
                        <div class="small text-muted">Date de réception</div>
                        <div class="fw-semibold">{{ $courrier->date_reception?->format('d/m/Y') ?: '-' }}</div>
                    </div>
                    @if ($courrier->motif_suspension)
                        <div class="col-12">
                            <div class="alert alert-warning mb-0 py-2">
                                <strong>Suspension :</strong> {{ $courrier->motif_suspension }}
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        @if (! empty($peutReprendre))
            <div class="alert alert-info border-0 shadow-sm">
                <i class="bi bi-pause-circle me-1"></i>
                Ce dossier est suspendu. Cliquez sur <strong>Reprendre le traitement</strong> pour le remettre dans la file
                et pouvoir le traiter favorablement ou le rejeter.
            </div>
        @endif

        @if ($peutTraiter)
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white">
                    <h5 class="mb-0"><i class="bi bi-patch-check me-2 text-primary"></i>Résultat du traitement</h5>
                </div>
                <div class="card-body">
                    <x-erreurs-formulaire />

                    <form method="POST" action="{{ route('courriers.traiter', $courrier) }}" id="form-traitement">
                        @csrf

                        <div class="mb-3">
                            <label class="form-label fw-semibold">Résultat <x-required /></label>
                            <div class="d-flex flex-wrap gap-3">
                                @foreach ([
                                    'favorable' => ['label' => 'Favorable', 'class' => 'success'],
                                    'rejet' => ['label' => 'Rejet', 'class' => 'danger'],
                                    'suspendu' => ['label' => 'Suspendu', 'class' => 'warning'],
                                ] as $valeur => $meta)
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" name="resultat" id="resultat-{{ $valeur }}"
                                               value="{{ $valeur }}" @checked($resultat === $valeur)>
                                        <label class="form-check-label" for="resultat-{{ $valeur }}">
                                            <span class="badge text-bg-{{ $meta['class'] }}">{{ $meta['label'] }}</span>
                                        </label>
                                    </div>
                                @endforeach
                            </div>
                        </div>

                        <div class="row g-3 mb-3">
                            <div class="col-md-6">
                                <label for="date_traitement" class="form-label fw-semibold">Date de traitement <x-required /></label>
                                <input type="date" name="date_traitement" id="date_traitement" class="form-control"
                                       value="{{ old('date_traitement', now()->toDateString()) }}" max="{{ now()->toDateString() }}" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Utilisateur ayant traité</label>
                                <input type="text" class="form-control" value="{{ $agentTraitant }}" readonly>
                            </div>
                        </div>

                        <div id="bloc-favorable" class="border rounded p-3 mb-3 bg-light" @style(['display: none' => $resultat !== 'favorable'])>
                            <label class="form-label fw-semibold">Document généré <x-required /></label>
                            <div class="d-flex gap-3">
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="document_genere" id="doc-oui" value="1"
                                           @checked((string) old('document_genere', '1') === '1')>
                                    <label class="form-check-label" for="doc-oui">Oui</label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="document_genere" id="doc-non" value="0"
                                           @checked((string) old('document_genere') === '0')>
                                    <label class="form-check-label" for="doc-non">Non</label>
                                </div>
                            </div>
                            <div class="form-text mt-2">
                                Après validation, le dossier revient automatiquement à l'Accueil (file Documents à délivrer).
                                La délivrance au contribuable est assurée uniquement par l'Accueil.
                            </div>
                        </div>

                        <div id="bloc-rejet" class="border rounded p-3 mb-3 bg-light" @style(['display: none' => $resultat !== 'rejet'])>
                            <label for="motif_rejet" class="form-label fw-semibold">Motif du rejet <x-required /></label>
                            <textarea name="motif_rejet" id="motif_rejet" rows="3" class="form-control">{{ old('motif_rejet') }}</textarea>
                        </div>

                        <div id="bloc-suspendu" class="border rounded p-3 mb-3 bg-light" @style(['display: none' => $resultat !== 'suspendu'])>
                            <label for="motif_suspension" class="form-label fw-semibold">Motif de suspension <x-required /></label>
                            <textarea name="motif_suspension" id="motif_suspension" rows="3" class="form-control">{{ old('motif_suspension') }}</textarea>
                            <div class="form-text">Le dossier pourra être repris ultérieurement via « Reprendre le traitement ».</div>
                        </div>

                        <div class="d-flex justify-content-end gap-2">
                            <a href="{{ route('courriers.traitement.index') }}" class="btn btn-outline-secondary">Annuler</a>
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-check2-circle me-1"></i>Valider le traitement
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        @endif

        @if (! empty($peutReaffecter))
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white">
                    <h5 class="mb-0"><i class="bi bi-arrow-left-right me-2 text-primary"></i>Réaffectation</h5>
                </div>
                <div class="card-body">
                    <p class="small text-muted">Vous pouvez réaffecter ce dossier depuis l'écran de traitement, sans passer par la réception.</p>
                    <form method="POST" action="{{ route('courriers.reaffecter', $courrier) }}">
                        @csrf
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label for="unite_destination_id" class="form-label fw-semibold">Unité de destination <x-required /></label>
                                <select name="unite_destination_id" id="unite_destination_id" class="form-select" required>
                                    <option value="">Sélectionner une unité</option>
                                    @foreach ($allowedUnites as $unite)
                                        <option value="{{ $unite->id }}">{{ $unite->nom }} ({{ $unite->code }}) — {{ $unite->type_unite }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label for="motif_reaffectation" class="form-label fw-semibold">Motif <x-required /></label>
                                <textarea name="motif" id="motif_reaffectation" rows="2" class="form-control" required minlength="5"></textarea>
                            </div>
                        </div>
                        <div class="d-flex justify-content-end mt-3">
                            <button type="submit" class="btn btn-outline-primary">
                                <i class="bi bi-arrow-left-right me-1"></i>Réaffecter
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        @endif

        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white">
                <h5 class="mb-0"><i class="bi bi-paperclip me-2 text-primary"></i>Pièces jointes</h5>
            </div>
            <div class="card-body">
                @if ($courrier->piecesJointes->isEmpty())
                    <div class="alert alert-light border mb-0">Aucune pièce jointe.</div>
                @else
                    <div class="list-group list-group-flush">
                        @foreach ($courrier->piecesJointes as $piece)
                            <div class="list-group-item px-0 d-flex justify-content-between align-items-center">
                                <span><i class="bi {{ $piece->iconeBootstrap() }} me-2 text-primary"></i>{{ $piece->nom_original }}</span>
                                <a href="{{ route('courriers.pieces-jointes.telecharger', [$courrier, $piece]) }}" class="btn btn-sm btn-outline-primary">Télécharger</a>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>

        @include('courriers._suivi-dossier', ['courrier' => $courrier])
    </div>
</div>
@endsection

@push('scripts')
<script>
(function () {
    const form = document.getElementById('form-traitement');
    if (!form) return;
    const blocs = {
        favorable: document.getElementById('bloc-favorable'),
        rejet: document.getElementById('bloc-rejet'),
        suspendu: document.getElementById('bloc-suspendu'),
    };
    function afficher() {
        const resultat = form.querySelector('input[name="resultat"]:checked')?.value;
        Object.entries(blocs).forEach(([cle, el]) => {
            if (el) el.style.display = cle === resultat ? '' : 'none';
        });
    }
    form.querySelectorAll('input[name="resultat"]').forEach((input) => input.addEventListener('change', afficher));
    afficher();
})();
</script>
@endpush
