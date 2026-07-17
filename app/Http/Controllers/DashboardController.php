<?php

namespace App\Http\Controllers;

use App\Models\Courrier;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        $user = Auth::user();
        $unite = $user->unite;
        $hierarchyIds = $unite ? $unite->hierarchy_ids : [];

        $query = Courrier::query();
        if (! empty($hierarchyIds)) {
            $query->whereIn('unite_actuelle_id', $hierarchyIds);
        }

        $stats = [
            'total' => $query->count(),
            'aujourdhui' => (clone $query)->whereDate('date_depot', today())->count(),
            'mois' => (clone $query)->whereMonth('date_depot', now()->month)
                ->whereYear('date_depot', now()->year)
                ->count(),
        ];

        $derniersCourriers = $query->with(['contribuable', 'categorie', 'uniteActuelle'])
            ->latest('date_depot')
            ->limit(5)
            ->get();

        return view('dashboard.index', compact('stats', 'derniersCourriers'));
    }
}
