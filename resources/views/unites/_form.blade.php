@php
    $uniteCourante = $unite ?? null;
    $estModification = $uniteCourante !== null;

    $typeSelectionne = old('type_unite', $uniteCourante->type_unite ?? '');
    $parentSelectionne = old('parent_id', $uniteCourante->parent_id ?? '');
@endphp

<x-erreurs-formulaire />

<div class="formulaire-section">
    <div class="formulaire-section-titre">Identification</div>

    <div class="row g-3">
        <div class="col-md-4">
            <label for="type_unite" class="form-label">Type d'unité <x-required /></label>
            <select
                class="form-select @error('type_unite') is-invalid @enderror"
                id="type_unite"
                name="type_unite"
                required
            >
                <option value="">Sélectionner un type</option>
                @foreach ($typesUnite as $typeUnite)
                    <option value="{{ $typeUnite }}" @selected((string) $typeSelectionne === (string) $typeUnite)>
                        {{ $typeUnite }}
                    </option>
                @endforeach
            </select>
            @error('type_unite')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
            <div class="form-text">Détermine le code attribué et les rattachements possibles.</div>
        </div>

        <div class="col-md-4">
            <label for="code" class="form-label">Code de l'unité</label>
            <input
                type="text"
                class="form-control-plaintext border rounded px-2 bg-body-secondary @error('code') is-invalid @enderror"
                id="code"
                name="code"
                value="{{ old('code', $uniteCourante->code ?? '') }}"
                placeholder="Attribué automatiquement"
                data-genere="{{ $estModification ? '0' : '1' }}"
                data-url="{{ url('/unites/prochain-code') }}"
                readonly
            >
            @error('code')
                <div class="invalid-feedback d-block">{{ $message }}</div>
            @enderror
            <div class="form-text">
                @if ($estModification)
                    Non modifiable : ce code figure dans les numéros de courrier déjà délivrés.
                @else
                    Attribué automatiquement à la suite des unités du même type.
                @endif
            </div>
        </div>

        <div class="col-md-4">
            <label for="nom" class="form-label">Nom de l'unité <x-required /></label>
            <input
                type="text"
                class="form-control @error('nom') is-invalid @enderror"
                id="nom"
                name="nom"
                value="{{ old('nom', $uniteCourante->nom ?? '') }}"
                placeholder="Brigade de vérification"
                required
            >
            @error('nom')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <div class="col-12">
            <label for="description" class="form-label">Description</label>
            <textarea
                class="form-control @error('description') is-invalid @enderror"
                id="description"
                name="description"
                rows="2"
                placeholder="Missions ou périmètre de l'unité (facultatif)"
            >{{ old('description', $uniteCourante->description ?? '') }}</textarea>
            @error('description')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>
    </div>
</div>

<div class="formulaire-section">
    <div class="formulaire-section-titre">Position dans l'organisation</div>

    <div class="row g-3">
        <div class="col-md-8">
            <label for="parent_id" class="form-label">Unité parente</label>
            <select
                class="form-select @error('parent_id') is-invalid @enderror"
                id="parent_id"
                name="parent_id"
                data-url="{{ url('/unites/parents') }}"
                data-selection="{{ $parentSelectionne }}"
                data-exclure="{{ $uniteCourante->id ?? '' }}"
                @disabled($typeSelectionne === '')
            >
                @if ($typeSelectionne === '')
                    <option value="">Sélectionnez d'abord un type</option>
                @else
                    <option value="">Aucune (unité racine)</option>
                    @foreach ($parents as $parent)
                        <option value="{{ $parent->id }}" @selected((string) $parentSelectionne === (string) $parent->id)>
                            {{ $parent->nom }} ({{ $parent->code }})
                        </option>
                    @endforeach
                @endif
            </select>
            @error('parent_id')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
            <div class="form-text">Seules les unités de niveau supérieur ou égal sont proposées.</div>
        </div>

        <div class="col-12">
            <div class="form-check form-switch">
                <input class="form-check-input" type="checkbox" id="actif" name="actif" value="1" @checked(old('actif', $uniteCourante->actif ?? true))>
                <label class="form-check-label" for="actif">Unité active</label>
            </div>
            <div class="form-text">
                Une unité inactive n'est plus proposée comme destinataire, mais reste visible sur les courriers existants.
            </div>
        </div>
    </div>
</div>

@push('styles')
<link rel="stylesheet" href="{{ asset('css/formulaires.css') }}">
@endpush

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', () => {
    const champType = document.getElementById('type_unite');
    const champCode = document.getElementById('code');
    const champParent = document.getElementById('parent_id');

    const codeGenere = champCode.dataset.genere === '1';
    const uniteExclue = champParent.dataset.exclure;
    let parentAPreselectionner = champParent.dataset.selection || '';

    const chargerCode = (type) => {
        if (!codeGenere) {
            return;
        }

        if (!type) {
            champCode.value = '';

            return;
        }

        fetch(`${champCode.dataset.url}/${encodeURIComponent(type)}`, { headers: { Accept: 'application/json' } })
            .then((reponse) => reponse.json())
            .then((donnees) => {
                champCode.value = donnees.code ?? '';
            })
            .catch(() => {
                champCode.value = '';
            });
    };

    const chargerParents = (type) => {
        champParent.innerHTML = '';

        if (!type) {
            champParent.disabled = true;
            champParent.appendChild(new Option("Sélectionnez d'abord un type", ''));

            return;
        }

        champParent.disabled = false;
        champParent.appendChild(new Option('Aucune (unité racine)', ''));

        const parametres = uniteExclue ? `?exclure=${encodeURIComponent(uniteExclue)}` : '';

        fetch(`${champParent.dataset.url}/${encodeURIComponent(type)}${parametres}`, { headers: { Accept: 'application/json' } })
            .then((reponse) => reponse.json())
            .then((parents) => {
                parents.forEach((parent) => {
                    const option = new Option(`${parent.nom} (${parent.code})`, parent.id);
                    option.selected = String(parentAPreselectionner) === String(parent.id);
                    champParent.appendChild(option);
                });
            })
            .catch(() => {
                // La liste reste réduite à « Aucune » : l'unité sera créée à la racine.
            });
    };

    champType.addEventListener('change', () => {
        // Le rattachement précédent n'a plus de sens si le niveau change.
        parentAPreselectionner = '';
        chargerCode(champType.value);
        chargerParents(champType.value);
    });

    // Au retour d'une erreur de validation, le code n'a pas encore été calculé.
    if (codeGenere && champType.value && champCode.value === '') {
        chargerCode(champType.value);
    }
});
</script>
@endpush
