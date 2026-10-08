<div class="table-responsive">
    <table class="table table-hover table-referentiel mb-0 align-middle">
        <thead class="table-light">
            <tr>
                <th scope="col">Utilisateur</th>
                <th scope="col">Rôle</th>
                <th scope="col">Unité</th>
                <th scope="col" class="text-end">Actions</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($users as $user)
                <tr>
                    <td>
                        <span class="referentiel-libelle">{{ $user->nomComplet() }}</span>
                        <span class="referentiel-precision">{{ $user->email }}</span>
                    </td>
                    <td>
                        <span class="badge bg-primary-subtle text-primary-emphasis border border-primary-subtle">
                            {{ $user->role?->name ?? '—' }}
                        </span>
                    </td>
                    <td>
                        @if ($user->unite)
                            <span class="referentiel-libelle">{{ $user->unite->nom }}</span>
                            <span class="referentiel-precision">{{ $user->unite->code }}</span>
                        @else
                            <span class="text-muted">—</span>
                        @endif
                    </td>
                    <td class="text-end text-nowrap">
                        <a href="{{ route('users.show', $user) }}" class="btn btn-sm btn-outline-secondary me-1" title="Voir">
                            <i class="bi bi-eye" aria-hidden="true"></i>
                        </a>
                        <a href="{{ route('users.edit', $user) }}" class="btn btn-sm btn-outline-primary me-1" title="Modifier">
                            <i class="bi bi-pencil" aria-hidden="true"></i>
                        </a>
                        <form action="{{ route('users.destroy', $user) }}" method="POST" class="d-inline" onsubmit="return confirm('Supprimer cet utilisateur ?')">
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
                    <td colspan="4" class="text-center text-muted py-5">
                        @if (($recherche ?? '') !== '')
                            Aucun utilisateur ne correspond à cette recherche.
                        @else
                            Aucun utilisateur enregistré.
                        @endif
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>

@if ($users->hasPages())
    <div class="card-footer bg-white border-0 d-flex justify-content-end py-3">
        {{ $users->links('pagination.defaut') }}
    </div>
@endif
