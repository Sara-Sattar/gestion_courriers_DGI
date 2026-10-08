<div class="table-responsive">
    <table class="table table-hover table-referentiel mb-0">
        <thead class="table-light">
            <tr>
                <th>Délégant</th>
                <th>Délégué</th>
                <th>Début</th>
                <th>Fin</th>
                <th>Motif</th>
                <th>Créée par</th>
                <th class="text-center">Statut</th>
                <th class="text-end">Actions</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($delegations as $delegation)
                <tr>
                    <td class="fw-medium">
                        {{ $delegation->agentAbsent?->name }}
                        @if ($delegation->unite)
                            <span class="referentiel-precision d-block">{{ $delegation->unite->code }}</span>
                        @endif
                    </td>
                    <td>{{ $delegation->agentRemplacant?->name }}</td>
                    <td class="text-nowrap small">{{ $delegation->date_debut->format('d/m/Y') }}</td>
                    <td class="text-nowrap small">{{ $delegation->date_fin->format('d/m/Y') }}</td>
                    <td>{{ $delegation->motif->label() }}</td>
                    <td class="small">{{ $delegation->createur?->name ?? '—' }}</td>
                    <td class="text-center">
                        @php $badge = $delegation->statut->badgeClass(); @endphp
                        <span class="badge bg-{{ $badge }}-subtle text-{{ $badge }}-emphasis border border-{{ $badge }}-subtle">
                            {{ $delegation->statut->label() }}
                        </span>
                    </td>
                    <td class="text-end text-nowrap">
                        @can('delegations.modifier')
                            @if ($delegation->statut->estModifiable())
                                <a
                                    href="{{ route('delegations.edit', $delegation) }}"
                                    class="btn btn-sm btn-outline-secondary me-1"
                                    title="Modifier"
                                >
                                    <i class="bi bi-pencil"></i>
                                </a>
                            @endif
                        @endcan
                        @can('delegations.annuler')
                            @if ($delegation->statut->estAnnulable())
                                <form
                                    method="POST"
                                    action="{{ route('delegations.destroy', $delegation) }}"
                                    class="d-inline"
                                    onsubmit="return confirm('Annuler cette délégation ? Elle restera visible dans l’historique.');"
                                >
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-outline-danger" title="Annuler">
                                        <i class="bi bi-x-circle"></i>
                                    </button>
                                </form>
                            @endif
                        @endcan
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="8" class="text-center text-muted py-5">
                        Aucune délégation trouvée dans votre périmètre.
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>

@if ($delegations->hasPages())
    <div class="card-footer bg-white border-0">
        {{ $delegations->links() }}
    </div>
@endif
