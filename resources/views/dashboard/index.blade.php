@extends('layouts.app')

@section('title', 'Tableau de bord')

@section('breadcrumb')
    <li class="breadcrumb-item active">Tableau de bord</li>
@endsection

@section('content')
<div class="row g-4 mb-4">
    <div class="col-md-4">
        <div class="card">
            <div class="card-body d-flex align-items-center">
                <div class="rounded-circle bg-primary bg-opacity-10 p-3 me-3">
                    <i class="bi bi-envelope fs-4 text-primary"></i>
                </div>
                <div>
                    <div class="text-muted small">Total courriers</div>
                    <div class="fs-4 fw-bold">{{ $stats['total'] }}</div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card">
            <div class="card-body d-flex align-items-center">
                <div class="rounded-circle bg-success bg-opacity-10 p-3 me-3">
                    <i class="bi bi-calendar-day fs-4 text-success"></i>
                </div>
                <div>
                    <div class="text-muted small">Aujourd'hui</div>
                    <div class="fs-4 fw-bold">{{ $stats['aujourdhui'] }}</div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card">
            <div class="card-body d-flex align-items-center">
                <div class="rounded-circle bg-info bg-opacity-10 p-3 me-3">
                    <i class="bi bi-calendar-month fs-4 text-info"></i>
                </div>
                <div>
                    <div class="text-muted small">Ce mois</div>
                    <div class="fs-4 fw-bold">{{ $stats['mois'] }}</div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <span><i class="bi bi-clock-history me-2"></i>Derniers courriers enregistrés</span>
        @if (Auth::user()->role && Auth::user()->role->name === 'Direction Régionale')
            <a href="{{ route('courriers.create') }}" class="btn btn-sm btn-primary">
                <i class="bi bi-plus-lg me-1"></i>Nouveau courrier
            </a>
        @endif
    </div>
    <div class="card-body p-0">
        @if ($derniersCourriers->isEmpty())
            <div class="text-center text-muted py-5">
                <i class="bi bi-inbox fs-1 d-block mb-2"></i>
                Aucun courrier enregistré pour le moment.
            </div>
        @else
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>N° Courrier</th>
                            <th>Contribuable</th>
                            <th>Catégorie</th>
                            <th>Date dépôt</th>
                            <th>Statut</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($derniersCourriers as $courrier)
                            <tr>
                                <td>
                                    <a href="{{ route('courriers.show', $courrier) }}" class="text-decoration-none fw-medium">
                                        {{ $courrier->numero }}
                                    </a>
                                </td>
                                <td>{{ $courrier->contribuable->nom_raison_sociale }}</td>
                                <td>{{ $courrier->categorie->nom }}</td>
                                <td>{{ $courrier->date_depot->format('d/m/Y H:i') }}</td>
                                <td><span class="badge bg-success">{{ $courrier->statut->label() }}</span></td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>
</div>
@endsection
