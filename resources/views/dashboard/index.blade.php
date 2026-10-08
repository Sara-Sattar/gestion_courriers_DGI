@extends('layouts.app')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/dashboard.css') }}">
    <link rel="stylesheet" href="{{ asset('css/liste-outils.css') }}">
@endpush

@section('title', 'Accueil')

@section('page-header')
    <div class="col">
        @if (! empty($enRemplacement) && $sujetCourant)
            <h2 class="h3 mb-0 fw-bold">Espace de {{ $sujetCourant->name }}</h2>
            <p class="text-muted mb-0 mt-1">
                Mode remplacement — connecté en tant que {{ Auth::user()->name }}
                · {{ now()->locale('fr')->translatedFormat('l j F Y') }}
            </p>
        @else
            <h2 class="h3 mb-0 fw-bold">Bonjour, {{ Auth::user()->name }}</h2>
            <p class="text-muted mb-0 mt-1">{{ now()->locale('fr')->translatedFormat('l j F Y') }}</p>
        @endif
    </div>
@endsection

@section('content')
@include('dashboard._stats')

<div class="card">
    <div
        class="card-header d-flex flex-wrap gap-3 justify-content-between align-items-center px-4 py-3"
        data-liste-recherche
        data-url="{{ route('dashboard') }}"
        data-target="#table-wrapper"
    >
        <span class="fw-semibold fs-5"><i class="bi bi-clock-history me-2"></i>Liste des courriers</span>
        <div class="d-flex flex-1 gap-3 align-items-center liste-outils-toolbar">
            <x-barre-recherche
                placeholder="Rechercher un courrier…"
                :value="$search ?? ''"
                name="search"
            />

            @if ($filter)
                <input type="hidden" data-param="filter" value="{{ $filter }}">
            @endif
            @if ($statut ?? null)
                <input type="hidden" data-param="statut" value="{{ $statut }}">
            @endif

            <select class="form-select liste-select" data-param="sort">
                <option value="recent" @selected(($sort ?? 'recent') === 'recent')>Plus récents</option>
                <option value="oldest" @selected(($sort ?? '') === 'oldest')>Plus anciens</option>
            </select>

            @can('courriers.creer')
                <a href="{{ route('courriers.create') }}" class="btn btn-primary flex-shrink-0 ms-auto liste-new-btn">
                    <i class="bi bi-plus-lg me-1"></i>Nouveau courrier
                </a>
            @endcan
        </div>
    </div>
    <div class="card-body p-0" id="table-wrapper">
        @include('dashboard._table', ['courriers' => $courriers])
    </div>
</div>
@endsection

@push('scripts')
    <script src="{{ asset('js/liste-recherche.js') }}"></script>
@endpush
