<div class="table-responsive">
    <table class="table table-hover table-referentiel mb-0">
        <thead class="table-light">
            <tr>
                <th scope="col">Type de courrier</th>
                <th scope="col">Catégorie</th>
                <th scope="col" class="text-center">Courriers</th>
                <th scope="col" class="text-center">Statut</th>
                <th scope="col" class="text-end">Actions</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($types as $type)
                <tr>
                    <td>
                        <span class="referentiel-libelle">{{ $type->nom }}</span>
                        @if ($type->description)
                            <span class="referentiel-precision">{{ Str::limit($type->description, 80) }}</span>
                        @endif
                    </td>
                    <td>
                        @if ($type->categorie)
                            <span class="badge bg-light text-dark border fw-normal">{{ $type->categorie->nom }}</span>
                        @else
                            <span class="text-muted">—</span>
                        @endif
                    </td>
                    <td class="text-center"><span class="referentiel-compteur">{{ $type->courriers_count }}</span></td>
                    <td class="text-center">
                        <span class="badge bg-{{ $type->actif ? 'success' : 'secondary' }}-subtle text-{{ $type->actif ? 'success' : 'secondary' }}-emphasis border border-{{ $type->actif ? 'success' : 'secondary' }}-subtle">
                            {{ $type->actif ? 'Actif' : 'Inactif' }}
                        </span>
                    </td>
                    <td class="text-end text-nowrap">
                        <a href="{{ route('types-courrier.edit', $type) }}" class="btn btn-sm btn-outline-secondary me-1" title="Modifier">
                            <i class="bi bi-pencil" aria-hidden="true"></i>
                        </a>
                        <form action="{{ route('types-courrier.destroy', $type) }}" method="POST" class="d-inline" onsubmit="return confirm('Supprimer définitivement le type « {{ $type->nom }} » ?')">
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
                    <td colspan="5">
                        <div class="referentiel-vide">
                            @if (($recherche ?? '') !== '' || ($statut ?? '') !== '')
                                <i class="bi bi-search" aria-hidden="true"></i>
                                <p class="mb-0">Aucun type ne correspond à ces critères.</p>
                            @else
                                <i class="bi bi-list-ul" aria-hidden="true"></i>
                                <p class="mb-3">Aucun type de courrier n'est encore enregistré.</p>
                                <a href="{{ route('types-courrier.create') }}" class="btn btn-sm btn-primary">
                                    <i class="bi bi-plus-lg me-1"></i>Créer le premier type
                                </a>
                            @endif
                        </div>
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>

@if ($types->total() > 0)
    <div class="card-footer bg-white d-flex flex-wrap justify-content-between align-items-center gap-2 py-3">
        <span class="text-muted small">
            {{ $types->total() }} type{{ $types->total() > 1 ? 's' : '' }} de courrier
            @if ($types->hasPages())
                · page {{ $types->currentPage() }} sur {{ $types->lastPage() }}
            @endif
        </span>
        {{ $types->links('pagination.defaut') }}
    </div>
@endif
