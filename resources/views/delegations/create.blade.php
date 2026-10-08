@extends('layouts.app')

@section('title', 'Nouvelle délégation')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/formulaires.css') }}">
    <link rel="stylesheet" href="{{ asset('css/delegations.css') }}">
@endpush

@section('page-header')
    <div class="col">
        <x-lien-retour :href="route('delegations.index')">Délégations</x-lien-retour>
        <h2 class="h3 fw-bold mb-1">Nouvelle délégation</h2>
        <p class="text-muted mb-0 small">
            Désignez un délégué dans votre périmètre hiérarchique pour assurer la continuité.
        </p>
    </div>
@endsection

@section('content')
    <div class="card shadow-sm border-0 form-card">
        <div class="card-body p-4">
            @include('delegations._form', [
                'action' => route('delegations.store'),
                'method' => 'POST',
                'delegation' => null,
                'unites' => $unites,
                'motifs' => $motifs,
                'agents' => $agents,
                'remplacants' => $remplacants,
            ])
        </div>
    </div>
@endsection
