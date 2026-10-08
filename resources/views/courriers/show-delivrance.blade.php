@extends('layouts.app')

@section('title', 'Délivrance — '.$courrier->numero)

@push('styles')
<link rel="stylesheet" href="{{ asset('css/suivi-dossier.css') }}">
<link rel="stylesheet" href="{{ asset('css/formulaires.css') }}">
@endpush

@section('page-header')
    <div class="col">
        <x-lien-retour :href="route('courriers.delivrance.index')">Délivrance</x-lien-retour>
        <div class="d-flex flex-wrap align-items-center gap-2 mb-1">
            <h2 class="h3 mb-0 fw-bold">{{ $courrier->numero }}</h2>
            <span class="badge bg-{{ $courrier->statut->badgeColor() }}">{{ $courrier->statut->label() }}</span>
        </div>
        <p class="text-muted mb-0 small">Retrait du document — Accueil</p>
    </div>
@endsection

@section('content')
@php
    $mode = old('mode_livraison', 'interesse');
    $agent = $sujetCourant?->name ?? auth()->user()?->name;
@endphp

<div class="row g-4">
    <div class="col-lg-5">
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white">
                <h5 class="mb-0"><i class="bi bi-person-vcard me-2 text-primary"></i>Contribuable</h5>
            </div>
            <div class="card-body">
                <dl class="row mb-0">
                    <dt class="col-5">Nom</dt>
                    <dd class="col-7">{{ $courrier->contribuable?->nom_raison_sociale ?: 'N/A' }}</dd>
                    <dt class="col-5">IFU</dt>
                    <dd class="col-7">{{ $courrier->contribuable?->ifu ?: '-' }}</dd>
                    <dt class="col-5">CIN</dt>
                    <dd class="col-7">{{ $courrier->contribuable?->cin ?: '-' }}</dd>
                </dl>
            </div>
        </div>

        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white">
                <h5 class="mb-0"><i class="bi bi-info-circle me-2 text-primary"></i>Traitement</h5>
            </div>
            <div class="card-body">
                <dl class="row mb-0">
                    <dt class="col-5">Objet</dt>
                    <dd class="col-7">{{ $courrier->objet ?: '—' }}</dd>
                    <dt class="col-5">Date traitement</dt>
                    <dd class="col-7">{{ $courrier->date_traitement?->format('d/m/Y') ?: '-' }}</dd>
                    <dt class="col-5">Traité par</dt>
                    <dd class="col-7">{{ $courrier->traitePar?->name ?: '-' }}</dd>
                    <dt class="col-5">Document</dt>
                    <dd class="col-7">{{ $courrier->document_genere ? 'Oui' : 'Non' }}</dd>
                </dl>
            </div>
        </div>
    </div>

    <div class="col-lg-7">
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white">
                <h5 class="mb-0"><i class="bi bi-box-seam me-2 text-primary"></i>
                    {{ $courrier->document_genere ? 'Retrait du document' : 'Clôture du dossier' }}
                </h5>
            </div>
            <div class="card-body">
                <x-erreurs-formulaire />

                @if ($courrier->document_genere)
                    <form method="POST" action="{{ route('courriers.delivrer', $courrier) }}" id="form-delivrance">
                        @csrf

                        <div class="row g-3 mb-3">
                            <div class="col-md-6">
                                <label for="date_delivrance" class="form-label fw-semibold">Date de délivrance <x-required /></label>
                                <input type="date" name="date_delivrance" id="date_delivrance" class="form-control"
                                       value="{{ old('date_delivrance', now()->toDateString()) }}" max="{{ now()->toDateString() }}" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Ayant effectué la délivrance</label>
                                <input type="text" class="form-control" value="{{ $agent }}" readonly>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-semibold">Mode de livraison <x-required /></label>
                            <div class="d-flex flex-column gap-2">
                                @foreach (\App\Enums\ModeLivraison::cases() as $option)
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" name="mode_livraison"
                                               id="mode-{{ $option->value }}" value="{{ $option->value }}"
                                               @checked($mode === $option->value)>
                                        <label class="form-check-label" for="mode-{{ $option->value }}">{{ $option->label() }}</label>
                                    </div>
                                @endforeach
                            </div>
                        </div>

                        <div id="bloc-mandataire" class="border rounded p-3 mb-3 bg-light" @style(['display: none' => $mode !== 'mandataire'])>
                            <div class="row g-3">
                                <div class="col-md-4">
                                    <label for="mandataire_nom" class="form-label fw-semibold">Nom <x-required /></label>
                                    <input type="text" name="mandataire_nom" id="mandataire_nom" class="form-control" value="{{ old('mandataire_nom') }}">
                                </div>
                                <div class="col-md-4">
                                    <label for="mandataire_prenom" class="form-label fw-semibold">Prénom <x-required /></label>
                                    <input type="text" name="mandataire_prenom" id="mandataire_prenom" class="form-control" value="{{ old('mandataire_prenom') }}">
                                </div>
                                <div class="col-md-4">
                                    <label for="mandataire_cin" class="form-label fw-semibold">CIN <x-required /></label>
                                    <input type="text" name="mandataire_cin" id="mandataire_cin" class="form-control" value="{{ old('mandataire_cin') }}">
                                </div>
                                <div class="col-md-6">
                                    <label for="date_procuration" class="form-label fw-semibold">Date de procuration <x-required /></label>
                                    <input type="date" name="date_procuration" id="date_procuration" class="form-control"
                                           value="{{ old('date_procuration') }}" max="{{ now()->toDateString() }}">
                                </div>
                                <div class="col-md-6">
                                    <label for="mandataire_piece" class="form-label fw-semibold">Réf. document (optionnel)</label>
                                    <input type="text" name="mandataire_piece" id="mandataire_piece" class="form-control" value="{{ old('mandataire_piece') }}">
                                </div>
                            </div>
                        </div>

                        <div id="bloc-postal" class="border rounded p-3 mb-3 bg-light" @style(['display: none' => $mode !== 'envoi_postal'])>
                            <div class="row g-3">
                                <div class="col-md-4">
                                    <label for="postal_cin" class="form-label fw-semibold">CIN <x-required /></label>
                                    <input type="text" name="postal_cin" id="postal_cin" class="form-control" value="{{ old('postal_cin') }}">
                                </div>
                                <div class="col-md-4">
                                    <label for="postal_nom" class="form-label fw-semibold">Nom postal <x-required /></label>
                                    <input type="text" name="postal_nom" id="postal_nom" class="form-control" value="{{ old('postal_nom') }}">
                                </div>
                                <div class="col-md-4">
                                    <label for="reference_envoi" class="form-label fw-semibold">Référence d'envoi <x-required /></label>
                                    <input type="text" name="reference_envoi" id="reference_envoi" class="form-control" value="{{ old('reference_envoi') }}">
                                </div>
                            </div>
                        </div>

                        <div class="alert alert-light border small">
                            Un <strong>numéro de départ</strong> sera attribué automatiquement
                            (AAAA–NNN–code unité de retrait), unique par année.
                        </div>

                        <div class="d-flex justify-content-end gap-2">
                            <a href="{{ route('courriers.delivrance.index') }}" class="btn btn-outline-secondary">Retour</a>
                            <button type="submit" class="btn btn-success">
                                <i class="bi bi-check2-circle me-1"></i>Confirmer la délivrance
                            </button>
                        </div>
                    </form>
                @else
                    <p class="text-muted">
                        Aucun document n'a été généré lors du traitement. Vous pouvez clôturer le dossier.
                    </p>
                    <form method="POST" action="{{ route('courriers.cloturer-sans-document', $courrier) }}"
                          onsubmit="return confirm('Clôturer ce dossier sans délivrance ?');">
                        @csrf
                        <div class="d-flex justify-content-end gap-2">
                            <a href="{{ route('courriers.delivrance.index') }}" class="btn btn-outline-secondary">Retour</a>
                            <button type="submit" class="btn btn-dark">
                                <i class="bi bi-lock me-1"></i>Clôturer le dossier
                            </button>
                        </div>
                    </form>
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
    const form = document.getElementById('form-delivrance');
    if (!form) return;
    const blocMandataire = document.getElementById('bloc-mandataire');
    const blocPostal = document.getElementById('bloc-postal');

    function afficher() {
        const mode = form.querySelector('input[name="mode_livraison"]:checked')?.value;
        if (blocMandataire) blocMandataire.style.display = mode === 'mandataire' ? '' : 'none';
        if (blocPostal) blocPostal.style.display = mode === 'envoi_postal' ? '' : 'none';
    }

    form.querySelectorAll('input[name="mode_livraison"]').forEach((el) => el.addEventListener('change', afficher));
    afficher();
})();
</script>
@endpush
