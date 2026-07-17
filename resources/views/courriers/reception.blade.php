@extends('layouts.app')

@use(App\Enums\StatutCourrier)
@section('content')
<div class="py-4">
    <div class="container">
        <div class="mb-4">
            <h1 class="h2 fw-semibold text-gray-800">Réception des Courriers</h1>
        </div>

        <!-- Today's Courriers -->
        <div class="mb-6">
            <div class="card shadow-sm">
                <div class="card-body">
                    <h5 class="card-title mb-4">Confirmation de réception</h5>
                    
                    <form id="reception-form" action="{{ route('courriers.receptionner') }}" method="POST">
                        @csrf
                        <div class="d-flex justify-content-between align-items-center mb-4">
                            <div class="d-flex align-items-center gap-2">
                                <input type="checkbox" id="select-all-today" class="form-check-input">
                                <label for="select-all-today" class="form-check-label text-muted">Tout sélectionner</label>
                            </div>
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-check-circle me-2"></i>Réceptionner
                            </button>
                        </div>
                        
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead class="table-light">
                                    <tr>
                                        <th scope="col"></th>
                                        <th scope="col">Référence</th>
                                        <th scope="col">Objet</th>
                                        <th scope="col">Expéditeur</th>
                                        <th scope="col">Date de dépôt</th>
                                        <th scope="col">Priorité</th>
                                        <th scope="col">Statut</th>
                                        <th scope="col">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($courriersAujourdhui as $courrier)
                                        <tr>
                                            <td>
                                                <input type="checkbox" name="courrier_ids[]" value="{{ $courrier->id }}" class="courrier-checkbox form-check-input">
                                            </td>
                                            <td>{{ $courrier->numero }}</td>
                                            <td>{{ $courrier->objet }}</td>
                                            <td>{{ $courrier->contribuable?->nom ?? 'N/A' }}</td>
                                            <td>{{ $courrier->date_depot->format('d/m/Y H:i') }}</td>
                                            <td>
                                                <span class="badge {{ $courrier->priorite === 'haute' ? 'bg-danger' : ($courrier->priorite === 'moyenne' ? 'bg-warning' : 'bg-success') }}">
                                                    {{ ucfirst($courrier->priorite) }}
                                                </span>
                                            </td>
                                            <td>
                                                <span class="badge bg-{{ $courrier->statut->badgeColor() }}">
                                                    {{ $courrier->statut->label() }}
                                                </span>
                                            </td>
                                            <td class="text-nowrap">
                                                @if($courrier->statut === StatutCourrier::Receptionne || $courrier->statut === StatutCourrier::Reaffecte)
                                                    <form action="{{ route('courriers.annuler-reception', $courrier) }}" method="POST" class="d-inline" onsubmit="return confirm('Êtes-vous sûr de vouloir annuler la réception de ce courrier ?');">
                                                        @csrf
                                                        <button type="submit" class="btn btn-sm btn-warning me-1">
                                                            <i class="bi bi-x-circle"></i> Annuler
                                                        </button>
                                                    </form>
                                                @endif

                                                @if($courrier->statut === StatutCourrier::Receptionne || $courrier->statut === StatutCourrier::Reaffecte)
                                                    <button type="button" class="btn btn-sm btn-outline-warning reaffecter-btn" data-courrier-id="{{ $courrier->id }}">
                                                        <i class="bi bi-arrow-left-right"></i> Réaffecter
                                                    </button>
                                                @endif
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="8" class="text-center text-muted py-4">Aucun courrier déposé aujourd'hui.</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Pending Courriers -->
        <div class="mb-6">
            <div class="card shadow-sm">
                <div class="card-body">
                    <h5 class="card-title mb-4">Courriers en instance de réception (avant la date du jour)</h5>
                    
                    <form id="reception-form-pending" action="{{ route('courriers.receptionner') }}" method="POST">
                        @csrf
                        <div class="d-flex justify-content-between align-items-center mb-4">
                            <div class="d-flex align-items-center gap-2">
                                <input type="checkbox" id="select-all-pending" class="form-check-input">
                                <label for="select-all-pending" class="form-check-label text-muted">Tout sélectionner</label>
                            </div>
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-check-circle me-2"></i>Réceptionner
                            </button>
                        </div>
                        
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead class="table-light">
                                    <tr>
                                        <th scope="col"></th>
                                        <th scope="col">Référence</th>
                                        <th scope="col">Objet</th>
                                        <th scope="col">Expéditeur</th>
                                        <th scope="col">Date de dépôt</th>
                                        <th scope="col">Priorité</th>
                                        <th scope="col">Statut</th>
                                        <th scope="col">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($courriersEnInstance as $courrier)
                                        <tr>
                                            <td>
                                                <input type="checkbox" name="courrier_ids[]" value="{{ $courrier->id }}" class="courrier-checkbox form-check-input">
                                            </td>
                                            <td>{{ $courrier->numero }}</td>
                                            <td>{{ $courrier->objet }}</td>
                                            <td>{{ $courrier->contribuable?->nom ?? 'N/A' }}</td>
                                            <td>{{ $courrier->date_depot->format('d/m/Y H:i') }}</td>
                                            <td>
                                                <span class="badge {{ $courrier->priorite === 'haute' ? 'bg-danger' : ($courrier->priorite === 'moyenne' ? 'bg-warning' : 'bg-success') }}">
                                                    {{ ucfirst($courrier->priorite) }}
                                                </span>
                                            </td>
                                            <td>
                                                <span class="badge bg-{{ $courrier->statut->badgeColor() }}">
                                                    {{ $courrier->statut->label() }}
                                                </span>
                                            </td>
                                            <td class="text-nowrap">
                                                @if($courrier->statut === StatutCourrier::Receptionne || $courrier->statut === StatutCourrier::Reaffecte)
                                                    <form action="{{ route('courriers.annuler-reception', $courrier) }}" method="POST" class="d-inline" onsubmit="return confirm('Êtes-vous sûr de vouloir annuler la réception de ce courrier ?');">
                                                        @csrf
                                                        <button type="submit" class="btn btn-sm btn-warning me-1">
                                                            <i class="bi bi-x-circle"></i> Annuler
                                                        </button>
                                                    </form>
                                                @endif

                                                @if($courrier->statut === StatutCourrier::Receptionne || $courrier->statut === StatutCourrier::Reaffecte)
                                                    <button type="button" class="btn btn-sm btn-outline-warning reaffecter-btn" data-courrier-id="{{ $courrier->id }}">
                                                        <i class="bi bi-arrow-left-right"></i> Réaffecter
                                                    </button>
                                                @endif
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="8" class="text-center text-muted py-4">Aucun courrier en instance de réception.</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </form>
                </div>
            </div>
        </div>



        <!-- Reaffectation History -->
        <div class="mb-6">
            <div class="card shadow-sm">
                <div class="card-body">
                    <h5 class="card-title mb-4">Historique des réaffectations</h5>
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead class="table-light">
                                <tr>
                                    <th scope="col">Référence</th>
                                    <th scope="col">Ancien destinataire</th>
                                    <th scope="col">Nouveau destinataire</th>
                                    <th scope="col">Utilisateur</th>
                                    <th scope="col">Date</th>
                                    <th scope="col">Heure</th>
                                    <th scope="col">Motif</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($historiquesReaffectations as $historique)
                                    <tr>
                                        <td>{{ $historique->courrier?->numero ?? 'N/A' }}</td>
                                        <td>
                                            {{ $historique->ancienResponsable?->name ?? $historique->uniteSource?->nom ?? 'N/A' }}
                                        </td>
                                        <td>
                                            {{ $historique->nouveauResponsable?->name ?? $historique->uniteDestination?->nom ?? 'N/A' }}
                                        </td>
                                        <td>{{ $historique->user?->name ?? 'N/A' }}</td>
                                        <td>{{ $historique->created_at->format('d/m/Y') }}</td>
                                        <td>{{ $historique->created_at->format('H:i:s') }}</td>
                                        <td>{{ $historique->motif }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="7" class="text-center text-muted py-4">Aucune réaffectation enregistrée.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    <div class="mt-4">
                        {{ $historiquesReaffectations->links('pagination::bootstrap-5') }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Reaffectation Modal -->
<div class="modal fade" id="reaffecterModal" tabindex="-1" aria-labelledby="reaffecterModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="reaffecter-form" method="POST">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title" id="reaffecterModalLabel">Réaffecter le courrier</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="type_destinataire" class="form-label">Type de destinataire</label>
                        <select name="type_destinataire" id="type_destinataire" class="form-select" required>
                            <option value="utilisateur">Utilisateur</option>
                            <option value="unite">Unité</option>
                        </select>
                    </div>
                    <div class="mb-3" id="destinataire-utilisateur">
                        <label for="destinataire_utilisateur" class="form-label">Utilisateur</label>
                        <select name="destinataire_id" id="destinataire_utilisateur" class="form-select">
                            @foreach($allowedUsers as $user)
                                <option value="{{ $user->id }}">{{ $user->name }} ({{ $user->unite?->nom ?? 'N/A' }})</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3" id="destinataire-unite" style="display: none;">
                        <label for="destinataire_unite" class="form-label">Unité</label>
                        <select name="destinataire_id" id="destinataire_unite" class="form-select">
                            @foreach($allowedUnites as $unite)
                                <option value="{{ $unite->id }}">{{ $unite->nom }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="motif" class="form-label">Motif</label>
                        <textarea name="motif" id="motif" rows="3" class="form-control" required></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                    <button type="submit" class="btn btn-warning">Confirmer</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const selectAllToday = document.getElementById('select-all-today');
    const selectAllPending = document.getElementById('select-all-pending');
    const typeDestinataire = document.getElementById('type_destinataire');
    const modalElement = document.getElementById('reaffecterModal');

    if (selectAllToday) {
        selectAllToday.addEventListener('change', function() {
        const checkboxes = document.querySelectorAll('#reception-form .courrier-checkbox');
        checkboxes.forEach(cb => cb.checked = this.checked);
        });
    }

    if (selectAllPending) {
        selectAllPending.addEventListener('change', function() {
        const checkboxes = document.querySelectorAll('#reception-form-pending .courrier-checkbox');
        checkboxes.forEach(cb => cb.checked = this.checked);
        });
    }

    if (typeDestinataire) {
        typeDestinataire.addEventListener('change', function() {
        const type = this.value;
        document.getElementById('destinataire-utilisateur').style.display = type === 'utilisateur' ? 'block' : 'none';
        document.getElementById('destinataire-unite').style.display = type === 'unite' ? 'block' : 'none';
        });
    }

    if (modalElement && window.bootstrap) {
        const reaffecterModal = new bootstrap.Modal(modalElement);
        document.querySelectorAll('.reaffecter-btn').forEach(btn => {
            btn.addEventListener('click', function() {
                const courrierId = this.dataset.courrierId;
                const form = document.getElementById('reaffecter-form');
                form.action = `/courriers/${courrierId}/reaffecter`;
                reaffecterModal.show();
            });
        });
    }
});
</script>
@endsection
