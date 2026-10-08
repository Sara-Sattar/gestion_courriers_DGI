@extends('layouts.app')

@section('title', 'Nouvel utilisateur')

@php
    $erreurEtape1 = $errors->hasAny([
        'name', 'email', 'password', 'password_confirmation', 'role_id', 'unite_id',
    ]);
    $etapeInitiale = $erreurEtape1 ? 1 : (old('_token') ? 2 : 1);
@endphp

@section('page-header')
    <div class="col">
        <x-lien-retour :href="route('users.index')">Utilisateurs</x-lien-retour>
        <h2 class="h3 fw-bold mb-1">Nouvel utilisateur</h2>
        <p class="text-muted mb-0 small" data-wizard-sous-titre>
            Étape {{ $etapeInitiale }} sur 2 —
            {{ $etapeInitiale === 1 ? 'renseignez l’identité et l’affectation' : 'définissez les droits d’accès' }}.
        </p>
    </div>
@endsection

@section('content')
<div class="row justify-content-center">
    <div class="col-xl-9">
        <nav class="user-wizard-steps mb-4" aria-label="Étapes de création">
            <ol class="user-wizard-list">
                <li class="user-wizard-item {{ $etapeInitiale === 1 ? 'is-active' : 'is-done' }}" data-wizard-step="1">
                    <span class="user-wizard-index">1</span>
                    <span class="user-wizard-label">
                        <strong>Informations</strong>
                        <small>Identité, mot de passe, affectation</small>
                    </span>
                </li>
                <li class="user-wizard-connector" aria-hidden="true"></li>
                <li class="user-wizard-item {{ $etapeInitiale === 2 ? 'is-active' : '' }}" data-wizard-step="2">
                    <span class="user-wizard-index">2</span>
                    <span class="user-wizard-label">
                        <strong>Droits d’accès</strong>
                        <small>Menus et permissions</small>
                    </span>
                </li>
            </ol>
        </nav>

        <div class="card shadow-sm border-0">
            <div class="card-body p-4">
                <form
                    id="form-nouvel-utilisateur"
                    action="{{ route('users.store') }}"
                    method="POST"
                    data-etape-initiale="{{ $etapeInitiale }}"
                    novalidate
                >
                    @csrf

                    @include('users._form', ['parcoursEtapes' => true])

                    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mt-4 pt-3 border-top">
                        <a href="{{ route('users.index') }}" class="btn btn-light border">Annuler</a>

                        <div class="d-flex flex-wrap gap-2 ms-auto">
                            <button
                                type="button"
                                id="btn-etape-retour"
                                class="btn btn-outline-secondary"
                                hidden
                            >
                                <i class="bi bi-arrow-left me-1"></i>Retour
                            </button>

                            <button
                                type="button"
                                id="btn-etape-suivant"
                                class="btn btn-primary"
                                hidden
                                disabled
                            >
                                Suivant
                                <i class="bi bi-arrow-right ms-1"></i>
                            </button>

                            <button
                                type="submit"
                                id="btn-creer-compte"
                                class="btn btn-primary"
                                hidden
                            >
                                <i class="bi bi-check-lg me-1"></i>Créer le compte
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<link rel="stylesheet" href="{{ asset('css/formulaires.css') }}">
<link rel="stylesheet" href="{{ asset('css/permissions.css') }}">
<link rel="stylesheet" href="{{ asset('css/users-admin.css') }}">
<style>
    .user-wizard-list {
        display: flex;
        align-items: center;
        gap: 0.75rem;
        margin: 0;
        padding: 1rem 1.25rem;
        list-style: none;
        background: #fff;
        border: 1px solid #e9ecef;
        border-radius: 0.5rem;
        box-shadow: 0 1px 2px rgba(0, 0, 0, 0.03);
    }

    .user-wizard-item {
        display: flex;
        align-items: center;
        gap: 0.75rem;
        min-width: 0;
        color: #6c757d;
    }

    .user-wizard-index {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 2rem;
        height: 2rem;
        flex-shrink: 0;
        border-radius: 50%;
        border: 1.5px solid #ced4da;
        font-size: 0.875rem;
        font-weight: 600;
        background: #fff;
    }

    .user-wizard-label {
        display: flex;
        flex-direction: column;
        min-width: 0;
        line-height: 1.25;
    }

    .user-wizard-label strong {
        font-size: 0.9375rem;
        font-weight: 600;
        color: inherit;
    }

    .user-wizard-label small {
        font-size: 0.75rem;
        color: #adb5bd;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    .user-wizard-connector {
        flex: 1 1 2rem;
        height: 1px;
        background: #dee2e6;
        min-width: 1.5rem;
    }

    .user-wizard-item.is-active {
        color: #0d47a1;
    }

    .user-wizard-item.is-active .user-wizard-index {
        border-color: #0d47a1;
        background: #0d47a1;
        color: #fff;
    }

    .user-wizard-item.is-done .user-wizard-index {
        border-color: #0d47a1;
        background: rgba(13, 71, 161, 0.08);
        color: #0d47a1;
    }

    .user-wizard-item.is-done .user-wizard-label strong {
        color: #0d47a1;
    }

    @media (max-width: 575.98px) {
        .user-wizard-label small {
            display: none;
        }
    }
</style>
@endpush

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', () => {
    const formulaire = document.getElementById('form-nouvel-utilisateur');
    if (!formulaire) return;

    const panel1 = formulaire.querySelector('[data-etape-panel="1"]');
    const panel2 = formulaire.querySelector('[data-etape-panel="2"]');
    const btnRetour = document.getElementById('btn-etape-retour');
    const btnSuivant = document.getElementById('btn-etape-suivant');
    const btnCreer = document.getElementById('btn-creer-compte');
    const steps = [...document.querySelectorAll('[data-wizard-step]')];

    let etape = Number(formulaire.dataset.etapeInitiale || 1);

    const champsEtape1 = () => [...formulaire.querySelectorAll('[data-etape1-required]')];

    const etape1Complete = () => {
        const password = document.getElementById('password');
        const confirmation = document.getElementById('password_confirmation');

        return champsEtape1().every((champ) => {
            if (champ.disabled) return false;
            return String(champ.value || '').trim() !== '';
        }) && password.value === confirmation.value;
    };

    const majBoutonSuivant = () => {
        const pret = etape1Complete();
        btnSuivant.hidden = etape !== 1 || !pret;
        btnSuivant.disabled = !pret;
    };

    const majSousTitre = () => {
        const texte = document.querySelector('[data-wizard-sous-titre]');
        if (!texte) return;
        texte.textContent = etape === 1
            ? 'Étape 1 sur 2 — renseignez l’identité et l’affectation.'
            : 'Étape 2 sur 2 — définissez les droits d’accès.';
    };

    const afficherEtape = (numero) => {
        etape = numero;
        panel1.hidden = numero !== 1;
        panel2.hidden = numero !== 2;

        btnRetour.hidden = numero !== 2;
        btnCreer.hidden = numero !== 2;
        majBoutonSuivant();

        steps.forEach((step) => {
            const n = Number(step.dataset.wizardStep);
            step.classList.toggle('is-active', n === numero);
            step.classList.toggle('is-done', n < numero);
        });

        majSousTitre();

        if (numero === 2) {
            panel2.scrollIntoView({ behavior: 'smooth', block: 'start' });
        }
    };

    const validerEtape1 = () => {
        let ok = true;
        champsEtape1().forEach((champ) => {
            if (!champ.checkValidity()) {
                champ.reportValidity();
                ok = false;
            }
        });

        const password = document.getElementById('password');
        const confirmation = document.getElementById('password_confirmation');
        if (password.value !== confirmation.value) {
            confirmation.setCustomValidity('La confirmation ne correspond pas au mot de passe.');
            confirmation.reportValidity();
            ok = false;
        } else {
            confirmation.setCustomValidity('');
        }

        return ok;
    };

    btnSuivant.addEventListener('click', () => {
        if (!validerEtape1()) return;
        afficherEtape(2);
    });

    btnRetour.addEventListener('click', () => afficherEtape(1));

    formulaire.addEventListener('input', majBoutonSuivant);
    formulaire.addEventListener('change', majBoutonSuivant);

    afficherEtape(etape);
    majBoutonSuivant();
});
</script>
@endpush
