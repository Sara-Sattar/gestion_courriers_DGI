@extends('layouts.app')

@section('title', 'Nouveau type de courrier')

@section('page-header')
    <div class="col">
        <x-lien-retour :href="route('types-courrier.index')">Types de courrier</x-lien-retour>
        <h2 class="h3 fw-bold mb-1">Nouveau type de courrier</h2>
        <p class="text-muted mb-0 small">
            Le type s'affichera à l'enregistrement une fois la catégorie correspondante sélectionnée.
        </p>
    </div>
@endsection

@section('content')
<div class="row justify-content-center">
    <div class="col-xl-9">
        <div class="card shadow-sm border-0">
            <div class="card-body p-4">
                <form method="POST" action="{{ route('types-courrier.store') }}">
                    @csrf

                    @include('types-courrier._form')

                    <div class="d-flex justify-content-end gap-2 mt-4 pt-3 border-top">
                        <a href="{{ route('types-courrier.index') }}" class="btn btn-light border">Annuler</a>
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-check-lg me-1"></i>Créer le type
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
