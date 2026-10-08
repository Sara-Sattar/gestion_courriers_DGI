@php
    $uniteId = old('unite_id', $delegation?->unite_id);
    $absentId = old('agent_absent_id', $delegation?->agent_absent_id);
    $remplacantId = old('agent_remplacant_id', $delegation?->agent_remplacant_id);
    $dateDebut = old('date_debut', $delegation?->date_debut?->format('Y-m-d'));
    $dateFin = old('date_fin', $delegation?->date_fin?->format('Y-m-d'));
    $motifVal = old('motif', $delegation?->motif?->value);
    $remplacants = $remplacants ?? $agents ?? collect();
@endphp

<x-erreurs-formulaire />

@if (session('error'))
    <div class="alert alert-danger">{{ session('error') }}</div>
@endif

<form method="POST" action="{{ $action }}" id="form-delegation">
    @csrf
    @if ($method !== 'POST')
        @method($method)
    @endif

    <div class="row g-3">
        <div class="col-md-12">
            <label for="unite_id" class="form-label">Unité du délégant <x-required /></label>
            <select name="unite_id" id="unite_id" class="form-select @error('unite_id') is-invalid @enderror" required>
                <option value="">Sélectionner une unité de votre périmètre…</option>
                @foreach ($unites as $unite)
                    <option value="{{ $unite->id }}" @selected((string) $uniteId === (string) $unite->id)>
                        {{ $unite->nom }} ({{ $unite->code }})
                    </option>
                @endforeach
            </select>
            @error('unite_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>

        <div class="col-md-6">
            <label for="agent_absent_id" class="form-label">Délégant (utilisateur indisponible) <x-required /></label>
            <select name="agent_absent_id" id="agent_absent_id" class="form-select @error('agent_absent_id') is-invalid @enderror" required>
                <option value="">Sélectionner…</option>
                @foreach ($agents as $agent)
                    <option value="{{ $agent->id }}" @selected((string) $absentId === (string) $agent->id)>
                        {{ $agent->name }}
                    </option>
                @endforeach
            </select>
            @error('agent_absent_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>

        <div class="col-md-6">
            <label for="agent_remplacant_id" class="form-label">Délégué (remplaçant) <x-required /></label>
            <select name="agent_remplacant_id" id="agent_remplacant_id" class="form-select @error('agent_remplacant_id') is-invalid @enderror" required>
                <option value="">Sélectionner…</option>
                @foreach ($remplacants as $agent)
                    <option value="{{ $agent->id }}" @selected((string) $remplacantId === (string) $agent->id)>
                        {{ $agent->name }}
                        @if ($agent->unite)
                            — {{ $agent->unite->code }}
                        @endif
                    </option>
                @endforeach
            </select>
            @error('agent_remplacant_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
            <div class="form-text">Le délégué doit appartenir à votre périmètre hiérarchique.</div>
        </div>

        <div class="col-md-4">
            <label for="date_debut" class="form-label">Date de début <x-required /></label>
            <input type="date" name="date_debut" id="date_debut" value="{{ $dateDebut }}"
                   class="form-control @error('date_debut') is-invalid @enderror" required>
            @error('date_debut')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>

        <div class="col-md-4">
            <label for="date_fin" class="form-label">Date de fin <x-required /></label>
            <input type="date" name="date_fin" id="date_fin" value="{{ $dateFin }}"
                   class="form-control @error('date_fin') is-invalid @enderror" required>
            @error('date_fin')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>

        <div class="col-md-4">
            <label for="motif" class="form-label">Motif de l’indisponibilité <x-required /></label>
            <select name="motif" id="motif" class="form-select @error('motif') is-invalid @enderror" required>
                <option value="">Sélectionner…</option>
                @foreach ($motifs as $motif)
                    <option value="{{ $motif->value }}" @selected($motifVal === $motif->value)>
                        {{ $motif->label() }}
                    </option>
                @endforeach
            </select>
            @error('motif')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>
    </div>

    <div class="d-flex justify-content-between mt-4 pt-3 border-top">
        <a href="{{ route('delegations.index') }}" class="btn btn-outline-secondary">Retour</a>
        <button type="submit" class="btn btn-primary">
            <i class="bi bi-check-lg me-1"></i>
            {{ $delegation ? 'Enregistrer' : 'Créer la délégation' }}
        </button>
    </div>
</form>

@push('scripts')
<script>
(function () {
    const uniteSelect = document.getElementById('unite_id');
    const absentSelect = document.getElementById('agent_absent_id');
    const urlAgents = @json(route('delegations.agents'));
    const selectedAbsent = @json((string) ($absentId ?? ''));

    function remplirDelegants(agents, preserveAbsent) {
        absentSelect.innerHTML = '<option value="">Sélectionner…</option>' + agents.map(a =>
            `<option value="${a.id}">${a.name}</option>`
        ).join('');
        if (preserveAbsent) absentSelect.value = preserveAbsent;
    }

    async function chargerDelegants(uniteId, preserve = false) {
        if (!uniteId) {
            remplirDelegants([], '');
            return;
        }
        const response = await fetch(`${urlAgents}?unite_id=${uniteId}`, {
            headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
        });
        const agents = await response.json();
        remplirDelegants(agents, preserve ? selectedAbsent : '');
    }

    uniteSelect.addEventListener('change', () => chargerDelegants(uniteSelect.value, false));
    if (uniteSelect.value) {
        chargerDelegants(uniteSelect.value, true);
    }
})();
</script>
@endpush
