<?php

namespace App\Http\Controllers;

use App\Enums\StatutCourrier;
use App\Services\CourrierPerimetreService;
use App\Services\DelegationContext;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __construct(
        private readonly DelegationContext $delegationContext,
        private readonly CourrierPerimetreService $perimetre,
    ) {}

    public function index(Request $request): View
    {
        $this->authorize('menu.dashboard');

        $user = $this->delegationContext->sujet();

        $queryBase = fn () => $this->perimetre->requeteTableauDeBord($user);

        $query = $queryBase();

        $filter = $request->input('filter');
        if ($filter === 'today') {
            $query->whereDate('date_depot', today());
        } elseif ($filter === 'month') {
            $query->whereMonth('date_depot', now()->month)
                ->whereYear('date_depot', now()->year);
        } elseif ($filter === 'entrants') {
            $query->whereIn('statut', CourrierPerimetreService::statutsEntrants());
        } elseif ($filter === 'sortants') {
            $query->whereIn('statut', CourrierPerimetreService::statutsSortants());
        } else {
            $filter = null;
        }

        $statut = $request->input('statut');
        $statutsValides = collect(StatutCourrier::cases())->map->value->all();
        if ($statut && in_array($statut, $statutsValides, true)) {
            $query->where('statut', $statut);
        } else {
            $statut = null;
        }

        $search = $request->input('search');
        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('numero', 'like', "%{$search}%")
                    ->orWhere('objet', 'like', "%{$search}%")
                    ->orWhereHas('contribuable', function ($cq) use ($search) {
                        $cq->where('nom_raison_sociale', 'like', "%{$search}%");
                    })
                    ->orWhereHas('categorie', function ($cq) use ($search) {
                        $cq->where('nom', 'like', "%{$search}%");
                    });
            });
        }

        $sort = $request->input('sort', 'recent');
        if ($sort === 'oldest') {
            $query->orderBy('date_depot', 'asc');
        } else {
            $query->orderBy('date_depot', 'desc');
        }

        $query->with(['contribuable', 'categorie', 'uniteActuelle', 'responsableActuel']);

        $courriers = $query->paginate(8)->appends([
            'search' => $search,
            'filter' => $filter,
            'statut' => $statut,
            'sort' => $sort,
        ]);

        $statsBase = $queryBase();
        $stats = [
            'total' => (clone $statsBase)->count(),
            'aujourdhui' => (clone $statsBase)->whereDate('date_depot', today())->count(),
            'mois' => (clone $statsBase)->whereMonth('date_depot', now()->month)->whereYear('date_depot', now()->year)->count(),
            'entrants' => (clone $statsBase)->whereIn('statut', CourrierPerimetreService::statutsEntrants())->count(),
            'sortants' => (clone $statsBase)->whereIn('statut', CourrierPerimetreService::statutsSortants())->count(),
        ];

        if ($request->ajax()) {
            return view('dashboard._table', compact('courriers'));
        }

        return view('dashboard.index', [
            'courriers' => $courriers,
            'search' => $search,
            'filter' => $filter,
            'statut' => $statut,
            'sort' => $sort,
            'stats' => $stats,
            'sujetCourant' => $user,
            'enRemplacement' => $this->delegationContext->enRemplacement(),
        ]);
    }
}
