<?php

namespace App\Http\Controllers;

use App\Http\Requests\UserRequest;
use App\Models\BureauOrdre;
use App\Models\Permission;
use App\Models\Role;
use App\Models\Unite;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class UserController extends Controller
{
    public function index(Request $request): View
    {
        Gate::authorize('manage-users');

        $recherche = trim((string) $request->query('recherche'));

        $users = User::with(['role', 'unite'])
            ->when($recherche !== '', function ($q) use ($recherche) {
                $q->where(function ($groupe) use ($recherche) {
                    $groupe->where('name', 'like', '%'.$recherche.'%')
                        ->orWhere('prenom', 'like', '%'.$recherche.'%')
                        ->orWhere('email', 'like', '%'.$recherche.'%')
                        ->orWhere('ppr', 'like', '%'.$recherche.'%')
                        ->orWhereHas('role', fn ($r) => $r->where('name', 'like', '%'.$recherche.'%'))
                        ->orWhereHas('unite', fn ($u) => $u->where('nom', 'like', '%'.$recherche.'%')
                            ->orWhere('code', 'like', '%'.$recherche.'%'));
                });
            })
            ->orderBy('name')
            ->paginate(10)
            ->withQueryString();

        if ($request->ajax()) {
            return view('users._table', compact('users', 'recherche'));
        }

        return view('users.index', compact('users', 'recherche'));
    }

    public function create(): View
    {
        Gate::authorize('manage-users');

        return view('users.create', $this->formulaireDonnees());
    }

    public function store(UserRequest $request): RedirectResponse
    {
        Gate::authorize('manage-users');

        $validated = $request->validated();
        $permissionIds = $this->normaliserPermissions(
            $validated['permissions'] ?? [],
            (int) $validated['role_id']
        );
        $roleIds = $this->normaliserRoles(
            $validated['role_ids'] ?? [],
            (int) $validated['role_id']
        );
        unset($validated['permissions'], $validated['role_ids']);
        $validated['password'] = bcrypt($validated['password']);

        DB::transaction(function () use ($validated, $permissionIds, $roleIds) {
            $user = User::query()->create($validated);
            $user->permissions()->sync($permissionIds);
            $user->roles()->sync($roleIds);
        });

        return redirect()->route('users.index')->with('success', 'Utilisateur créé avec succès');
    }

    public function show(User $user): View
    {
        Gate::authorize('manage-users');

        $user->load(['role.permissions', 'roles', 'unite', 'permissions']);

        $permissionsEffectives = $user->permissions->isNotEmpty()
            ? $user->permissions
            : ($user->role?->permissions ?? collect());

        return view('users.show', compact('user', 'permissionsEffectives'));
    }

    public function edit(User $user): View
    {
        Gate::authorize('manage-users');

        $user->load(['role.permissions', 'roles', 'permissions']);

        return view('users.edit', array_merge(
            $this->formulaireDonnees($user),
            ['user' => $user]
        ));
    }

    public function update(UserRequest $request, User $user): RedirectResponse
    {
        Gate::authorize('manage-users');

        $validated = $request->validated();
        $permissionIds = $this->normaliserPermissions(
            $validated['permissions'] ?? [],
            (int) $validated['role_id']
        );
        $roleIds = $this->normaliserRoles(
            $validated['role_ids'] ?? [],
            (int) $validated['role_id']
        );
        unset($validated['permissions'], $validated['role_ids']);

        if (empty($validated['password'])) {
            unset($validated['password']);
        } else {
            $validated['password'] = bcrypt($validated['password']);
        }

        DB::transaction(function () use ($user, $validated, $permissionIds, $roleIds) {
            $user->update($validated);
            $user->permissions()->sync($permissionIds);
            $user->roles()->sync($roleIds);
        });

        return redirect()->route('users.index')->with('success', 'Utilisateur mis à jour avec succès');
    }

    public function destroy(User $user): RedirectResponse
    {
        Gate::authorize('manage-users');

        $user->delete();

        return redirect()->route('users.index')->with('success', 'Utilisateur supprimé avec succès');
    }

    /**
     * @return array{roles: \Illuminate\Support\Collection, unites: \Illuminate\Support\Collection, permissions: \Illuminate\Support\Collection, groupes: array, permissionIdsActives: list<int>}
     */
    private function formulaireDonnees(?User $utilisateur = null): array
    {
        $permissions = Permission::query()->orderBy('groupe')->orderBy('label')->get()->groupBy('groupe');
        $groupes = config('permissions.groupes', []);
        $permissionIdsActives = old('permissions')
            ? collect(old('permissions'))->map(fn ($id) => (int) $id)->all()
            : ($utilisateur?->permissionIdsEffectives() ?? []);

        return [
            'roles' => Role::query()->orderBy('name')->get(),
            'unites' => Unite::query()->orderBy('nom')->get(),
            'bureauxOrdre' => BureauOrdre::query()->actifs()->orderBy('libelle')->get(),
            'permissions' => $permissions,
            'groupes' => $groupes,
            'permissionIdsActives' => $permissionIdsActives,
        ];
    }

    /**
     * @param  list<int|string>  $permissionIds
     * @return list<int>
     */
    private function normaliserPermissions(array $permissionIds, int $roleId): array
    {
        $ids = collect($permissionIds)
            ->map(fn ($id) => (int) $id)
            ->unique()
            ->values()
            ->all();

        $role = Role::query()->find($roleId);

        if ($role?->name === 'Admin') {
            $obligatoire = Permission::query()->where('code', 'users.gerer')->value('id');
            if ($obligatoire && ! in_array((int) $obligatoire, $ids, true)) {
                $ids[] = (int) $obligatoire;
            }
        }

        // Responsables hiérarchiques : droits de délégation toujours présents.
        if (in_array($role?->name, ['Service', 'Brigade', 'Bureau', 'Section', 'Secteur'], true)) {
            $idsDelegation = Permission::query()
                ->where('code', 'like', 'delegations.%')
                ->pluck('id')
                ->map(fn ($id) => (int) $id)
                ->all();

            $ids = collect($ids)->merge($idsDelegation)->unique()->values()->all();
        }

        // Accueil : jamais réception / traitement / réaffectation.
        if ($role?->name === 'Accueil') {
            $idsInterdits = Permission::query()
                ->whereIn('code', [
                    'menu.reception',
                    'menu.traitement',
                    'courriers.receptionner',
                    'courriers.annuler_reception',
                    'courriers.reaffecter',
                    'courriers.refuser_reception',
                ])
                ->pluck('id')
                ->map(fn ($id) => (int) $id)
                ->all();

            $ids = array_values(array_filter(
                $ids,
                fn (int $id) => ! in_array($id, $idsInterdits, true)
            ));
        }

        return $ids;
    }

    /**
     * @param  list<int|string>  $roleIds
     * @return list<int>
     */
    private function normaliserRoles(array $roleIds, int $rolePrincipalId): array
    {
        $ids = collect($roleIds)
            ->map(fn ($id) => (int) $id)
            ->push($rolePrincipalId)
            ->filter()
            ->unique()
            ->values()
            ->all();

        return $ids;
    }
}
