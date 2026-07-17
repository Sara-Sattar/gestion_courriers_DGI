@extends('layouts.app')

@section('title', 'Enregistrer un courrier')

@section('breadcrumb')
    <li class="breadcrumb-item active">Enregistrer un courrier</li>
@endsection

@section('content')
<form method="POST" action="{{ route('courriers.store') }}" enctype="multipart/form-data" id="courrier-form">
    @csrf

    <div class="card shadow-sm border-0 mb-4">
        <div class="card-header bg-white">
            <h5 class="mb-1"><i class="bi bi-search me-2 text-primary"></i>Informations du contribuable</h5>
            <p class="text-muted mb-0 small">Recherchez un contribuable existant.</p>
        </div>
        <div class="card-body">
            <input type="hidden" name="contribuable_id" id="contribuable_id" value="{{ old('contribuable_id') }}">

            <div class="row g-3 mb-4">
                <div class="col-md-12">
                    <label for="contribuable-search" class="form-label">Recherche</label>
                    <input
                        type="text"
                        id="contribuable-search"
                        class="form-control @error('contribuable_id') is-invalid @enderror"
                        placeholder="Rechercher par IFU, CIN, ICE ou nom / raison sociale"
                        autocomplete="off"
                    >
                    <div class="form-text">Saisissez au moins 2 caractères pour lancer la recherche.</div>
                    @error('contribuable_id')
                        <div class="invalid-feedback d-block">{{ $message }}</div>
                    @enderror
                    <div class="list-group position-relative mt-2 d-none" id="contribuable-results"></div>
                </div>
            </div>

            <div class="row g-3">
                <div class="col-md-6">
                    <label for="nom_raison_sociale" class="form-label">Nom / Raison sociale</label>
                    <input type="text" class="form-control bg-light" id="nom_raison_sociale" readonly value="{{ old('nom_raison_sociale') }}">
                </div>
                <div class="col-md-3">
                    <label for="ifu" class="form-label">IFU</label>
                    <input type="text" class="form-control bg-light" id="ifu" readonly value="{{ old('ifu') }}">
                </div>
                <div class="col-md-3">
                    <label for="cin" class="form-label">CIN</label>
                    <input type="text" class="form-control bg-light" id="cin" readonly value="{{ old('cin') }}">
                </div>
                <div class="col-md-3">
                    <label for="ice" class="form-label">ICE</label>
                    <input type="text" class="form-control bg-light" id="ice" readonly value="{{ old('ice') }}">
                </div>
                <div class="col-md-3">
                    <label for="numero_tp" class="form-label">TP</label>
                    <input type="text" class="form-control bg-light" id="numero_tp" readonly value="{{ old('numero_tp') }}">
                </div>
                <div class="col-md-3">
                    <label for="numero_th" class="form-label">TH</label>
                    <input type="text" class="form-control bg-light" id="numero_th" readonly value="{{ old('numero_th') }}">
                </div>
                <div class="col-md-3">
                    <label for="ville" class="form-label">Ville</label>
                    <input type="text" class="form-control bg-light" id="ville" readonly value="{{ old('ville') }}">
                </div>
                <div class="col-md-8">
                    <label for="adresse" class="form-label">Adresse</label>
                    <input type="text" class="form-control bg-light" id="adresse" readonly value="{{ old('adresse') }}">
                </div>
                <div class="col-md-2">
                    <label for="telephone" class="form-label">Téléphone</label>
                    <input type="text" class="form-control bg-light" id="telephone" readonly value="{{ old('telephone') }}">
                </div>
                <div class="col-md-2">
                    <label for="email" class="form-label">Email</label>
                    <input type="email" class="form-control bg-light" id="email" readonly value="{{ old('email') }}">
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
                    <label for="categorie_courrier_id" class="form-label">Catégorie</label>
                    <select class="form-select @error('categorie_courrier_id') is-invalid @enderror" id="categorie_courrier_id" name="categorie_courrier_id" data-old-type="{{ old('type_courrier_id') }}">
                        <option value="">Sélectionner</option>
                        @foreach ($categories as $categorie)
                            <option value="{{ $categorie->id }}" @selected((string) old('categorie_courrier_id') === (string) $categorie->id)>{{ $categorie->nom }}</option>
                        @endforeach
                    </select>
                    @error('categorie_courrier_id')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <div class="col-md-4">
                    <label for="type_courrier_id" class="form-label">Type de courrier</label>
                    <select class="form-select @error('type_courrier_id') is-invalid @enderror" id="type_courrier_id" name="type_courrier_id">
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
                <div class="col-md-4">
                    <label for="type_unite" class="form-label">Destination (type d'unité)</label>
                    <select class="form-select @error('type_unite') is-invalid @enderror" id="type_unite" name="type_unite" data-old-unite="{{ old('unite_destinataire_id') }}">
                        <option value="">Sélectionner</option>
                    </select>
                    @error('type_unite')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <div class="col-md-8">
                    <label for="unite_destinataire_id" class="form-label">Unité destinataire</label>
                    <select class="form-select @error('unite_destinataire_id') is-invalid @enderror" id="unite_destinataire_id" name="unite_destinataire_id">
                        <option value="">{{ old('type_unite') ? 'Chargement...' : 'Choisir un type d\'unité d\'abord' }}</option>
                    </select>
                    @error('unite_destinataire_id')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <div class="col-md-6">
                    <label for="impots" class="form-label">Impôts concernés</label>
                    <select class="form-select @error('impots') is-invalid @enderror @error('impots.*') is-invalid @enderror" id="impots" name="impots[]" multiple size="4">
                        @foreach (['IR', 'IS', 'TVA', 'TP'] as $impot)
                            <option value="{{ $impot }}" @selected(collect(old('impots', []))->contains($impot))>{{ $impot }}</option>
                        @endforeach
                    </select>
                    <div class="form-text">Maintenez `Ctrl` pour une sélection multiple.</div>
                    @error('impots')
                        <div class="invalid-feedback d-block">{{ $message }}</div>
                    @enderror
                    @error('impots.*')
                        <div class="invalid-feedback d-block">{{ $message }}</div>
                    @enderror
                </div>
                <div class="col-md-6">
                    <label for="annees" class="form-label">Années concernées</label>
                    <select class="form-select @error('annees') is-invalid @enderror @error('annees.*') is-invalid @enderror" id="annees" name="annees[]" multiple size="6">
                        @foreach ($anneesDisponibles as $annee)
                            <option value="{{ $annee }}" @selected(collect(old('annees', []))->contains((string) $annee) || collect(old('annees', []))->contains($annee))>{{ $annee }}</option>
                        @endforeach
                    </select>
                    <div class="form-text">Maintenez `Ctrl` pour une sélection multiple.</div>
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
            <h5 class="mb-1"><i class="bi bi-paperclip me-2 text-primary"></i>Pièces jointes</h5>
            <p class="text-muted mb-0 small">Formats autorisés : PDF, JPG, JPEG, PNG. Taille maximale : 5 Mo par fichier.</p>
        </div>
        <div class="card-body">
            <label for="pieces_jointes" class="form-label">Fichiers</label>
            <input type="file" class="form-control @error('pieces_jointes') is-invalid @enderror @error('pieces_jointes.*') is-invalid @enderror" id="pieces_jointes" name="pieces_jointes[]" multiple accept=".pdf,.jpg,.jpeg,.png">
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
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', () => {
    const searchInput = document.getElementById('contribuable-search');
    const resultsBox = document.getElementById('contribuable-results');
    const contribuableIdInput = document.getElementById('contribuable_id');
    const categorieSelect = document.getElementById('categorie_courrier_id');
    const typeCourrierSelect = document.getElementById('type_courrier_id');
    const typeUniteSelect = document.getElementById('type_unite');
    const uniteSelect = document.getElementById('unite_destinataire_id');

    const contribuableFields = [
        'nom_raison_sociale',
        'ifu',
        'cin',
        'ice',
        'numero_tp',
        'numero_th',
        'adresse',
        'ville',
        'telephone',
        'email',
    ].map((id) => document.getElementById(id));

    let searchTimer = null;

    const escapeHtml = (value) => {
        const div = document.createElement('div');
        div.innerText = value ?? '';
        return div.innerHTML;
    };

    const fillContribuable = (contribuable) => {
        contribuableIdInput.value = contribuable.id ?? '';
        document.getElementById('nom_raison_sociale').value = contribuable.nom_raison_sociale ?? '';
        document.getElementById('ifu').value = contribuable.ifu ?? '';
        document.getElementById('cin').value = contribuable.cin ?? '';
        document.getElementById('ice').value = contribuable.ice ?? '';
        document.getElementById('numero_tp').value = contribuable.numero_tp ?? '';
        document.getElementById('numero_th').value = contribuable.numero_th ?? '';
        document.getElementById('adresse').value = contribuable.adresse ?? '';
        document.getElementById('ville').value = contribuable.ville ?? '';
        document.getElementById('telephone').value = contribuable.telephone ?? '';
        document.getElementById('email').value = contribuable.email ?? '';
        searchInput.value = contribuable.nom_raison_sociale ?? '';
        resultsBox.classList.add('d-none');
        resultsBox.innerHTML = '';
    };

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
            try {
                const contribuables = await fetchJson(`/contribuables/recherche?q=${encodeURIComponent(query)}`);

                if (!contribuables.length) {
                    resultsBox.innerHTML = '<div class="list-group-item text-muted small">Aucun contribuable trouvé.</div>';
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
                `).join('');

                resultsBox.classList.remove('d-none');
            } catch (error) {
                resultsBox.innerHTML = '<div class="list-group-item text-danger small">Erreur lors de la recherche.</div>';
                resultsBox.classList.remove('d-none');
            }
        }, 300);
    });

    resultsBox.addEventListener('click', (event) => {
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

    categorieSelect.addEventListener('change', () => {
        loadTypesCourrier(categorieSelect.value);
    });

    typeUniteSelect.addEventListener('change', () => {
        loadUnites(typeUniteSelect.value);
    });

    loadTypesCourrier(categorieSelect.value, categorieSelect.dataset.oldType);
    loadTypesUnites(@json(old('type_unite')));
    loadUnites(@json(old('type_unite')), typeUniteSelect.dataset.oldUnite);
});
</script>
@endpush
