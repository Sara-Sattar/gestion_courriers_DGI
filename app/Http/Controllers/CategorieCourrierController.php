<?php

namespace App\Http\Controllers;

use App\Http\Requests\CategorieCourrierRequest;
use App\Models\CategorieCourrier;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class CategorieCourrierController extends Controller
{
    public function index(Request $request): View
    {
        Gate::authorize('manage-categories');

        $recherche = trim((string) $request->query('recherche'));
        $statut = (string) $request->query('statut');

        $categories = CategorieCourrier::query()
            ->withCount(['typesCourrier', 'courriers'])
            ->when($recherche !== '', fn ($requete) => $requete->where(
                fn ($groupe) => $groupe
                    ->where('nom', 'like', '%'.$recherche.'%')
                    ->orWhere('description', 'like', '%'.$recherche.'%')
            ))
            ->when($statut === 'actifs', fn ($requete) => $requete->where('actif', true))
            ->when($statut === 'inactifs', fn ($requete) => $requete->where('actif', false))
            ->orderBy('nom')
            ->paginate(10)
            ->withQueryString();

        if ($request->ajax()) {
            return view('categories._table', compact('categories', 'recherche', 'statut'));
        }

        return view('categories.index', compact('categories', 'recherche', 'statut'));
    }

    public function create(): View
    {
        Gate::authorize('manage-categories');

        return view('categories.create');
    }

    public function store(CategorieCourrierRequest $request): RedirectResponse
    {
        $donnees = $request->validated();
        $donnees['actif'] = $request->boolean('actif');

        CategorieCourrier::create($donnees);

        return redirect()
            ->route('categories.index')
            ->with('success', 'Catégorie créée avec succès.');
    }

    public function edit(CategorieCourrier $categorie): View
    {
        Gate::authorize('manage-categories');

        return view('categories.edit', compact('categorie'));
    }

    public function update(CategorieCourrierRequest $request, CategorieCourrier $categorie): RedirectResponse
    {
        $donnees = $request->validated();
        $donnees['actif'] = $request->boolean('actif');

        $categorie->update($donnees);

        return redirect()
            ->route('categories.index')
            ->with('success', 'Catégorie mise à jour avec succès.');
    }

    public function destroy(CategorieCourrier $categorie): RedirectResponse
    {
        Gate::authorize('manage-categories');

        if ($categorie->courriers()->exists()) {
            return redirect()
                ->route('categories.index')
                ->with('error', 'Impossible de supprimer : des courriers utilisent cette catégorie. Désactivez-la plutôt.');
        }

        if ($categorie->typesCourrier()->exists()) {
            return redirect()
                ->route('categories.index')
                ->with('error', 'Impossible de supprimer : supprimez d\'abord les types de courrier rattachés.');
        }

        $categorie->delete();

        return redirect()
            ->route('categories.index')
            ->with('success', 'Catégorie supprimée.');
    }
}
