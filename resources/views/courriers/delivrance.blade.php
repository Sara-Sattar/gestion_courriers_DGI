@extends('layouts.app')

@section('title', 'Délivrance des documents')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/liste-outils.css') }}">
@endpush

@section('page-header')
    <div class="col">
        <h2 class="h3 fw-bold mb-1">Documents à délivrer</h2>
        <p class="text-muted mb-0 small">
            Dossiers traités favorablement — la délivrance est assurée uniquement par l'Accueil.
        </p>
    </div>
    <div class="col-auto">
        <span class="badge text-bg-light border fs-6">{{ $courriers->count() }} en attente</span>
    </div>
@endsection

@section('content')
    <div class="card shadow-sm border-0">
        <div
            class="card-header bg-white d-flex flex-wrap gap-3 justify-content-between align-items-center px-4 py-3"
            data-liste-recherche
            data-url="{{ route('courriers.delivrance.index') }}"
            data-target="#table-wrapper"
        >
            <span class="fw-semibold fs-5"><i class="bi bi-box-seam me-2"></i>File de délivrance</span>
            <div class="d-flex flex-1 gap-3 align-items-center liste-outils-toolbar">
                <x-barre-recherche
                    placeholder="Rechercher par n° de courrier…"
                    :value="$filtreNumero ?? ''"
                    name="numero"
                />
                <x-filtre-effacable
                    type="number"
                    param="annee"
                    :value="$filtreAnnee ?? ''"
                    placeholder="Année"
                    title="Année d'enregistrement"
                    min="1990"
                    max="2100"
                />
            </div>
        </div>

        <div class="card-body p-0" id="table-wrapper">
            @include('courriers._delivrance-table', ['courriers' => $courriers])
        </div>
    </div>
@endsection

@push('scripts')
    <script src="{{ asset('js/liste-recherche.js') }}"></script>
@endpush
