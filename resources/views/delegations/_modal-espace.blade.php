{{-- Modal de choix d'espace après connexion --}}
@if (! empty($doitChoisirEspaceDelegation) && ($delegationsActivesRemplacant ?? collect())->isNotEmpty())
<div class="modal fade" id="modalEspaceDelegation" tabindex="-1" aria-labelledby="modalEspaceDelegationLabel" aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <form method="POST" action="{{ route('espace-travail.choisir') }}">
                @csrf
                <div class="modal-header border-0 pb-0">
                    <h5 class="modal-title fw-bold" id="modalEspaceDelegationLabel">
                        Bienvenue {{ Auth::user()->name }}.
                    </h5>
                </div>
                <div class="modal-body pt-2">
                    <p class="text-muted mb-3">
                        {{ $delegationsActivesRemplacant->count() === 1
                            ? 'Une délégation vous a été attribuée.'
                            : 'Des délégations vous ont été attribuées.' }}
                        Choisissez votre espace de travail :
                    </p>
                    <div class="espace-choix-list">
                        <label class="espace-choix-item">
                            <input type="radio" name="espace" value="self" checked>
                            <span>
                                <strong>Mon espace</strong>
                                <small class="d-block text-muted">Vos dossiers et tâches personnels</small>
                            </span>
                        </label>
                        @foreach ($delegationsActivesRemplacant as $delegation)
                            <label class="espace-choix-item">
                                <input type="radio" name="espace" value="{{ $delegation->id }}">
                                <span>
                                    <strong>{{ $delegation->libelleRemplacement() }}</strong>
                                    <small class="d-block text-muted">
                                        {{ $delegation->unite?->nom }}
                                        · {{ $delegation->date_debut->format('d/m/Y') }} → {{ $delegation->date_fin->format('d/m/Y') }}
                                    </small>
                                </span>
                            </label>
                        @endforeach
                    </div>
                </div>
                <div class="modal-footer border-0">
                    <button type="submit" class="btn btn-primary px-4">Continuer</button>
                </div>
            </form>
        </div>
    </div>
</div>
@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const modalEl = document.getElementById('modalEspaceDelegation');
    if (modalEl && window.bootstrap) {
        bootstrap.Modal.getOrCreateInstance(modalEl).show();
    }
});
</script>
@endpush
@endif
