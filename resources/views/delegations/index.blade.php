@extends('layouts.app')

@section('title', 'Gestion des délégations')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/referentiels.css') }}">
    <link rel="stylesheet" href="{{ asset('css/delegations.css') }}">
    <link rel="stylesheet" href="{{ asset('css/liste-outils.css') }}">
@endpush

@section('page-header')
    <div class="col">
        <h2 class="h3 fw-bold mb-1">Gestion des délégations</h2>
        <p class="text-muted mb-0 small">
            Continuité d’activité en cas d’indisponibilité — réservé aux responsables hiérarchiques
            de votre périmètre (Service, Bureau, Section…).
        </p>
    </div>
@endsection

@section('content')
    <div class="card shadow-sm border-0">
        <div
            class="card-header bg-white d-flex flex-wrap gap-3 justify-content-between align-items-center px-4 py-3"
            data-liste-recherche
            data-url="{{ route('delegations.index') }}"
            data-target="#table-wrapper"
        >
            <span class="fw-semibold fs-5">
                <i class="bi bi-people me-2"></i>Liste des délégations
            </span>
            <div class="d-flex flex-1 gap-3 align-items-center liste-outils-toolbar">
                <x-barre-recherche
                    placeholder="Rechercher délégant, délégué…"
                    :value="$recherche ?? ''"
                    name="recherche"
                />

                <select class="form-select liste-select" data-param="statut">
                    <option value="" @selected(($statut ?? '') === '')>Tous les statuts</option>
                    <option value="planifiee" @selected(($statut ?? '') === 'planifiee')>Planifiées</option>
                    <option value="active" @selected(($statut ?? '') === 'active')>Actives</option>
                    <option value="terminee" @selected(($statut ?? '') === 'terminee')>Terminées</option>
                    <option value="annulee" @selected(($statut ?? '') === 'annulee')>Annulées</option>
                </select>

                @can('delegations.creer')
                    <a href="{{ route('delegations.create') }}" class="btn btn-primary flex-shrink-0 ms-auto liste-new-btn">
                        <i class="bi bi-plus-lg me-1"></i>Nouvelle délégation
                    </a>
                @endcan
            </div>
        </div>

        <div class="card-body p-0" id="table-wrapper">
            @include('delegations._table', ['delegations' => $delegations])
        </div>
    </div>
@endsection

@push('scripts')
    <script src="{{ asset('js/liste-recherche.js') }}"></script>
@endpush
