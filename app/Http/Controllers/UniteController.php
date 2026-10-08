<?php

namespace App\Http\Controllers;

use App\Http\Requests\UniteRequest;
use App\Models\Role;
use App\Models\TypeCourrier;
use App\Models\Unite;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class UniteController extends Controller
{
    public function index(Request $request): View
    {
        Gate::authorize('manage-unites');

        $recherche = trim((string) $request->query('recherche'));
        $statut = (string) $request->query('statut');

        $unites = Unite::query()
            ->with('parent')
            ->withCount(['users', 'courriersDestines'])
            ->when($recherche !== '', fn ($requete) => $requete->where(
                fn ($groupe) => $groupe
                    ->where('code', 'like', '%'.$recherche.'%')
                    ->orWhere('nom', 'like', '%'.$recherche.'%')
                    ->orWhere('type_unite', 'like', '%'.$recherche.'%')
            ))
            ->when($statut === 'actifs', fn ($requete) => $requete->where('actif', true))
            ->when($statut === 'inactifs', fn ($requete) => $requete->where('actif', false))
            ->orderBy('type_unite')
            ->orderBy('nom')
            ->paginate(10)
            ->withQueryString();

        if ($request->ajax()) {
            return view('unites._table', compact('unites', 'recherche', 'statut'));
        }

        return view('unites.index', compact('unites', 'recherche', 'statut'));
    }

    public function create(): View
    {
        Gate::authorize('manage-unites');

        $typesUnite = Unite::typesDisponibles();
        $parents = Unite::parentsPossibles(old('type_unite'));

        return view('unites.create', compact('parents', 'typesUnite'));
    }

    public function store(UniteRequest $request): RedirectResponse
    {
        $donnees = $request->validated();
        $donnees['actif'] = $request->boolean('actif');
        // Le code suit la numérotation du type et n'est jamais saisi à la main.
        $donnees['code'] = Unite::prochainCode($donnees['type_unite']);

        Unite::create($donnees);

        return redirect()
            ->route('unites.index')
            ->with('success', 'Unité créée avec succès.');
    }

    public function edit(Unite $unite): View
    {
        Gate::authorize('manage-unites');

        $typesUnite = Unite::typesDisponibles();
        $parents = Unite::parentsPossibles(old('type_unite', $unite->type_unite), $unite);

        return view('unites.edit', compact('unite', 'parents', 'typesUnite'));
    }

    public function update(UniteRequest $request, Unite $unite): RedirectResponse
    {
        $donnees = $request->validated();
        $donnees['actif'] = $request->boolean('actif');

        $unite->update($donnees);

        return redirect()
            ->route('unites.index')
            ->with('success', 'Unité mise à jour avec succès.');
    }

    public function destroy(Unite $unite): RedirectResponse
    {
        Gate::authorize('manage-unites');

        if ($unite->courriersDestines()->exists() || $unite->courriersActuels()->exists()) {
            return redirect()
                ->route('unites.index')
                ->with('error', 'Impossible de supprimer : des courriers sont rattachés à cette unité. Désactivez-la plutôt.');
        }

        if ($unite->users()->exists()) {
            return redirect()
                ->route('unites.index')
                ->with('error', 'Impossible de supprimer : des utilisateurs sont affectés à cette unité.');
        }

        if ($unite->enfants()->exists()) {
            return redirect()
                ->route('unites.index')
                ->with('error', 'Impossible de supprimer : cette unité a des sous-unités.');
        }

        $unite->delete();

        return redirect()
            ->route('unites.index')
            ->with('success', 'Unité supprimée.');
    }

    /** Code qui sera attribué à la prochaine unité de ce type. */
    public function prochainCode(string $type): JsonResponse
    {
        Gate::authorize('manage-unites');

        return response()->json(['code' => Unite::prochainCode($type)]);
    }

    /** Unités pouvant servir de parent à une unité du type demandé. */
    public function parentsPossibles(Request $request, string $type): JsonResponse
    {
        Gate::authorize('manage-unites');

        $exclue = $request->filled('exclure')
            ? Unite::find($request->integer('exclure'))
            : null;

        $parents = Unite::parentsPossibles($type, $exclue)
            ->map(fn (Unite $parent) => [
                'id' => $parent->id,
                'nom' => $parent->nom,
                'code' => $parent->code,
                'type_unite' => $parent->type_unite,
            ]);

        return response()->json($parents);
    }

    // Mapping between Role names and Unite type_unite
    private function getTypeUniteForRole(string $roleName): ?string
    {
        $mapping = [
            'Accueil' => 'Bureau',
            'Service' => 'Service',
            'Brigade' => 'Brigade',
            'Bureau' => 'Bureau',
            'Section' => 'Section',
            'Secteur' => 'Secteur',
            'Admin' => null,
        ];

        return $mapping[$roleName] ?? null;
    }

    public function types(): JsonResponse
    {
        $types = Unite::query()
            ->actives()
            ->select('type_unite')
            ->distinct()
            ->orderBy('type_unite')
            ->pluck('type_unite');

        return response()->json($types);
    }

    public function parType(string $type): JsonResponse
    {
        $unites = Unite::query()
            ->actives()
            ->parType($type)
            ->orderBy('nom')
            ->get(['id', 'nom', 'code', 'type_unite']);

        return response()->json($unites);
    }

    public function parRole(int $roleId): JsonResponse
    {
        $role = Role::find($roleId);
        if (!$role) {
            return response()->json([]);
        }

        $typeUnite = $this->getTypeUniteForRole($role->name);

        // If typeUnite is null (like Admin or Substitut), return all active unites?
        if (!$typeUnite) {
            $unites = Unite::query()
                ->actives()
                ->orderBy('nom')
                ->get(['id', 'nom', 'code', 'type_unite']);
        } else {
            $unites = Unite::query()
                ->actives()
                ->parType($typeUnite)
                ->orderBy('nom')
                ->get(['id', 'nom', 'code', 'type_unite']);
        }

        return response()->json($unites);
    }

    public function typesCourrier(int $categorieId): JsonResponse
    {
        $types = TypeCourrier::query()
            ->actifs()
            ->where('categorie_courrier_id', $categorieId)
            ->orderBy('nom')
            ->get(['id', 'nom']);

        return response()->json($types);
    }
}
