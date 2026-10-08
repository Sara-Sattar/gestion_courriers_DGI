<?php

namespace App\Http\Controllers;

use App\Http\Requests\TypeCourrierRequest;
use App\Models\CategorieCourrier;
use App\Models\TypeCourrier;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class TypeCourrierController extends Controller
{
    public function index(Request $request): View
    {
        Gate::authorize('manage-types-courrier');

        $recherche = trim((string) $request->query('recherche'));
        $statut = (string) $request->query('statut');

        $types = TypeCourrier::query()
            ->with('categorie')
            ->withCount('courriers')
            ->when($recherche !== '', fn ($requete) => $requete->where(
                fn ($groupe) => $groupe
                    ->where('nom', 'like', '%'.$recherche.'%')
                    ->orWhere('description', 'like', '%'.$recherche.'%')
                    ->orWhereHas('categorie', fn ($categorie) => $categorie->where('nom', 'like', '%'.$recherche.'%'))
            ))
            ->when($statut === 'actifs', fn ($requete) => $requete->where('actif', true))
            ->when($statut === 'inactifs', fn ($requete) => $requete->where('actif', false))
            ->orderBy('categorie_courrier_id')
            ->orderBy('nom')
            ->paginate(10)
            ->withQueryString();

        if ($request->ajax()) {
            return view('types-courrier._table', compact('types', 'recherche', 'statut'));
        }

        return view('types-courrier.index', compact('types', 'recherche', 'statut'));
    }

    public function create(): View
    {
        Gate::authorize('manage-types-courrier');

        $categories = CategorieCourrier::query()->orderBy('nom')->get();

        return view('types-courrier.create', compact('categories'));
    }

    public function store(TypeCourrierRequest $request): RedirectResponse
    {
        $donnees = $request->validated();
        $donnees['actif'] = $request->boolean('actif');

        TypeCourrier::create($donnees);

        return redirect()
            ->route('types-courrier.index')
            ->with('success', 'Type de courrier créé avec succès.');
    }

    public function edit(TypeCourrier $typesCourrier): View
    {
        Gate::authorize('manage-types-courrier');

        $categories = CategorieCourrier::query()->orderBy('nom')->get();
        $type = $typesCourrier;

        return view('types-courrier.edit', compact('type', 'categories'));
    }

    public function update(TypeCourrierRequest $request, TypeCourrier $typesCourrier): RedirectResponse
    {
        $donnees = $request->validated();
        $donnees['actif'] = $request->boolean('actif');

        $typesCourrier->update($donnees);

        return redirect()
            ->route('types-courrier.index')
            ->with('success', 'Type de courrier mis à jour avec succès.');
    }

    public function destroy(TypeCourrier $typesCourrier): RedirectResponse
    {
        Gate::authorize('manage-types-courrier');

        if ($typesCourrier->courriers()->exists()) {
            return redirect()
                ->route('types-courrier.index')
                ->with('error', 'Impossible de supprimer : des courriers utilisent ce type. Désactivez-le plutôt.');
        }

        $typesCourrier->delete();

        return redirect()
            ->route('types-courrier.index')
            ->with('success', 'Type de courrier supprimé.');
    }
}
