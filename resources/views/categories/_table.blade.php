<div class="table-responsive">
    <table class="table table-hover table-referentiel mb-0">
        <thead class="table-light">
            <tr>
                <th scope="col">Catégorie</th>
                <th scope="col" class="text-center">Types</th>
                <th scope="col" class="text-center">Courriers</th>
                <th scope="col" class="text-center">Réponse requise</th>
                <th scope="col" class="text-center">Statut</th>
                <th scope="col" class="text-end">Actions</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($categories as $categorie)
                <tr>
                    <td>
                        <span class="referentiel-libelle">{{ $categorie->nom }}</span>
                        @if ($categorie->description)
                            <span class="referentiel-precision">{{ Str::limit($categorie->description, 80) }}</span>
                        @endif
                    </td>
                    <td class="text-center">
                        @if ($categorie->types_courrier_count > 0)
                            <a
                                href="{{ route('types-courrier.index', ['recherche' => $categorie->nom]) }}"
                                class="referentiel-compteur text-decoration-none"
                                title="Voir les types de cette catégorie"
                            >{{ $categorie->types_courrier_count }}</a>
                        @else
                            <span class="referentiel-compteur">0</span>
                        @endif
                    </td>
                    <td class="text-center">
                        <span class="referentiel-compteur">{{ $categorie->courriers_count }}</span>
                    </td>
                    <td class="text-center">{{ $categorie->necessite_reponse ? 'Oui' : 'Non' }}</td>
                    <td class="text-center">
                        <span class="badge bg-{{ $categorie->actif ? 'success' : 'secondary' }}-subtle text-{{ $categorie->actif ? 'success' : 'secondary' }}-emphasis border border-{{ $categorie->actif ? 'success' : 'secondary' }}-subtle">
                            {{ $categorie->actif ? 'Active' : 'Inactive' }}
                        </span>
                    </td>
                    <td class="text-end text-nowrap">
                        <a href="{{ route('categories.edit', $categorie) }}" class="btn btn-sm btn-outline-secondary me-1" title="Modifier">
                            <i class="bi bi-pencil" aria-hidden="true"></i>
                        </a>
                        <form action="{{ route('categories.destroy', $categorie) }}" method="POST" class="d-inline" onsubmit="return confirm('Supprimer définitivement la catégorie « {{ $categorie->nom }} » ?')">
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
                    <td colspan="6">
                        <div class="referentiel-vide">
                            @if (($recherche ?? '') !== '' || ($statut ?? '') !== '')
                                <i class="bi bi-search" aria-hidden="true"></i>
                                <p class="mb-0">Aucune catégorie ne correspond à ces critères.</p>
                            @else
                                <i class="bi bi-tags" aria-hidden="true"></i>
                                <p class="mb-3">Aucune catégorie n'est encore enregistrée.</p>
                                <a href="{{ route('categories.create') }}" class="btn btn-sm btn-primary">
                                    <i class="bi bi-plus-lg me-1"></i>Créer la première catégorie
                                </a>
                            @endif
                        </div>
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>

@if ($categories->total() > 0)
    <div class="card-footer bg-white d-flex flex-wrap justify-content-between align-items-center gap-2 py-3">
        <span class="text-muted small">
            {{ $categories->total() }} catégorie{{ $categories->total() > 1 ? 's' : '' }}
            @if ($categories->hasPages())
                · page {{ $categories->currentPage() }} sur {{ $categories->lastPage() }}
            @endif
        </span>
        {{ $categories->links('pagination.defaut') }}
    </div>
@endif
