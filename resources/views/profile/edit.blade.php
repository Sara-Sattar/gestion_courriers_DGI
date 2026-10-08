@extends('layouts.app')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/profile.css') }}">
@endpush

@section('title', 'Mon profil')

@section('content')
@php
    $affiliation = $user->unite?->nom ?? $user->role?->name;
@endphp

{{-- En-tête profil — pleine largeur --}}
<div class="card profile-header border-0 shadow-sm mb-4">
    <div class="card-body p-4 p-md-5">
        <div class="d-flex flex-column flex-md-row align-items-center align-items-md-start gap-4">
            <div class="profile-avatar flex-shrink-0">
                <i class="bi bi-person-fill"></i>
            </div>
            <div class="flex-grow-1 text-center text-md-start">
                <h3 class="h4 fw-bold mb-1">{{ $user->name }}</h3>
                @if ($affiliation)
                    <p class="profile-affiliation mb-1">{{ $affiliation }}</p>
                @endif
                <p class="text-muted mb-0">{{ $user->email }}</p>
            </div>
            <div class="text-center text-md-end flex-shrink-0">
                <small class="text-muted">
                    <i class="bi bi-calendar3 me-1"></i>
                    Compte créé le {{ $user->created_at?->format('d/m/Y') }}
                </small>
            </div>
        </div>
    </div>
</div>

{{-- Formulaires — pleine largeur --}}
<div class="row g-4">
    <div class="col-lg-6">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-header bg-white py-3">
                <h5 class="mb-0 fw-semibold">
                    <i class="bi bi-person-gear me-2 text-primary"></i>Informations personnelles
                </h5>
            </div>
            <div class="card-body p-4">
                @include('profile.partials.update-profile-information-form')
            </div>
        </div>
    </div>

    <div class="col-lg-6">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-header bg-white py-3">
                <h5 class="mb-0 fw-semibold">
                    <i class="bi bi-key me-2 text-primary"></i>Mot de passe
                </h5>
            </div>
            <div class="card-body p-4">
                @include('profile.partials.update-password-form')
            </div>
        </div>
    </div>
</div>
@endsection
