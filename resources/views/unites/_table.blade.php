<div class="table-responsive">
    <table class="table table-hover table-referentiel mb-0">
        <thead class="table-light">
            <tr>
                <th scope="col">Code</th>
                <th scope="col">Unité</th>
                <th scope="col">Type</th>
                <th scope="col" class="text-center">Agents</th>
                <th scope="col" class="text-center">Courriers</th>
                <th scope="col" class="text-center">Statut</th>
                <th scope="col" class="text-end">Actions</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($unites as $unite)
                <tr>
                    <td><code>{{ $unite->code }}</code></td>
                    <td>
                        <span class="referentiel-libelle">{{ $unite->nom }}</span>
                        @if ($unite->parent)
                            <span class="referentiel-precision">
                                <i class="bi bi-arrow-return-right me-1" aria-hidden="true"></i>rattachée à {{ $unite->parent->nom }}
                            </span>
                        @endif
                    </td>
                    <td><span class="badge bg-light text-dark border fw-normal">{{ $unite->type_unite }}</span></td>
                    <td class="text-center"><span class="referentiel-compteur">{{ $unite->users_count }}</span></td>
                    <td class="text-center"><span class="referentiel-compteur">{{ $unite->courriers_destines_count }}</span></td>
                    <td class="text-center">
                        <span class="badge bg-{{ $unite->actif ? 'success' : 'secondary' }}-subtle text-{{ $unite->actif ? 'success' : 'secondary' }}-emphasis border border-{{ $unite->actif ? 'success' : 'secondary' }}-subtle">
                            {{ $unite->actif ? 'Active' : 'Inactive' }}
                        </span>
                    </td>
                    <td class="text-end text-nowrap">
                        <a href="{{ route('unites.edit', $unite) }}" class="btn btn-sm btn-outline-secondary me-1" title="Modifier">
                            <i class="bi bi-pencil" aria-hidden="true"></i>
                        </a>
                        <form action="{{ route('unites.destroy', $unite) }}" method="POST" class="d-inline" onsubmit="return confirm('Supprimer définitivement l\'unité « {{ $unite->nom }} » ?')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-sm btn-outline-danger" title="Supprimer">
                                <i class="bi bi-trash" aria-hidden="true"></i>
                            </button>
                        </form>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="7">
                        <div class="referentiel-vide">
                            @if (($recherche ?? '') !== '' || ($statut ?? '') !== '')
                                <i class="bi bi-search" aria-hidden="true"></i>
                                <p class="mb-0">Aucune unité ne correspond à ces critères.</p>
                            @else
                                <i class="bi bi-diagram-3" aria-hidden="true"></i>
                                <p class="mb-3">Aucune unité n'est encore enregistrée.</p>
                                <a href="{{ route('unites.create') }}" class="btn btn-sm btn-primary">
                                    <i class="bi bi-plus-lg me-1"></i>Créer la première unité
                                </a>
                            @endif
                        </div>
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>

@if ($unites->total() > 0)
    <div class="card-footer bg-white d-flex flex-wrap justify-content-between align-items-center gap-2 py-3">
        <span class="text-muted small">
            {{ $unites->total() }} unité{{ $unites->total() > 1 ? 's' : '' }}
            @if ($unites->hasPages())
                · page {{ $unites->currentPage() }} sur {{ $unites->lastPage() }}
            @endif
        </span>
        {{ $unites->links('pagination.defaut') }}
    </div>
@endif
