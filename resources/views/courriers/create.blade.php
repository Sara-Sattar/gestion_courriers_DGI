@extends('layouts.app')

@section('title', 'Enregistrer un courrier')

@push('styles')
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/tom-select@2.6.2/dist/css/tom-select.bootstrap5.min.css">
    <style>
        :root {
            --couleur-placeholder: #6c757d;
        }

        #courrier-form .form-control::placeholder {
            color: var(--couleur-placeholder);
            opacity: 1;
        }

        #courrier-form select.form-select:required:invalid {
            color: var(--couleur-placeholder);
        }

        #courrier-form select.form-select option {
            color: #212529;
        }

        #courrier-form select.form-select option[value=""] {
            color: var(--couleur-placeholder);
        }

        #courrier-form .ts-control input::placeholder {
            color: var(--couleur-placeholder) !important;
            opacity: 1;
        }

        #courrier-form .ts-wrapper .items-placeholder {
            color: var(--couleur-placeholder) !important;
            opacity: 1;
        }

        .ts-wrapper.multi .ts-control > .item {
            background-color: var(--bs-primary-bg-subtle);
            color: var(--bs-primary-text-emphasis);
            border: 1px solid var(--bs-primary-border-subtle);
            border-radius: var(--bs-border-radius);
        }

        .ts-wrapper.multi .ts-control > .item .remove {
            border-left-color: var(--bs-primary-border-subtle);
        }

        .depot-fichiers {
            border: 2px dashed #dee2e6;
            border-radius: 0.5rem;
            background-color: #f8f9fa;
            padding: 2.75rem 1.5rem;
            text-align: center;
            cursor: pointer;
            transition: border-color 0.15s ease, background-color 0.15s ease;
        }

        .depot-fichiers:hover,
        .depot-fichiers:focus-visible,
        .depot-fichiers.depot-fichiers--survol {
            border-color: var(--bs-primary);
            background-color: var(--bs-primary-bg-subtle);
            outline: none;
        }

        .depot-fichiers.is-invalid {
            border-color: var(--bs-danger);
        }

        .depot-fichiers-icone {
            font-size: 2.75rem;
            color: var(--bs-primary);
            line-height: 1;
            margin-bottom: 0.75rem;
        }

        .depot-fichiers-titre {
            font-weight: 600;
            color: #212529;
        }

        .depot-fichiers-aide {
            font-size: 0.875rem;
            color: var(--couleur-placeholder);
        }
    </style>
@endpush

@section('content')
@php
    $ongletActif = (session('import_resultat') || $errors->has('fichier')) ? 'import' : 'manuel';
@endphp

<ul class="nav nav-tabs mb-4" id="onglets-enregistrement" role="tablist">
    <li class="nav-item" role="presentation">
        <button
            class="nav-link {{ $ongletActif === 'manuel' ? 'active' : '' }}"
            id="onglet-manuel"
            data-bs-toggle="tab"
            data-bs-target="#panneau-manuel"
            type="button"
            role="tab"
            aria-controls="panneau-manuel"
            aria-selected="{{ $ongletActif === 'manuel' ? 'true' : 'false' }}"
        >
            <i class="bi bi-pencil-square me-1"></i>Saisie manuelle
        </button>
    </li>
    <li class="nav-item" role="presentation">
        <button
            class="nav-link {{ $ongletActif === 'import' ? 'active' : '' }}"
            id="onglet-import"
            data-bs-toggle="tab"
            data-bs-target="#panneau-import"
            type="button"
            role="tab"
            aria-controls="panneau-import"
            aria-selected="{{ $ongletActif === 'import' ? 'true' : 'false' }}"
        >
            <i class="bi bi-file-earmark-spreadsheet me-1"></i>Import Excel
        </button>
    </li>
</ul>

<div class="tab-content">
<div class="tab-pane fade {{ $ongletActif === 'manuel' ? 'show active' : '' }}" id="panneau-manuel" role="tabpanel" aria-labelledby="onglet-manuel">
<form method="POST" action="{{ route('courriers.store') }}" enctype="multipart/form-data" id="courrier-form">
    @csrf

    @php
        $modeContribuable = old('mode_contribuable', 'recherche');
        $etatContribuable = match (true) {
            filled(old('contribuable_id')) => 'existant',
            $modeContribuable === 'manuel' => 'nouveau',
            default => 'vide',
        };
    @endphp

    <div class="card shadow-sm border-0 mb-4">
        <div class="card-header bg-white">
            <h5 class="mb-1"><i class="bi bi-person-vcard me-2 text-primary"></i>Informations du contribuable</h5>
            <p class="text-muted mb-0 small">Recherchez le contribuable par IFU, CIN, ICE ou nom. Si aucune fiche n'existe, vous pourrez la créer.</p>
        </div>
        <div class="card-body">
            <input type="hidden" name="contribuable_id" id="contribuable_id" value="{{ old('contribuable_id') }}">
            <input type="hidden" name="mode_contribuable" id="mode_contribuable" value="{{ $modeContribuable }}">
            @error('mode_contribuable')
                <div class="invalid-feedback d-block mb-3">{{ $message }}</div>
            @enderror

            <div class="{{ $etatContribuable === 'vide' ? '' : 'd-none' }}" id="bloc-recherche">
                <label for="contribuable-search" class="form-label">Rechercher le contribuable</label>
                <div class="position-relative">
                    <div class="input-group input-group-lg">
                        <span class="input-group-text bg-white border-end-0"><i class="bi bi-search text-muted"></i></span>
                        <input
                            type="text"
                            id="contribuable-search"
                            class="form-control border-start-0 ps-0 @error('contribuable_id') is-invalid @enderror"
                            placeholder="IFU, CIN, ICE ou nom / raison sociale"
                            autocomplete="off"
                        >
                    </div>
                    <div class="list-group shadow position-absolute w-100 d-none" id="contribuable-results" style="z-index: 1050;"></div>
                </div>
                <div class="form-text">Saisissez au moins 2 caractères. Si aucune fiche ne correspond, vous pourrez en créer une.</div>
                @error('contribuable_id')
                    <div class="invalid-feedback d-block">{{ $message }}</div>
                @enderror
            </div>

            <div class="alert align-items-center justify-content-between gap-3 mb-0 d-none" id="bandeau-contribuable">
                <div class="d-flex align-items-center gap-3">
                    <i class="fs-4" id="bandeau-icone"></i>
                    <div>
                        <div class="fw-semibold" id="bandeau-titre"></div>
                        <div class="small" id="bandeau-detail"></div>
                    </div>
                </div>
                <button type="button" class="btn btn-sm btn-outline-secondary flex-shrink-0" id="btn-changer-contribuable"></button>
            </div>

            <div class="row g-3 mt-1 {{ $etatContribuable === 'vide' ? 'd-none' : '' }}" id="bloc-champs">
                <div class="col-md-6">
                    <label for="nom_raison_sociale" class="form-label">
                        Nom / Raison sociale
                        <span class="marqueur-obligatoire-manuel {{ $etatContribuable === 'nouveau' ? '' : 'd-none' }}"><x-required /></span>
                    </label>
                    <input type="text" class="form-control contribuable-champ @error('nom_raison_sociale') is-invalid @enderror" id="nom_raison_sociale" name="nom_raison_sociale" value="{{ old('nom_raison_sociale') }}">
                    @error('nom_raison_sociale')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <div class="col-md-6">
                    <label for="type_contribuable" class="form-label">
                        Type de contribuable
                        <span class="marqueur-obligatoire-manuel {{ $etatContribuable === 'nouveau' ? '' : 'd-none' }}"><x-required /></span>
                    </label>
                    <select class="form-select contribuable-champ @error('type_contribuable') is-invalid @enderror" id="type_contribuable" name="type_contribuable">
                        <option value="">— Choisir —</option>
                        @foreach (\App\Enums\TypeContribuable::cases() as $typeContribuable)
                            <option value="{{ $typeContribuable->value }}" @selected(old('type_contribuable') === $typeContribuable->value)>
                                {{ $typeContribuable->label() }}
                            </option>
                        @endforeach
                    </select>
                    @error('type_contribuable')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <div class="col-12 {{ $etatContribuable === 'nouveau' ? '' : 'd-none' }}" id="aide-identifiants-manuel">
                    <p class="form-text mb-0">Renseignez au moins un identifiant fiscal ci-dessous <span class="text-danger">*</span></p>
                </div>
                <div class="col-md-3">
                    <label for="ifu" class="form-label">
                        IFU
                        <span class="marqueur-obligatoire-manuel {{ $etatContribuable === 'nouveau' ? '' : 'd-none' }}"><x-required /></span>
                    </label>
                    <input type="text" class="form-control contribuable-champ @error('ifu') is-invalid @enderror" id="ifu" name="ifu" value="{{ old('ifu') }}">
                    @error('ifu')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <div class="col-md-3">
                    <label for="cin" class="form-label">
                        CIN
                        <span class="marqueur-obligatoire-manuel {{ $etatContribuable === 'nouveau' ? '' : 'd-none' }}"><x-required /></span>
                    </label>
                    <input type="text" class="form-control contribuable-champ" id="cin" name="cin" value="{{ old('cin') }}">
                </div>
                <div class="col-md-3">
                    <label for="ice" class="form-label">
                        ICE
                        <span class="marqueur-obligatoire-manuel {{ $etatContribuable === 'nouveau' ? '' : 'd-none' }}"><x-required /></span>
                    </label>
                    <input type="text" class="form-control contribuable-champ" id="ice" name="ice" value="{{ old('ice') }}">
                </div>
                <div class="col-md-3">
                    <label for="numero_tp" class="form-label">
                        N° TP
                        <span class="marqueur-obligatoire-manuel {{ $etatContribuable === 'nouveau' ? '' : 'd-none' }}"><x-required /></span>
                    </label>
                    <input type="text" class="form-control contribuable-champ" id="numero_tp" name="numero_tp" value="{{ old('numero_tp') }}">
                </div>
                <div class="col-md-3">
                    <label for="numero_th" class="form-label">
                        N° TH
                        <span class="marqueur-obligatoire-manuel {{ $etatContribuable === 'nouveau' ? '' : 'd-none' }}"><x-required /></span>
                    </label>
                    <input type="text" class="form-control contribuable-champ" id="numero_th" name="numero_th" value="{{ old('numero_th') }}">
                </div>
                <div class="col-md-3">
                    <label for="ville" class="form-label">Ville</label>
                    <input type="text" class="form-control contribuable-champ" id="ville" name="ville" value="{{ old('ville') }}">
                </div>
                <div class="col-md-8">
                    <label for="adresse" class="form-label">Adresse</label>
                    <input type="text" class="form-control contribuable-champ" id="adresse" name="adresse" value="{{ old('adresse') }}">
                </div>
                <div class="col-md-2">
                    <label for="telephone" class="form-label">Téléphone</label>
                    <input type="text" class="form-control contribuable-champ" id="telephone" name="telephone" value="{{ old('telephone') }}">
                </div>
                <div class="col-md-2">
                    <label for="email" class="form-label">Email</label>
                    <input type="email" class="form-control contribuable-champ @error('email') is-invalid @enderror" id="email" name="email" value="{{ old('email') }}">
                    @error('email')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>
        </div>
    </div>

    <div class="card shadow-sm border-0 mb-4">
        <div class="card-header bg-white">
            <h5 class="mb-1"><i class="bi bi-envelope-paper me-2 text-primary"></i>Informations du courrier</h5>
            <p class="text-muted mb-0 small">Sélectionnez la catégorie, le type, la destination et les éléments fiscaux concernés.</p>
        </div>
        <div class="card-body">
            <div class="row g-3">
                <div class="col-md-4">
                    <label for="categorie_courrier_id" class="form-label">Catégorie <x-required /></label>
                    <select class="form-select @error('categorie_courrier_id') is-invalid @enderror" id="categorie_courrier_id" name="categorie_courrier_id" data-old-type="{{ old('type_courrier_id') }}" required>
                        <option value="">Sélectionner</option>
                        @foreach ($categories as $categorie)
                            <option value="{{ $categorie->id }}" data-necessite-reponse="{{ $categorie->necessite_reponse ? '1' : '0' }}" @selected((string) old('categorie_courrier_id') === (string) $categorie->id)>{{ $categorie->nom }}</option>
                        @endforeach
                    </select>
                    @error('categorie_courrier_id')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <div class="col-md-4">
                    <label for="type_courrier_id" class="form-label">Type de courrier <x-required /></label>
                    <select class="form-select @error('type_courrier_id') is-invalid @enderror" id="type_courrier_id" name="type_courrier_id" required>
                        <option value="">{{ old('categorie_courrier_id') ? 'Chargement...' : 'Choisir une catégorie d\'abord' }}</option>
                    </select>
                    @error('type_courrier_id')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <div class="col-md-4">
                    <label for="objet" class="form-label">Objet</label>
                    <input type="text" class="form-control @error('objet') is-invalid @enderror" id="objet" name="objet" value="{{ old('objet') }}">
                    @error('objet')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <div class="col-md-6">
                    <label class="form-label">Nécessitant une réponse</label>
                    {{-- Lecture seule : la valeur est imposée par la catégorie (non envoyée au serveur). --}}
                    <div class="d-flex gap-3 mt-1">
                        <div class="form-check">
                            <input class="form-check-input" type="radio" id="reponse-oui" value="1" disabled>
                            <label class="form-check-label" for="reponse-oui">Oui</label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="radio" id="reponse-non" value="0" disabled>
                            <label class="form-check-label" for="reponse-non">Non</label>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <label for="type_unite" class="form-label">Destination (type d'unité) <x-required /></label>
                    <select class="form-select @error('type_unite') is-invalid @enderror" id="type_unite" name="type_unite" data-old-unite="{{ old('unite_destinataire_id') }}" required>
                        <option value="">Sélectionner</option>
                    </select>
                    @error('type_unite')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <div class="col-md-8">
                    <label for="unite_destinataire_id" class="form-label">Unité destinataire <x-required /></label>
                    <select class="form-select @error('unite_destinataire_id') is-invalid @enderror" id="unite_destinataire_id" name="unite_destinataire_id" required>
                        <option value="">{{ old('type_unite') ? 'Chargement...' : 'Choisir un type d\'unité d\'abord' }}</option>
                    </select>
                    @error('unite_destinataire_id')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <div class="col-md-6">
                    <label for="impots" class="form-label">Impôts concernés <x-required /></label>
                    <select class="form-select @error('impots') is-invalid @enderror @error('impots.*') is-invalid @enderror" id="impots" name="impots[]" multiple data-placeholder="Rechercher ou sélectionner un impôt">
                        @foreach (\App\Enums\TypeImpot::cases() as $impot)
                            <option value="{{ $impot->value }}" @selected(collect(old('impots', []))->contains($impot->value))>{{ $impot->value }} — {{ $impot->libelle() }}</option>
                        @endforeach
                    </select>
                    @error('impots')
                        <div class="invalid-feedback d-block">{{ $message }}</div>
                    @enderror
                    @error('impots.*')
                        <div class="invalid-feedback d-block">{{ $message }}</div>
                    @enderror
                </div>
                <div class="col-md-6">
                    <label for="annees" class="form-label">Années concernées <x-required /></label>
                    <select class="form-select @error('annees') is-invalid @enderror @error('annees.*') is-invalid @enderror" id="annees" name="annees[]" multiple data-placeholder="Rechercher ou sélectionner une année">
                        @foreach ($anneesDisponibles as $annee)
                            <option value="{{ $annee }}" @selected(collect(old('annees', []))->contains((string) $annee) || collect(old('annees', []))->contains($annee))>{{ $annee }}</option>
                        @endforeach
                    </select>
                    @error('annees')
                        <div class="invalid-feedback d-block">{{ $message }}</div>
                    @enderror
                    @error('annees.*')
                        <div class="invalid-feedback d-block">{{ $message }}</div>
                    @enderror
                </div>
                <div class="col-12">
                    <label for="remarque" class="form-label">Remarque</label>
                    <textarea class="form-control @error('remarque') is-invalid @enderror" id="remarque" name="remarque" rows="4">{{ old('remarque') }}</textarea>
                    @error('remarque')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>
        </div>
    </div>

    <div class="card shadow-sm border-0 mb-4">
        <div class="card-header bg-white">
            <h5 class="mb-0"><i class="bi bi-paperclip me-2 text-primary"></i>Pièces jointes</h5>
        </div>
        <div class="card-body">
            <label for="pieces_jointes" class="form-label">Documents justificatifs liés au courrier (facultatif)</label>

            <div
                class="depot-fichiers @error('pieces_jointes') is-invalid @enderror @error('pieces_jointes.*') is-invalid @enderror"
                id="depot-fichiers"
                tabindex="0"
                role="button"
                aria-label="Sélectionner ou déposer des fichiers"
            >
                <input
                    type="file"
                    class="visually-hidden @error('pieces_jointes') is-invalid @enderror @error('pieces_jointes.*') is-invalid @enderror"
                    id="pieces_jointes"
                    name="pieces_jointes[]"
                    multiple
                    accept=".pdf,.doc,.docx,.jpg,.jpeg,.png"
                >
                <i class="bi bi-cloud-arrow-up depot-fichiers-icone" aria-hidden="true"></i>
                <p class="depot-fichiers-titre mb-1">Cliquez ou déposez vos fichiers ici</p>
                <p class="depot-fichiers-aide mb-0">PDF, Word (DOC, DOCX), images (JPG, JPEG, PNG) — 10 Mo maximum par fichier</p>
            </div>

            <ul class="list-group list-group-flush mt-3 d-none" id="liste-fichiers" aria-live="polite"></ul>

            @error('pieces_jointes')
                <div class="invalid-feedback d-block">{{ $message }}</div>
            @enderror
            @error('pieces_jointes.*')
                <div class="invalid-feedback d-block">{{ $message }}</div>
            @enderror
        </div>
    </div>

    <div class="d-flex justify-content-end gap-2">
        <a href="{{ route('dashboard') }}" class="btn btn-light border">Annuler</a>
        <button type="submit" class="btn btn-primary">
            <i class="bi bi-save me-1"></i>Enregistrer et générer le reçu
        </button>
    </div>
</form>
</div>

<div class="tab-pane fade {{ $ongletActif === 'import' ? 'show active' : '' }}" id="panneau-import" role="tabpanel" aria-labelledby="onglet-import">
    @include('courriers._import-excel')
</div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/tom-select@2.6.2/dist/js/tom-select.complete.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', () => {
    const searchInput = document.getElementById('contribuable-search');
    const resultsBox = document.getElementById('contribuable-results');
    const contribuableIdInput = document.getElementById('contribuable_id');
    const categorieSelect = document.getElementById('categorie_courrier_id');
    const typeCourrierSelect = document.getElementById('type_courrier_id');
    const typeUniteSelect = document.getElementById('type_unite');
    const uniteSelect = document.getElementById('unite_destinataire_id');

    const modeInput = document.getElementById('mode_contribuable');
    const blocRecherche = document.getElementById('bloc-recherche');
    const blocChamps = document.getElementById('bloc-champs');
    const bandeau = document.getElementById('bandeau-contribuable');
    const bandeauIcone = document.getElementById('bandeau-icone');
    const bandeauTitre = document.getElementById('bandeau-titre');
    const bandeauDetail = document.getElementById('bandeau-detail');
    const btnChanger = document.getElementById('btn-changer-contribuable');
    const champsContribuable = Array.from(document.querySelectorAll('.contribuable-champ'));
    const marqueursObligatoireManuel = Array.from(document.querySelectorAll('.marqueur-obligatoire-manuel'));
    const aideIdentifiantsManuel = document.getElementById('aide-identifiants-manuel');

    let searchTimer = null;

    const champ = (id) => document.getElementById(id);

    const identifiantsLisibles = () => {
        const parts = [
            ['IFU', champ('ifu').value],
            ['CIN', champ('cin').value],
            ['ICE', champ('ice').value],
        ].filter(([, valeur]) => valeur.trim() !== '');

        return parts.length
            ? parts.map(([label, valeur]) => `${label} : ${valeur}`).join(' · ')
            : 'Aucun identifiant fiscal renseigné';
    };

    const majBandeau = (etat) => {
        if (etat === 'existant') {
            bandeau.classList.add('alert-success');
            bandeau.classList.remove('alert-primary');
            bandeauIcone.className = 'bi bi-person-check-fill fs-4';
            bandeauTitre.textContent = champ('nom_raison_sociale').value || 'Contribuable sélectionné';
            bandeauDetail.textContent = `Fiche existante — ${identifiantsLisibles()}`;
            btnChanger.innerHTML = '<i class="bi bi-arrow-repeat me-1"></i>Changer de contribuable';
            return;
        }

        bandeau.classList.add('alert-primary');
        bandeau.classList.remove('alert-success');
        bandeauIcone.className = 'bi bi-person-plus-fill fs-4';
        bandeauTitre.textContent = 'Nouvelle fiche contribuable';
        bandeauDetail.textContent = 'Renseignez le nom ainsi qu\'au moins un identifiant fiscal : IFU, CIN, ICE, N° TP ou N° TH.';
        btnChanger.innerHTML = '<i class="bi bi-search me-1"></i>Rechercher une fiche existante';
    };

    const appliquerEtat = (etat) => {
        const surFiche = etat !== 'vide';
        const existant = etat === 'existant';

        modeInput.value = existant ? 'recherche' : (etat === 'nouveau' ? 'manuel' : 'recherche');

        blocRecherche.classList.toggle('d-none', surFiche);
        blocChamps.classList.toggle('d-none', !surFiche);
        bandeau.classList.toggle('d-none', !surFiche);
        bandeau.classList.toggle('d-flex', surFiche);

        champsContribuable.forEach((champContribuable) => {
            champContribuable.readOnly = existant;
            champContribuable.classList.toggle('bg-light', existant);
        });

        const saisieManuelle = etat === 'nouveau';
        marqueursObligatoireManuel.forEach((marqueur) => {
            marqueur.classList.toggle('d-none', !saisieManuelle);
        });
        aideIdentifiantsManuel?.classList.toggle('d-none', !saisieManuelle);

        resultsBox.classList.add('d-none');
        resultsBox.innerHTML = '';

        if (surFiche) {
            majBandeau(etat);
        }
    };

    const viderChampsContribuable = () => {
        champsContribuable.forEach((champContribuable) => {
            champContribuable.value = '';
        });
        contribuableIdInput.value = '';
    };

    const escapeHtml = (value) => {
        const div = document.createElement('div');
        div.innerText = value ?? '';
        return div.innerHTML;
    };

    const fillContribuable = (contribuable) => {
        contribuableIdInput.value = contribuable.id ?? '';
        champ('nom_raison_sociale').value = contribuable.nom_raison_sociale ?? '';
        champ('ifu').value = contribuable.ifu ?? '';
        champ('cin').value = contribuable.cin ?? '';
        champ('ice').value = contribuable.ice ?? '';
        champ('numero_tp').value = contribuable.numero_tp ?? '';
        champ('numero_th').value = contribuable.numero_th ?? '';
        champ('adresse').value = contribuable.adresse ?? '';
        champ('ville').value = contribuable.ville ?? '';
        champ('telephone').value = contribuable.telephone ?? '';
        champ('email').value = contribuable.email ?? '';
        appliquerEtat('existant');
    };

    const demarrerCreation = (recherche) => {
        viderChampsContribuable();

        const valeur = (recherche ?? '').trim();

        if (valeur !== '') {
            champ(/^\d+$/.test(valeur) ? 'ifu' : 'nom_raison_sociale').value = valeur;
        }

        appliquerEtat('nouveau');
        champ('nom_raison_sociale').focus();
    };

    btnChanger.addEventListener('click', () => {
        viderChampsContribuable();
        searchInput.value = '';
        appliquerEtat('vide');
        searchInput.focus();
    });

    const populateSelect = (select, items, config) => {
        const {placeholder, valueKey = 'id', labelBuilder} = config;
        select.innerHTML = '';

        const defaultOption = document.createElement('option');
        defaultOption.value = '';
        defaultOption.textContent = placeholder;
        select.appendChild(defaultOption);

        items.forEach((item) => {
            const option = document.createElement('option');
            option.value = item[valueKey];
            option.textContent = labelBuilder(item);
            select.appendChild(option);
        });
    };

    const fetchJson = async (url) => {
        const response = await fetch(url, {
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json',
            },
        });

        if (!response.ok) {
            throw new Error('Erreur de chargement');
        }

        return response.json();
    };

    const loadTypesCourrier = async (categorieId, selectedId = '') => {
        if (!categorieId) {
            populateSelect(typeCourrierSelect, [], {
                placeholder: 'Choisir une catégorie d\'abord',
                labelBuilder: (item) => item.nom,
            });
            return;
        }

        populateSelect(typeCourrierSelect, [], {
            placeholder: 'Chargement...',
            labelBuilder: (item) => item.nom,
        });

        try {
            const data = await fetchJson(`/categories/${categorieId}/types-courrier`);
            populateSelect(typeCourrierSelect, data, {
                placeholder: 'Sélectionner',
                labelBuilder: (item) => item.nom,
            });

            if (selectedId) {
                typeCourrierSelect.value = String(selectedId);
            }
        } catch (error) {
            populateSelect(typeCourrierSelect, [], {
                placeholder: 'Erreur de chargement',
                labelBuilder: (item) => item.nom,
            });
        }
    };

    const loadTypesUnites = async (selectedType = '') => {
        try {
            const data = await fetchJson('/unites/types');
            populateSelect(typeUniteSelect, data.map((item) => ({type_unite: item})), {
                placeholder: 'Sélectionner',
                valueKey: 'type_unite',
                labelBuilder: (item) => item.type_unite,
            });

            if (selectedType) {
                typeUniteSelect.value = selectedType;
            }
        } catch (error) {
            populateSelect(typeUniteSelect, [], {
                placeholder: 'Erreur de chargement',
                labelBuilder: (item) => item.type_unite,
            });
        }
    };

    const loadUnites = async (typeUnite, selectedId = '') => {
        if (!typeUnite) {
            populateSelect(uniteSelect, [], {
                placeholder: 'Choisir un type d\'unité d\'abord',
                labelBuilder: (item) => item.nom,
            });
            return;
        }

        populateSelect(uniteSelect, [], {
            placeholder: 'Chargement...',
            labelBuilder: (item) => item.nom,
        });

        try {
            const data = await fetchJson(`/unites/type/${encodeURIComponent(typeUnite)}`);
            populateSelect(uniteSelect, data, {
                placeholder: 'Sélectionner',
                labelBuilder: (item) => `${item.nom} (${item.code})`,
            });

            if (selectedId) {
                uniteSelect.value = String(selectedId);
            }
        } catch (error) {
            populateSelect(uniteSelect, [], {
                placeholder: 'Erreur de chargement',
                labelBuilder: (item) => item.nom,
            });
        }
    };

    searchInput.addEventListener('input', () => {
        const query = searchInput.value.trim();

        clearTimeout(searchTimer);

        if (query.length < 2) {
            resultsBox.classList.add('d-none');
            resultsBox.innerHTML = '';
            return;
        }

        searchTimer = setTimeout(async () => {
            const optionCreation = (libelle) => `
                <button type="button" class="list-group-item list-group-item-action text-primary" data-creer="${encodeURIComponent(query)}">
                    <i class="bi bi-person-plus me-2"></i>${libelle}
                </button>
            `;

            try {
                const contribuables = await fetchJson(`/contribuables/recherche?q=${encodeURIComponent(query)}`);

                if (!contribuables.length) {
                    resultsBox.innerHTML = `
                        <div class="list-group-item text-muted small">Aucune fiche ne correspond à « ${escapeHtml(query)} ».</div>
                        ${optionCreation(`Créer la fiche « ${escapeHtml(query)} »`)}
                    `;
                    resultsBox.classList.remove('d-none');
                    return;
                }

                resultsBox.innerHTML = contribuables.map((contribuable) => `
                    <button type="button" class="list-group-item list-group-item-action" data-contribuable="${encodeURIComponent(JSON.stringify(contribuable))}">
                        <div class="fw-semibold">${escapeHtml(contribuable.nom_raison_sociale ?? '')}</div>
                        <div class="small text-muted">
                            IFU: ${escapeHtml(contribuable.ifu ?? '-')} |
                            CIN: ${escapeHtml(contribuable.cin ?? '-')} |
                            ICE: ${escapeHtml(contribuable.ice ?? '-')}
                        </div>
                    </button>
                `).join('') + optionCreation('Aucune de ces fiches — en créer une nouvelle');

                resultsBox.classList.remove('d-none');
            } catch (error) {
                resultsBox.innerHTML = '<div class="list-group-item text-danger small">Erreur lors de la recherche.</div>';
                resultsBox.classList.remove('d-none');
            }
        }, 300);
    });

    resultsBox.addEventListener('click', (event) => {
        const boutonCreation = event.target.closest('[data-creer]');

        if (boutonCreation) {
            demarrerCreation(decodeURIComponent(boutonCreation.dataset.creer));
            return;
        }

        const button = event.target.closest('[data-contribuable]');

        if (!button) {
            return;
        }

        fillContribuable(JSON.parse(decodeURIComponent(button.dataset.contribuable)));
    });

    document.addEventListener('click', (event) => {
        if (!resultsBox.contains(event.target) && event.target !== searchInput) {
            resultsBox.classList.add('d-none');
        }
    });

    const reponseOui = document.getElementById('reponse-oui');
    const reponseNon = document.getElementById('reponse-non');

    const afficherNecessiteReponse = () => {
        const valeur = categorieSelect.selectedOptions[0]?.dataset.necessiteReponse;
        reponseOui.checked = valeur === '1';
        reponseNon.checked = valeur === '0';
    };

    categorieSelect.addEventListener('change', () => {
        loadTypesCourrier(categorieSelect.value);
        afficherNecessiteReponse();
    });

    typeUniteSelect.addEventListener('change', () => {
        loadUnites(typeUniteSelect.value);
    });

    document.querySelectorAll('select[multiple][data-placeholder]').forEach((select) => {
        new TomSelect(select, {
            plugins: ['remove_button', 'clear_button'],
            placeholder: select.dataset.placeholder,
            hidePlaceholder: true,
            maxOptions: null,
            hideSelected: true,
            render: {
                no_results: () => '<div class="no-results">Aucun résultat</div>',
            },
        });
    });

    appliquerEtat(@json($etatContribuable));

    loadTypesCourrier(categorieSelect.value, categorieSelect.dataset.oldType);
    afficherNecessiteReponse();
    loadTypesUnites(@json(old('type_unite')));
    loadUnites(@json(old('type_unite')), typeUniteSelect.dataset.oldUnite);

    const depotFichiers = document.getElementById('depot-fichiers');
    const inputFichiers = document.getElementById('pieces_jointes');
    const listeFichiers = document.getElementById('liste-fichiers');
    const fichiersSelectionnes = [];

    const formaterTaille = (octets) => {
        if (octets < 1024) {
            return `${octets} o`;
        }

        if (octets < 1024 * 1024) {
            return `${(octets / 1024).toFixed(1)} Ko`;
        }

        return `${(octets / (1024 * 1024)).toFixed(1)} Mo`;
    };

    const synchroniserInput = () => {
        const dataTransfer = new DataTransfer();
        fichiersSelectionnes.forEach((fichier) => dataTransfer.items.add(fichier));
        inputFichiers.files = dataTransfer.files;
    };

    const afficherListeFichiers = () => {
        listeFichiers.innerHTML = '';

        if (!fichiersSelectionnes.length) {
            listeFichiers.classList.add('d-none');
            return;
        }

        fichiersSelectionnes.forEach((fichier, index) => {
            const item = document.createElement('li');
            item.className = 'list-group-item d-flex justify-content-between align-items-center gap-3 px-0';
            item.innerHTML = `
                <div class="d-flex align-items-center gap-2 text-truncate">
                    <i class="bi bi-file-earmark-text text-primary flex-shrink-0"></i>
                    <span class="text-truncate">${escapeHtml(fichier.name)}</span>
                    <span class="text-muted small flex-shrink-0">${formaterTaille(fichier.size)}</span>
                </div>
                <button type="button" class="btn btn-sm btn-outline-danger flex-shrink-0" data-index="${index}" aria-label="Retirer ${escapeHtml(fichier.name)}">
                    <i class="bi bi-x-lg"></i>
                </button>
            `;
            listeFichiers.appendChild(item);
        });

        listeFichiers.classList.remove('d-none');
    };

    const ajouterFichiers = (fichiers) => {
        Array.from(fichiers).forEach((fichier) => {
            const dejaPresent = fichiersSelectionnes.some(
                (existant) => existant.name === fichier.name && existant.size === fichier.size && existant.lastModified === fichier.lastModified
            );

            if (!dejaPresent) {
                fichiersSelectionnes.push(fichier);
            }
        });

        synchroniserInput();
        afficherListeFichiers();
    };

    depotFichiers.addEventListener('click', () => inputFichiers.click());

    depotFichiers.addEventListener('keydown', (event) => {
        if (event.key === 'Enter' || event.key === ' ') {
            event.preventDefault();
            inputFichiers.click();
        }
    });

    inputFichiers.addEventListener('change', () => {
        ajouterFichiers(inputFichiers.files);
        inputFichiers.value = '';
    });

    ['dragenter', 'dragover'].forEach((evenement) => {
        depotFichiers.addEventListener(evenement, (event) => {
            event.preventDefault();
            depotFichiers.classList.add('depot-fichiers--survol');
        });
    });

    ['dragleave', 'drop'].forEach((evenement) => {
        depotFichiers.addEventListener(evenement, (event) => {
            event.preventDefault();
            depotFichiers.classList.remove('depot-fichiers--survol');
        });
    });

    depotFichiers.addEventListener('drop', (event) => {
        if (event.dataTransfer?.files?.length) {
            ajouterFichiers(event.dataTransfer.files);
        }
    });

    listeFichiers.addEventListener('click', (event) => {
        const bouton = event.target.closest('[data-index]');

        if (!bouton) {
            return;
        }

        fichiersSelectionnes.splice(Number(bouton.dataset.index), 1);
        synchroniserInput();
        afficherListeFichiers();
    });
});
</script>
@endpush
