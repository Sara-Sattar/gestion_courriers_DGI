@extends('layouts.app')

@section('title', $categorie->nom)

@section('page-header')
    <div class="col">
        <x-lien-retour :href="route('categories.index')">Catégories</x-lien-retour>
        <h2 class="h3 fw-bold mb-1">{{ $categorie->nom }}</h2>
        <p class="text-muted mb-0 small">Modification d'une catégorie de courrier.</p>
    </div>
@endsection

@section('content')
<div class="row justify-content-center">
    <div class="col-xl-9">
        <div class="card shadow-sm border-0">
            <div class="card-body p-4">
                <form method="POST" action="{{ route('categories.update', $categorie) }}">
                    @csrf
                    @method('PUT')

                    @include('categories._form')

                    <div class="d-flex justify-content-end gap-2 mt-4 pt-3 border-top">
                        <a href="{{ route('categories.index') }}" class="btn btn-light border">Annuler</a>
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-save me-1"></i>Enregistrer les modifications
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
