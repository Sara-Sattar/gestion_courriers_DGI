@extends('layouts.app')

@section('title', 'Détail du courrier')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('courriers.create') }}" class="text-decoration-none">Enregistrer un courrier</a></li>
    <li class="breadcrumb-item active">Détail du courrier</li>
@endsection

@section('content')
<div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4">
    <div>
        <h3 class="h5 mb-1">{{ $courrier->numero }}</h3>
        <div class="text-muted">Courrier enregistré le {{ $courrier->date_depot->format('d/m/Y à H:i') }}</div>
    </div>
    <div class="d-flex gap-2">
        <a href="{{ route('courriers.recu', $courrier) }}" class="btn btn-outline-primary" target="_blank">
            <i class="bi bi-file-earmark-pdf me-1"></i>Aperçu du reçu
        </a>
        <a href="{{ route('courriers.recu.telecharger', $courrier) }}" class="btn btn-primary">
            <i class="bi bi-download me-1"></i>Télécharger le reçu
        </a>
    </div>
</div>

<div class="row g-4">
    <div class="col-lg-4">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-header bg-white">
                <h5 class="mb-0"><i class="bi bi-person-vcard me-2 text-primary"></i>Contribuable</h5>
            </div>
            <div class="card-body">
                <dl class="row mb-0">
                    <dt class="col-sm-5">Nom</dt>
                    <dd class="col-sm-7">{{ $courrier->contribuable->nom_raison_sociale }}</dd>

                    <dt class="col-sm-5">IFU</dt>
                    <dd class="col-sm-7">{{ $courrier->contribuable->ifu ?: '-' }}</dd>

                    <dt class="col-sm-5">CIN</dt>
                    <dd class="col-sm-7">{{ $courrier->contribuable->cin ?: '-' }}</dd>

                    <dt class="col-sm-5">ICE</dt>
                    <dd class="col-sm-7">{{ $courrier->contribuable->ice ?: '-' }}</dd>

                    <dt class="col-sm-5">TP</dt>
                    <dd class="col-sm-7">{{ $courrier->contribuable->numero_tp ?: '-' }}</dd>

                    <dt class="col-sm-5">TH</dt>
                    <dd class="col-sm-7">{{ $courrier->contribuable->numero_th ?: '-' }}</dd>

                    <dt class="col-sm-5">Ville</dt>
                    <dd class="col-sm-7">{{ $courrier->contribuable->ville ?: '-' }}</dd>

                    <dt class="col-sm-5">Adresse</dt>
                    <dd class="col-sm-7">{{ $courrier->contribuable->adresse ?: '-' }}</dd>

                    <dt class="col-sm-5">Téléphone</dt>
                    <dd class="col-sm-7">{{ $courrier->contribuable->telephone ?: '-' }}</dd>

                    <dt class="col-sm-5">Email</dt>
                    <dd class="col-sm-7">{{ $courrier->contribuable->email ?: '-' }}</dd>
                </dl>
            </div>
        </div>
    </div>

    <div class="col-lg-8">
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white">
                <h5 class="mb-0"><i class="bi bi-envelope-open me-2 text-primary"></i>Informations du courrier</h5>
            </div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-6">
                        <div class="small text-muted">Catégorie</div>
                        <div class="fw-semibold">{{ $courrier->categorie->nom }}</div>
                    </div>
                    <div class="col-md-6">
                        <div class="small text-muted">Type de courrier</div>
                        <div class="fw-semibold">{{ $courrier->typeCourrier->nom }}</div>
                    </div>
                    <div class="col-md-6">
                        <div class="small text-muted">Objet</div>
                        <div class="fw-semibold">{{ $courrier->objet }}</div>
                    </div>
                    <div class="col-md-3">
                        <div class="small text-muted">Type d'unité</div>
                        <div class="fw-semibold">{{ $courrier->uniteDestinataire->type_unite }}</div>
                    </div>
                    <div class="col-md-3">
                        <div class="small text-muted">Unité destinataire</div>
                        <div class="fw-semibold">{{ $courrier->uniteDestinataire->nom }}</div>
                    </div>
                    <div class="col-md-4">
                        <div class="small text-muted">Code unité</div>
                        <div class="fw-semibold">{{ $courrier->code_unite }}</div>
                    </div>
                    <div class="col-md-4">
                        <div class="small text-muted">Statut</div>
                        <div><span class="badge bg-success">{{ $courrier->statut->label() }}</span></div>
                    </div>
                    <div class="col-md-4">
                        <div class="small text-muted">Enregistré par</div>
                        <div class="fw-semibold">{{ $courrier->utilisateur->name }}</div>
                    </div>
                    <div class="col-md-6">
                        <div class="small text-muted">Impôts concernés</div>
                        <div class="d-flex flex-wrap gap-2">
                            @foreach ($courrier->impots as $impot)
                                <span class="badge rounded-pill text-bg-light border">{{ $impot->impot->label() }}</span>
                            @endforeach
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="small text-muted">Années concernées</div>
                        <div class="d-flex flex-wrap gap-2">
                            @foreach ($courrier->annees->sortByDesc('annee') as $annee)
                                <span class="badge rounded-pill text-bg-light border">{{ $annee->annee }}</span>
                            @endforeach
                        </div>
                    </div>
                    <div class="col-12">
                        <div class="small text-muted">Remarque</div>
                        <div class="fw-semibold">{{ $courrier->remarque ?: 'Aucune remarque.' }}</div>
                    </div>
                </div>
            </div>
        </div>

        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white">
                <h5 class="mb-0"><i class="bi bi-paperclip me-2 text-primary"></i>Pièces jointes</h5>
            </div>
            <div class="card-body">
                @if ($courrier->piecesJointes->isEmpty())
                    <div class="alert alert-light border mb-0">Aucune pièce jointe n'a été ajoutée à ce courrier.</div>
                @else
                    <div class="list-group list-group-flush">
                        @foreach ($courrier->piecesJointes as $piece)
                            <div class="list-group-item px-0">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <div class="fw-semibold">
                                            <i class="bi {{ $piece->iconeBootstrap() }} me-2 text-primary"></i>{{ $piece->nom_original }}
                                        </div>
                                        <div class="small text-muted">
                                            {{ strtoupper($piece->extension) }} | {{ number_format($piece->taille / 1024, 2, ',', ' ') }} Ko
                                        </div>
                                    </div>
                                    <div class="d-flex gap-2">
                                        @if ($piece->estPrevisualisable())
                                            <a href="{{ route('courriers.pieces-jointes.show', [$courrier, $piece]) }}" class="btn btn-sm btn-outline-secondary" target="_blank">
                                                <i class="bi bi-eye me-1"></i>Voir
                                            </a>
                                        @endif
                                        <a href="{{ route('courriers.pieces-jointes.telecharger', [$courrier, $piece]) }}" class="btn btn-sm btn-outline-primary">
                                            <i class="bi bi-download me-1"></i>Télécharger
                                        </a>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>

        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white">
                <h5 class="mb-0"><i class="bi bi-clock-history me-2 text-primary"></i>Historique</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-sm align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Date</th>
                                <th>Utilisateur</th>
                                <th>Action</th>
                                <th>Commentaire</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($courrier->historiques as $historique)
                                <tr>
                                    <td>{{ $historique->created_at->format('d/m/Y H:i') }}</td>
                                    <td>{{ $historique->utilisateur->name }}</td>
                                    <td>{{ $historique->action->label() }}</td>
                                    <td>{{ $historique->commentaire ?: '-' }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
