@extends('layouts.app')

@section('title', 'Modifier — '.$user->name)

@push('styles')
<link rel="stylesheet" href="{{ asset('css/formulaires.css') }}">
<link rel="stylesheet" href="{{ asset('css/permissions.css') }}">
<link rel="stylesheet" href="{{ asset('css/users-admin.css') }}">
@endpush

@section('page-header')
    <div class="col">
        <x-lien-retour :href="route('users.index')">Utilisateurs</x-lien-retour>
        <h2 class="h3 fw-bold mb-1">Modifier l’utilisateur</h2>
        <p class="text-muted mb-0 small">
            {{ $user->name }} · {{ $user->email }}
        </p>
    </div>
@endsection

@section('content')
<div class="row justify-content-center">
    <div class="col-xl-9">
        <div class="card shadow-sm border-0">
            <div class="card-body p-4">
                <form action="{{ route('users.update', $user) }}" method="POST">
                    @csrf
                    @method('PUT')

                    @include('users._form')

                    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mt-4 pt-3 border-top">
                        <a href="{{ route('users.index') }}" class="btn btn-light border">Annuler</a>
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-check-lg me-1"></i>Enregistrer les modifications
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
