@extends('layouts.app')

@section('title', $user->name)

@push('styles')
<link rel="stylesheet" href="{{ asset('css/users-admin.css') }}">
@endpush

@section('page-header')
    <div class="col">
        <x-lien-retour :href="route('users.index')">Utilisateurs</x-lien-retour>
        <h2 class="h3 fw-bold mb-1">{{ $user->name }}</h2>
        <p class="text-muted mb-0 small">
            <i class="bi bi-envelope me-1"></i>{{ $user->email }}
        </p>
    </div>
    <div class="col-auto">
        <div class="d-flex flex-wrap gap-2">
            <a href="{{ route('users.edit', $user) }}" class="btn btn-primary">
                <i class="bi bi-pencil me-1"></i>Modifier
            </a>
            <form action="{{ route('users.destroy', $user) }}" method="POST" onsubmit="return confirm('Êtes-vous sûr de vouloir supprimer cet utilisateur ?')">
                @csrf
                @method('DELETE')
                <button type="submit" class="btn btn-outline-danger">
                    <i class="bi bi-trash me-1"></i>Supprimer
                </button>
            </form>
        </div>
    </div>
@endsection

@section('content')
<div class="row justify-content-center">
    <div class="col-xl-9">
        <div class="card shadow-sm border-0 mb-4">
            <div class="card-body p-4">
                <dl class="row mb-0">
                    <dt class="col-sm-4 text-muted fw-normal">Rôle / profil</dt>
                    <dd class="col-sm-8 fw-medium">{{ $user->role?->name ?? '—' }}</dd>

                    <dt class="col-sm-4 text-muted fw-normal">Unité de rattachement</dt>
                    <dd class="col-sm-8 fw-medium">
                        {{ $user->unite ? $user->unite->nom.' ('.$user->unite->code.')' : '—' }}
                    </dd>

                    <dt class="col-sm-4 text-muted fw-normal">Créé le</dt>
                    <dd class="col-sm-8 fw-medium mb-0">{{ $user->created_at?->format('d/m/Y à H:i') ?? '—' }}</dd>
                </dl>
            </div>
        </div>

        <div class="card shadow-sm border-0">
            <div class="card-header bg-white">
                <h5 class="mb-0"><i class="bi bi-shield-lock me-2 text-primary"></i>Droits effectifs</h5>
                <p class="text-muted small mb-0 mt-1">
                    @if ($user->permissions->isNotEmpty())
                        Permissions personnalisées pour cet utilisateur.
                    @else
                        Héritées du profil « {{ $user->role?->name }} » (aucune personnalisation enregistrée).
                    @endif
                </p>
            </div>
            <div class="card-body">
                @if ($permissionsEffectives->isEmpty())
                    <p class="text-muted mb-0">Aucun droit attribué.</p>
                @else
                    <div class="row g-2">
                        @foreach ($permissionsEffectives->groupBy('groupe') as $groupe => $liste)
                            <div class="col-md-6">
                                <h6 class="text-muted text-uppercase small">{{ config('permissions.groupes.'.$groupe.'.label', $groupe) }}</h6>
                                <ul class="list-unstyled mb-3">
                                    @foreach ($liste as $permission)
                                        <li class="mb-1">
                                            <i class="bi bi-check2 text-success me-1"></i>{{ $permission->label }}
                                        </li>
                                    @endforeach
                                </ul>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
