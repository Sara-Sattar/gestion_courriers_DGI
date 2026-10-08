@extends('layouts.app')

@section('title', 'Gestion des utilisateurs')

@push('styles')
<link rel="stylesheet" href="{{ asset('css/referentiels.css') }}">
<link rel="stylesheet" href="{{ asset('css/users-admin.css') }}">
<link rel="stylesheet" href="{{ asset('css/liste-outils.css') }}">
@endpush

@section('page-header')
    <div class="col">
        <h2 class="h3 fw-bold mb-1">Gestion des utilisateurs</h2>
        <p class="text-muted mb-0 small">
            Comptes agents, profils et droits d’accès à l’application.
        </p>
    </div>
@endsection

@section('content')
    <div class="card shadow-sm border-0">
        <div
            class="card-header bg-white d-flex flex-wrap gap-3 justify-content-between align-items-center px-4 py-3"
            data-liste-recherche
            data-url="{{ route('users.index') }}"
            data-target="#table-wrapper"
        >
            <span class="fw-semibold fs-5"><i class="bi bi-people me-2"></i>Liste des utilisateurs</span>
            <div class="d-flex flex-1 gap-3 align-items-center liste-outils-toolbar">
                <x-barre-recherche
                    placeholder="Rechercher un utilisateur…"
                    :value="$recherche ?? ''"
                    name="recherche"
                />
                <a href="{{ route('users.create') }}" class="btn btn-primary flex-shrink-0 ms-auto liste-new-btn">
                    <i class="bi bi-plus-lg me-1"></i>Nouvel utilisateur
                </a>
            </div>
        </div>

        <div class="card-body p-0" id="table-wrapper">
            @include('users._table', ['users' => $users, 'recherche' => $recherche ?? ''])
        </div>
    </div>
@endsection

@push('scripts')
    <script src="{{ asset('js/liste-recherche.js') }}"></script>
@endpush
