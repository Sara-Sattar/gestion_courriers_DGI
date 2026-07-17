@extends('layouts.app')

@section('title', 'Détails de l\'utilisateur')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('users.index') }}">Utilisateurs</a></li>
    <li class="breadcrumb-item active">Détails</li>
@endsection

@section('content')
    <div class="card shadow-sm border-0 mb-4">
        <div class="card-header bg-white">
            <h5 class="mb-0"><i class="bi bi-person me-2 text-primary"></i>Détails de l'utilisateur</h5>
        </div>
        <div class="card-body">
            <div class="row mb-3">
                <div class="col-md-6">
                    <p class="mb-2"><strong>Nom:</strong> {{ $user->name }}</p>
                    <p class="mb-2"><strong>Email:</strong> {{ $user->email }}</p>
                    <p class="mb-2"><strong>Rôle:</strong> {{ $user->role?->name ?? '-' }}</p>
                    <p class="mb-2"><strong>Unité:</strong> {{ $user->unite?->nom ?? '-' }}</p>
                    <p class="mb-0"><strong>Date de création:</strong> {{ $user->created_at?->format('d/m/Y H:i') }}</p>
                </div>
            </div>
        </div>
        <div class="card-footer bg-light d-flex justify-content-end gap-2">
            <a href="{{ route('users.edit', $user) }}" class="btn btn-warning btn-sm">
                <i class="bi bi-pencil me-1"></i>Modifier
            </a>
            <form action="{{ route('users.destroy', $user) }}" method="POST" onsubmit="return confirm('Êtes-vous sûr de vouloir supprimer cet utilisateur ?')">
                @csrf
                @method('DELETE')
                <button type="submit" class="btn btn-danger btn-sm">
                    <i class="bi bi-trash me-1"></i>Supprimer
                </button>
            </form>
        </div>
    </div>
@endsection
