@extends('layouts.app')

@section('title', 'Traitement des courriers')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/liste-outils.css') }}">
@endpush

@section('page-header')
    <div class="col">
        <h2 class="h3 fw-bold mb-1">Courriers à traiter</h2>
        <p class="text-muted mb-0 small">
            @if (! empty($enRemplacement) && $sujetCourant)
                Dossiers réceptionnés ou suspendus de <strong>{{ $sujetCourant->name }}</strong>
                (mode remplacement).
            @else
                Uniquement les dossiers réceptionnés (ou suspendus) dont vous êtes responsable.
            @endif
        </p>
    </div>
@endsection

@section('content')
    <div class="card shadow-sm border-0 mb-4">
        <div
            class="card-header bg-white d-flex flex-wrap gap-3 justify-content-between align-items-center px-4 py-3"
            data-liste-recherche
            data-url="{{ route('courriers.traitement.index') }}"
            data-target="#table-wrapper"
        >
            <span class="fw-semibold fs-5"><i class="bi bi-gear me-2"></i>Liste à traiter</span>
            <div class="d-flex flex-1 gap-3 align-items-center liste-outils-toolbar">
                <x-barre-recherche
                    placeholder="Rechercher par n° de dépôt…"
                    :value="$filtreNumero ?? ''"
                    name="numero"
                />
                <x-filtre-effacable
                    param="date_debut"
                    :value="$filtreDateDebut ?? ''"
                    title="Réception du"
                />
                <x-filtre-effacable
                    param="date_fin"
                    :value="$filtreDateFin ?? ''"
                    title="Réception au"
                />
            </div>
        </div>

        <div class="card-body p-0" id="table-wrapper">
            @include('courriers._traitement-table', [
                'courriers' => $courriers,
                'sujetCourant' => $sujetCourant,
                'enRemplacement' => $enRemplacement,
            ])
        </div>
    </div>

    {{-- Historique personnel : courriers déjà traités (traite_par_id) — Favorable / Rejeté / Suspendu --}}
    <div class="card shadow-sm border-0 mb-4" id="historique-traitements">
        <div class="card-header bg-white px-4 py-3">
            <h5 class="mb-0 fw-semibold">
                <i class="bi bi-journal-text me-2 text-primary"></i>Historique de mes traitements
                @if (! empty($enRemplacement) && $sujetCourant)
                    <span class="text-muted fw-normal small">— pour {{ $sujetCourant->name }}</span>
                @else
                    <span class="text-muted fw-normal small">— tous les courriers que vous avez traités</span>
                @endif
            </h5>
        </div>
        <div class="table-responsive">
            <table class="table table-hover mb-0 align-middle">
                <thead class="table-light">
                    <tr>
                        <th scope="col">N°</th>
                        <th scope="col">Date dépôt</th>
                        <th scope="col">Date réception</th>
                        <th scope="col">Date traitement</th>
                        <th scope="col">Contribuable</th>
                        <th scope="col">Objet</th>
                        <th scope="col">Type</th>
                        <th scope="col">Unité</th>
                        <th scope="col">Résultat</th>
                        <th scope="col">Statut actuel</th>
                        <th scope="col">Délivrance</th>
                        <th scope="col" class="text-end">Fiche</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($historiquesTraitements as $courrier)
                        <tr>
                            <td class="fw-semibold">{{ $courrier->numero }}</td>
                            <td>{{ $courrier->date_depot?->format('d/m/Y') ?: '—' }}</td>
                            <td>{{ $courrier->date_reception?->format('d/m/Y') ?: '—' }}</td>
                            <td>{{ $courrier->date_traitement?->format('d/m/Y') ?: '—' }}</td>
                            <td>{{ $courrier->contribuable?->nom_raison_sociale ?: '—' }}</td>
                            <td>{{ \Illuminate\Support\Str::limit($courrier->objet, 40) }}</td>
                            <td>{{ $courrier->typeCourrier?->nom ?: '—' }}</td>
                            <td>{{ $courrier->uniteDestinataire?->nom ?: ($courrier->uniteActuelle?->nom ?: '—') }}</td>
                            <td>
                                @if ($label = $courrier->resultatTraitementLabel())
                                    <span class="badge bg-{{ $courrier->resultatTraitementBadgeColor() }}">
                                        {{ $label }}
                                    </span>
                                @else
                                    —
                                @endif
                            </td>
                            <td>
                                <span class="badge bg-{{ $courrier->statut->badgeColor() }}">
                                    {{ $courrier->statut->label() }}
                                </span>
                            </td>
                            <td>{{ $courrier->date_delivrance?->format('d/m/Y') ?: '—' }}</td>
                            <td class="text-end">
                                <a href="{{ route('courriers.traitement.show', $courrier) }}" class="btn btn-sm btn-outline-secondary" title="Consulter">
                                    <i class="bi bi-eye"></i>
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="12" class="text-center text-muted py-4">
                                Aucun traitement enregistré pour le moment.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if ($historiquesTraitements->hasPages())
            <div class="card-footer bg-white d-flex justify-content-end py-3">
                {{ $historiquesTraitements->links('pagination.defaut') }}
            </div>
        @endif
    </div>
@endsection

@push('scripts')
    <script src="{{ asset('js/liste-recherche.js') }}"></script>
@endpush
