@extends('layouts.app')

@section('title', 'Nouvelle unité')

@section('page-header')
    <div class="col">
        <x-lien-retour :href="route('unites.index')">Unités</x-lien-retour>
        <h2 class="h3 fw-bold mb-1">Nouvelle unité</h2>
        <p class="text-muted mb-0 small">
            L'unité pourra recevoir des courriers et accueillir des agents dès sa création.
        </p>
    </div>
@endsection

@section('content')
<div class="row justify-content-center">
    <div class="col-xl-9">
        <div class="card shadow-sm border-0">
            <div class="card-body p-4">
                <form method="POST" action="{{ route('unites.store') }}">
                    @csrf

                    @include('unites._form')

                    <div class="d-flex justify-content-end gap-2 mt-4 pt-3 border-top">
                        <a href="{{ route('unites.index') }}" class="btn btn-light border">Annuler</a>
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-check-lg me-1"></i>Créer l'unité
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
