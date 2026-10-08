@extends('layouts.app')

@use(App\Enums\ActionType)

@section('title', 'Réception des courriers')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/dashboard.css') }}">
    <link rel="stylesheet" href="{{ asset('css/reception.css') }}">
    <link rel="stylesheet" href="{{ asset('css/liste-outils.css') }}">
@endpush

@section('page-header')
    <div class="col">
        <h2 class="h3 mb-0 fw-bold">Réception des courriers</h2>
        <p class="text-muted mb-0 mt-1">
            @if (! empty($enRemplacement) && $sujetCourant)
                Vous confirmez les réceptions <strong>pour le compte de {{ $sujetCourant->name }}</strong>.
                Les dossiers acceptés lui seront attribués.
            @else
                Confirmez la prise en charge des courriers déposés ou réaffectés, refusez si nécessaire, ou réaffectez selon votre niveau.
            @endif
        </p>
    </div>
    <div class="col-auto">
        <span class="text-muted small">
            <i class="bi bi-calendar3 me-1"></i>{{ now()->locale('fr')->translatedFormat('l j F Y') }}
        </span>
    </div>
@endsection

@section('content')
<div
    class="card shadow-sm border-0 mb-4"
    data-liste-recherche
    data-url="{{ route('courriers.reception.index') }}"
    data-target="#reception-liste"
>
    <div class="card-header bg-white d-flex flex-wrap gap-3 justify-content-between align-items-center px-4 py-3">
        <span class="fw-semibold"><i class="bi bi-search me-2"></i>Recherche</span>
        <div class="d-flex flex-1 gap-3 align-items-center liste-outils-toolbar">
            <x-barre-recherche
                placeholder="Rechercher par n° de dépôt…"
                :value="$filtreNumero ?? ''"
                name="numero"
            />
            <x-filtre-effacable
                param="date_debut"
                :value="$filtreDateDebut ?? ''"
                title="Arrivée du"
            />
            <x-filtre-effacable
                param="date_fin"
                :value="$filtreDateFin ?? ''"
                title="Arrivée au"
            />
        </div>
    </div>
</div>

<div id="reception-liste">
    @include('courriers._reception-liste')
</div>

{{-- Historique des réceptions --}}
<div class="card shadow-sm border-0 mb-4">
    <div class="card-header bg-white px-4 py-3">
        <h5 class="mb-0 fw-semibold">
            <i class="bi bi-journal-check me-2 text-primary"></i>Historique des réceptions
            @if (! empty($enRemplacement) && $sujetCourant)
                <span class="text-muted fw-normal small">— pour {{ $sujetCourant->name }}</span>
            @else
                <span class="text-muted fw-normal small">— vos actions personnelles</span>
            @endif
        </h5>
    </div>
    <div class="table-responsive">
        <table class="table table-hover reception-table mb-0 align-middle">
            <thead class="table-light">
                <tr>
                    <th scope="col">N°</th>
                    <th scope="col">Action</th>
                    <th scope="col">Unité</th>
                    <th scope="col">Utilisateur</th>
                    <th scope="col">Date</th>
                    <th scope="col">Heure</th>
                    <th scope="col" class="text-end">Actions</th>
                </tr>
            </thead>
            <tbody>
                @php
                    $courriersAvecReaffectation = [];
                @endphp
                @forelse ($historiquesReceptions as $historique)
                    @php
                        // Réaffectation proposée sur la réception la plus récente d'un courrier
                        // toujours réceptionné et dont l'utilisateur (ou l'absent remplacé) est responsable.
                        $courrierHistorique = $historique->courrier;
                        $peutReaffecter = $courrierHistorique
                            && $historique->action_type === ActionType::Reception
                            && $courrierHistorique->statut === \App\Enums\StatutCourrier::Receptionne
                            && $courrierHistorique->responsable_actuel_id === $sujetCourant?->id
                            && ! in_array($courrierHistorique->id, $courriersAvecReaffectation, true)
                            && auth()->user()->can('courriers.reaffecter');
                        if ($peutReaffecter) {
                            $courriersAvecReaffectation[] = $courrierHistorique->id;
                        }
                    @endphp
                    <tr>
                        <td class="fw-semibold">{{ $historique->courrier?->numero ?? '—' }}</td>
                        <td>
                            <span class="badge bg-{{ $historique->action_type === ActionType::Reception ? 'success' : 'secondary' }}-subtle text-{{ $historique->action_type === ActionType::Reception ? 'success' : 'secondary' }}-emphasis border">
                                {{ $historique->action_type?->label() ?? '—' }}
                            </span>
                        </td>
                        <td>{{ $historique->uniteDestination?->nom ?? '—' }}</td>
                        <td>
                            {{ $historique->user?->name ?? '—' }}
                            @if ($historique->pourCompteDe)
                                <span class="reception-precision d-block">pour {{ $historique->pourCompteDe->name }}</span>
                            @endif
                        </td>
                        <td>{{ $historique->created_at->format('d/m/Y') }}</td>
                        <td>{{ $historique->created_at->format('H:i') }}</td>
                        <td class="text-end">
                            @if ($peutReaffecter)
                                <button
                                    type="button"
                                    class="btn btn-sm btn-outline-secondary"
                                    title="Réaffecter"
                                    data-reaffecter
                                    data-courrier-id="{{ $courrierHistorique->id }}"
                                    data-courrier-numero="{{ $courrierHistorique->numero }}"
                                >
                                    <i class="bi bi-arrow-left-right"></i>
                                </button>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7">
                            <div class="reception-vide">
                                <i class="bi bi-journal" aria-hidden="true"></i>
                                <p class="mb-0">Aucune réception enregistrée pour le moment.</p>
                            </div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if ($historiquesReceptions->hasPages())
        <div class="card-footer bg-white d-flex justify-content-end py-3">
            {{ $historiquesReceptions->links('pagination.defaut') }}
        </div>
    @endif
</div>

{{-- Historique des réaffectations --}}
<div class="card shadow-sm border-0 mb-4">
    <div class="card-header bg-white px-4 py-3">
        <h5 class="mb-0 fw-semibold">
            <i class="bi bi-arrow-left-right me-2 text-primary"></i>Historique des réaffectations
            @if (! empty($enRemplacement) && $sujetCourant)
                <span class="text-muted fw-normal small">— pour {{ $sujetCourant->name }}</span>
            @else
                <span class="text-muted fw-normal small">— vos actions personnelles</span>
            @endif
        </h5>
    </div>
    <div class="table-responsive">
        <table class="table table-hover reception-table mb-0 align-middle">
            <thead class="table-light">
                <tr>
                    <th scope="col">N°</th>
                    <th scope="col">Ancienne unité</th>
                    <th scope="col">Nouvelle unité</th>
                    <th scope="col">Utilisateur</th>
                    <th scope="col">Date</th>
                    <th scope="col">Motif</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($historiquesReaffectations as $historique)
                    <tr>
                        <td class="fw-semibold">{{ $historique->courrier?->numero ?? '—' }}</td>
                        <td>{{ $historique->uniteSource?->nom ?? '—' }}</td>
                        <td>{{ $historique->uniteDestination?->nom ?? '—' }}</td>
                        <td>
                            {{ $historique->user?->name ?? '—' }}
                            @if ($historique->pourCompteDe)
                                <span class="reception-precision d-block">pour {{ $historique->pourCompteDe->name }}</span>
                            @endif
                        </td>
                        <td class="text-nowrap">
                            {{ $historique->created_at->format('d/m/Y') }}
                            <span class="reception-precision">{{ $historique->created_at->format('H:i') }}</span>
                        </td>
                        <td>{{ $historique->motif ?? '—' }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6">
                            <div class="reception-vide">
                                <i class="bi bi-arrow-left-right" aria-hidden="true"></i>
                                <p class="mb-0">Aucune réaffectation enregistrée.</p>
                            </div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if ($historiquesReaffectations->hasPages())
        <div class="card-footer bg-white d-flex justify-content-end py-3">
            {{ $historiquesReaffectations->links('pagination.defaut') }}
        </div>
    @endif
</div>


{{-- Modal réaffectation --}}
<div class="modal fade" id="reaffecterModal" tabindex="-1" aria-labelledby="reaffecterModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="reaffecter-form" method="POST">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title" id="reaffecterModalLabel">Réaffecter le courrier</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fermer"></button>
                </div>
                <div class="modal-body">
                    <p class="small text-muted mb-3">
                        Courrier : <strong id="reaffecter-numero">—</strong>
                    </p>

                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label class="form-label">Date de réaffectation</label>
                            <input type="text" class="form-control-plaintext border rounded px-2 bg-body-secondary" value="{{ now()->format('d/m/Y H:i') }}" readonly>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Effectuée par</label>
                            <input type="text" class="form-control-plaintext border rounded px-2 bg-body-secondary" value="{{ $utilisateurCourant->name }}" readonly>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="unite_destination_id" class="form-label">Unité de destination <x-required /></label>
                        <select name="unite_destination_id" id="unite_destination_id" class="form-select" required>
                            <option value="">Sélectionner une unité</option>
                            @forelse ($allowedUnites as $unite)
                                <option value="{{ $unite->id }}">{{ $unite->nom }} ({{ $unite->code }}) — {{ $unite->type_unite }}</option>
                            @empty
                                <option value="" disabled>Aucune autre unité active disponible</option>
                            @endforelse
                        </select>
                        <div class="form-text">Unités autorisées selon votre niveau hiérarchique (cahier des charges).</div>
                    </div>

                    <div class="mb-0">
                        <label for="motif" class="form-label">Motif <x-required /></label>
                        <textarea name="motif" id="motif" rows="3" class="form-control" required minlength="5" placeholder="Indiquez la raison de la réaffectation…"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light border" data-bs-dismiss="modal">Annuler</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-arrow-left-right me-1"></i>Confirmer la réaffectation
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- Modal refus réception (retour B.O.) --}}
<div class="modal fade" id="refusReceptionModal" tabindex="-1" aria-labelledby="refusReceptionModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="refus-reception-form" method="POST">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title" id="refusReceptionModalLabel">Refuser la réception</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fermer"></button>
                </div>
                <div class="modal-body">
                    <p class="small text-muted mb-3">
                        Courrier : <strong id="refus-reception-numero">—</strong><br>
                        Le dossier sera renvoyé au Bureau d'ordre.
                    </p>
                    <div class="mb-0">
                        <label for="motif_refus_reception" class="form-label">Motif <x-required /></label>
                        <textarea name="motif" id="motif_refus_reception" rows="3" class="form-control" required minlength="5" placeholder="Indiquez pourquoi la destination est incorrecte…"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light border" data-bs-dismiss="modal">Annuler</button>
                    <button type="submit" class="btn btn-danger">
                        <i class="bi bi-slash-circle me-1"></i>Refuser et renvoyer au B.O.
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- Modal refus réaffectation (retour n−1) --}}
<div class="modal fade" id="refusReaffectationModal" tabindex="-1" aria-labelledby="refusReaffectationModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="refus-reaffectation-form" method="POST">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title" id="refusReaffectationModalLabel">Refuser la réaffectation</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fermer"></button>
                </div>
                <div class="modal-body">
                    <p class="small text-muted mb-3">
                        Courrier : <strong id="refus-reaffectation-numero">—</strong><br>
                        Le dossier sera renvoyé à l'unité précédente (n−1).
                    </p>
                    <div class="mb-0">
                        <label for="motif_refus_reaffectation" class="form-label">Motif <x-required /></label>
                        <textarea name="motif" id="motif_refus_reaffectation" rows="3" class="form-control" required minlength="5" placeholder="Indiquez le motif du refus…"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light border" data-bs-dismiss="modal">Annuler</button>
                    <button type="submit" class="btn btn-warning">
                        <i class="bi bi-arrow-return-left me-1"></i>Refuser et renvoyer (n−1)
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="{{ asset('js/liste-recherche.js') }}"></script>
<script>
document.addEventListener('DOMContentLoaded', () => {
    const root = document.getElementById('reception-liste');

    const bindBulk = () => {
        document.querySelectorAll('[data-select-all]').forEach((master) => {
            if (master.dataset.bound === '1') return;
            master.dataset.bound = '1';
            const formId = master.dataset.selectAll;
            master.addEventListener('change', () => {
                document.querySelectorAll(`[data-courrier-checkbox="${formId}"]`).forEach((cb) => {
                    cb.checked = master.checked;
                });
            });
        });

        document.querySelectorAll('[data-confirm-bulk]').forEach((bouton) => {
            if (bouton.dataset.bound === '1') return;
            bouton.dataset.bound = '1';
            bouton.addEventListener('click', () => {
                const formId = bouton.dataset.confirmBulk;
                const selection = [...document.querySelectorAll(`[data-courrier-checkbox="${formId}"]:checked`)];
                if (selection.length === 0) {
                    alert('Sélectionnez au moins un courrier.');
                    return;
                }
                if (!confirm(`Confirmer la réception de ${selection.length} courrier(s) ?`)) {
                    return;
                }
                const form = document.createElement('form');
                form.method = 'POST';
                form.action = @json(route('courriers.receptionner'));
                form.style.display = 'none';
                const token = document.createElement('input');
                token.type = 'hidden';
                token.name = '_token';
                token.value = @json(csrf_token());
                form.appendChild(token);
                selection.forEach((checkbox) => {
                    const input = document.createElement('input');
                    input.type = 'hidden';
                    input.name = 'courrier_ids[]';
                    input.value = checkbox.value;
                    form.appendChild(input);
                });
                document.body.appendChild(form);
                form.submit();
            });
        });
    };

    bindBulk();

    const observer = new MutationObserver(bindBulk);
    if (root) {
        observer.observe(root, { childList: true, subtree: true });
    }

    if (!window.bootstrap) {
        return;
    }

    const bindModal = (modalId, formId, numeroId, triggerAttr, actionSuffix) => {
        const modalElement = document.getElementById(modalId);
        const form = document.getElementById(formId);
        const numero = document.getElementById(numeroId);
        if (!modalElement || !form || !numero) {
            return;
        }
        const modal = new bootstrap.Modal(modalElement);
        document.addEventListener('click', (event) => {
            const bouton = event.target.closest(`[${triggerAttr}]`);
            if (!bouton) return;
            form.action = `{{ url('/courriers') }}/${bouton.dataset.courrierId}/${actionSuffix}`;
            numero.textContent = bouton.dataset.courrierNumero;
            const motif = form.querySelector('textarea[name="motif"]');
            if (motif) motif.value = '';
            modal.show();
        });
    };

    bindModal('reaffecterModal', 'reaffecter-form', 'reaffecter-numero', 'data-reaffecter', 'reaffecter');
    bindModal('refusReceptionModal', 'refus-reception-form', 'refus-reception-numero', 'data-refus-reception', 'refuser-reception');
    bindModal('refusReaffectationModal', 'refus-reaffectation-form', 'refus-reaffectation-numero', 'data-refus-reaffectation', 'refuser-reaffectation');
});
</script>
@endpush
