<?php

namespace App\Http\Controllers;

use App\Enums\MotifDelegation;
use App\Enums\StatutDelegation;
use App\Http\Requests\DelegationRequest;
use App\Models\Delegation;
use App\Services\DelegationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use InvalidArgumentException;

class DelegationController extends Controller
{
    public function __construct(
        private readonly DelegationService $delegationService,
    ) {}

    public function index(Request $request): View
    {
        $this->authorize('delegations.voir');

        $user = $request->user();
        $recherche = trim((string) $request->query('recherche'));
        $statut = (string) $request->query('statut');
        $uniteIds = $this->delegationService->unitesDansPerimetre($user)->pluck('id');

        $this->delegationService->synchroniserStatuts();

        $delegations = Delegation::query()
            ->with(['agentAbsent', 'agentRemplacant', 'unite', 'createur'])
            ->whereIn('unite_id', $uniteIds->isEmpty() ? [-1] : $uniteIds)
            ->when($recherche !== '', function ($q) use ($recherche) {
                $q->where(function ($groupe) use ($recherche) {
                    $groupe->whereHas('agentAbsent', fn ($u) => $u->where('name', 'like', '%'.$recherche.'%'))
                        ->orWhereHas('agentRemplacant', fn ($u) => $u->where('name', 'like', '%'.$recherche.'%'))
                        ->orWhereHas('createur', fn ($u) => $u->where('name', 'like', '%'.$recherche.'%'));
                });
            })
            ->when($statut !== '' && in_array($statut, array_column(StatutDelegation::cases(), 'value'), true),
                fn ($q) => $q->where('statut', $statut))
            ->orderByDesc('date_debut')
            ->paginate(10)
            ->withQueryString();

        if ($request->ajax()) {
            return view('delegations._table', compact('delegations'));
        }

        return view('delegations.index', compact('delegations', 'recherche', 'statut'));
    }

    public function create(): View
    {
        $this->authorize('delegations.creer');

        $user = auth()->guard('web')->user();
        $unites = $this->delegationService->unitesDansPerimetre($user);
        $motifs = MotifDelegation::cases();
        $agents = collect();
        $remplacants = $this->delegationService->agentsDuPerimetre($user);

        return view('delegations.create', compact('unites', 'motifs', 'agents', 'remplacants'));
    }

    public function store(DelegationRequest $request): RedirectResponse
    {
        $this->authorize('delegations.creer');

        try {
            $this->delegationService->creer($request->validated(), $request->user());
        } catch (InvalidArgumentException $e) {
            return back()->withInput()->with('error', $e->getMessage());
        }

        return redirect()
            ->route('delegations.index')
            ->with('success', 'Délégation créée avec succès.');
    }

    public function edit(Delegation $delegation): View
    {
        $this->authorize('delegations.modifier');

        $user = auth()->guard('web')->user();
        abort_unless($this->delegationService->peutGererDelegation($user, $delegation), 403);
        abort_unless($delegation->statut->estModifiable(), 403, 'Seule une délégation planifiée peut être modifiée.');

        $unites = $this->delegationService->unitesDansPerimetre($user);
        $motifs = MotifDelegation::cases();
        $agents = $this->delegationService->agentsDuPerimetre($user, $delegation->unite_id);
        $remplacants = $this->delegationService->agentsDuPerimetre($user);

        return view('delegations.edit', compact('delegation', 'unites', 'motifs', 'agents', 'remplacants'));
    }

    public function update(DelegationRequest $request, Delegation $delegation): RedirectResponse
    {
        $this->authorize('delegations.modifier');

        try {
            $this->delegationService->mettreAJour($delegation, $request->validated(), $request->user());
        } catch (InvalidArgumentException $e) {
            return back()->withInput()->with('error', $e->getMessage());
        }

        return redirect()
            ->route('delegations.index')
            ->with('success', 'Délégation mise à jour avec succès.');
    }

    public function destroy(Delegation $delegation): RedirectResponse
    {
        $this->authorize('delegations.annuler');

        try {
            $this->delegationService->annuler($delegation, auth()->guard('web')->user());
        } catch (InvalidArgumentException $e) {
            return back()->with('error', $e->getMessage());
        }

        return redirect()
            ->route('delegations.index')
            ->with('success', 'Délégation annulée.');
    }

    public function agents(Request $request): JsonResponse
    {
        abort_unless(
            $request->user()?->can('delegations.creer') || $request->user()?->can('delegations.modifier'),
            403
        );

        $uniteId = (int) $request->query('unite_id');
        $agents = $this->delegationService->agentsDuPerimetre($request->user(), $uniteId ?: null);

        return response()->json($agents->map(fn ($u) => [
            'id' => $u->id,
            'name' => $u->name,
            'email' => $u->email,
            'unite_id' => $u->unite_id,
        ])->values());
    }
}
