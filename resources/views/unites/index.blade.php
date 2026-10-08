@extends('layouts.app')

@section('title', 'Unités destinataires')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/referentiels.css') }}">
    <link rel="stylesheet" href="{{ asset('css/liste-outils.css') }}">
@endpush

@section('page-header')
    <div class="col">
        <h2 class="h3 fw-bold mb-1">Unités destinataires</h2>
        <p class="text-muted mb-0 small">
            Structures auxquelles un courrier peut être adressé. Leur code alimente le numéro de courrier.
        </p>
    </div>
@endsection

@section('content')
    @include('referentiels._nav')

    <div class="card shadow-sm border-0">
        <div
            class="card-header bg-white d-flex flex-wrap gap-3 justify-content-between align-items-center px-4 py-3"
            data-liste-recherche
            data-url="{{ route('unites.index') }}"
            data-target="#table-wrapper"
        >
            <span class="fw-semibold fs-5"><i class="bi bi-diagram-3 me-2"></i>Liste des unités</span>
            <div class="d-flex flex-1 gap-3 align-items-center liste-outils-toolbar">
                <x-barre-recherche
                    placeholder="Rechercher une unité…"
                    :value="$recherche"
                    name="recherche"
                />
                <select class="form-select liste-select" data-param="statut">
                    <option value="" @selected($statut === '')>Tous les statuts</option>
                    <option value="actifs" @selected($statut === 'actifs')>Unités actives</option>
                    <option value="inactifs" @selected($statut === 'inactifs')>Unités inactives</option>
                </select>
                <a href="{{ route('unites.create') }}" class="btn btn-primary flex-shrink-0 ms-auto liste-new-btn">
                    <i class="bi bi-plus-lg me-1"></i>Nouvelle unité
                </a>
            </div>
        </div>

        <div class="card-body p-0" id="table-wrapper">
            @include('unites._table', compact('unites', 'recherche', 'statut'))
        </div>
    </div>
@endsection

@push('scripts')
    <script src="{{ asset('js/liste-recherche.js') }}"></script>
@endpush
