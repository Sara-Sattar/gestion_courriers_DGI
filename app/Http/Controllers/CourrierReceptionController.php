<?php

namespace App\Http\Controllers;

use App\Enums\ActionType;
use App\Enums\StatutCourrier;
use App\Http\Requests\AnnulerReceptionRequest;
use App\Http\Requests\ReaffecterCourrierRequest;
use App\Http\Requests\ReceptionnerCourrierRequest;
use App\Http\Requests\RefuserReceptionRequest;
use App\Http\Requests\RefuserReaffectationRequest;
use App\Models\Courrier;
use App\Models\HistoriqueAffectation;
use App\Services\CourrierReceptionService;
use App\Services\DelegationContext;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use InvalidArgumentException;

class CourrierReceptionController extends Controller
{
    public function __construct(
        private readonly CourrierReceptionService $receptionService,
        private readonly DelegationContext $delegationContext,
    ) {}

    public function index(Request $request): View
    {
        $this->authorize('menu.reception');

        $today = now()->toDateString();
        $sujet = $this->delegationContext->sujet();
        $acteur = $this->delegationContext->acteur();
        $uniteId = $sujet?->unite_id;
        $dateDebut = $request->filled('date_debut') ? $request->date('date_debut')?->toDateString() : null;
        $dateFin = $request->filled('date_fin') ? $request->date('date_fin')?->toDateString() : null;
        $filtreNumero = $request->filled('numero') ? trim((string) $request->input('numero')) : null;
        $relations = [
            'contribuable',
            'categorie',
            'typeCourrier',
            'uniteDestinataire',
            'uniteActuelle',
            'responsableReception',
        ];

        $porteeUnite = function ($query) use ($sujet, $uniteId) {
            $query->where(function ($q) use ($sujet, $uniteId) {
                if ($uniteId) {
                    $q->where('unite_actuelle_id', $uniteId);
                }
                if ($sujet) {
                    $q->orWhere('responsable_actuel_id', $sujet->id);
                }
            });
        };

        $enAttente = Courrier::with($relations)
            ->withMax([
                'historiqueAffectations as derniere_reaffectation_at' => fn ($q) => $q
                    ->where('action_type', ActionType::Reaffectation),
            ], 'created_at')
            ->where($porteeUnite)
            ->whereIn('statut', [StatutCourrier::Depose, StatutCourrier::Reaffecte])
            ->when($filtreNumero, fn ($q) => $q->where('numero', 'like', '%'.$filtreNumero.'%'))
            ->orderByDesc('date_depot')
            ->get();

        if ($dateDebut || $dateFin) {
            $enAttente = $enAttente->filter(function (Courrier $courrier) use ($dateDebut, $dateFin) {
                $jour = $courrier->dateArriveeUnite()->toDateString();

                if ($dateDebut && $jour < $dateDebut) {
                    return false;
                }

                if ($dateFin && $jour > $dateFin) {
                    return false;
                }

                return true;
            })->values();

            $courriersAujourdhui = collect();
            $courriersEnInstance = $enAttente
                ->sortBy(fn (Courrier $courrier) => $courrier->dateArriveeUnite())
                ->values();
        } else {
            $courriersAujourdhui = $enAttente
                ->filter(fn (Courrier $courrier) => $courrier->dateArriveeUnite()->toDateString() === $today)
                ->values();

            $courriersEnInstance = $enAttente
                ->filter(fn (Courrier $courrier) => $courrier->dateArriveeUnite()->toDateString() < $today)
                ->sortBy(fn (Courrier $courrier) => $courrier->dateArriveeUnite())
                ->values();
        }

        $porteeHistorique = function ($query) use ($acteur, $sujet) {
            $query->where(function ($q) use ($acteur, $sujet) {
                if ($this->delegationContext->enRemplacement() && $sujet) {
                    // Mode remplacement : uniquement les actions pour le compte de l'absent
                    // (ou prises en charge au nom de l'absent).
                    $q->where('pour_compte_de_id', $sujet->id)
                        ->orWhere('nouveau_responsable_id', $sujet->id)
                        ->orWhere(function ($perso) use ($sujet) {
                            $perso->whereNull('pour_compte_de_id')
                                ->where(function ($inner) use ($sujet) {
                                    $inner->where('user_id', $sujet->id)
                                        ->orWhere('nouveau_responsable_id', $sujet->id);
                                });
                        });
                } elseif ($acteur) {
                    // Mon espace : actions personnelles de l'agent connecté (hors délégation).
                    $q->where('user_id', $acteur->id)
                        ->whereNull('pour_compte_de_id');
                }
            });
        };

        $historiquesReceptions = HistoriqueAffectation::with([
            'courrier',
            'user',
            'pourCompteDe',
            'uniteDestination',
        ])
            ->whereIn('action_type', [ActionType::Reception, ActionType::AnnulationReception])
            ->where($porteeHistorique)
            ->orderByDesc('created_at')
            ->paginate(8, ['*'], 'receptions_page');

        $historiquesReaffectations = HistoriqueAffectation::with([
            'courrier',
            'user',
            'pourCompteDe',
            'uniteSource',
            'uniteDestination',
        ])
            ->where('action_type', ActionType::Reaffectation)
            ->where($porteeHistorique)
            ->orderByDesc('created_at')
            ->paginate(8, ['*'], 'reaffectations_page');

        $allowedUnites = $this->receptionService->unitesReaffectationAutorisees($sujet ?? $acteur);

        if ($request->ajax()) {
            return view('courriers._reception-liste', [
                'courriersAujourdhui' => $courriersAujourdhui,
                'courriersEnInstance' => $courriersEnInstance,
            ]);
        }

        return view('courriers.reception', [
            'courriersAujourdhui' => $courriersAujourdhui,
            'courriersEnInstance' => $courriersEnInstance,
            'historiquesReceptions' => $historiquesReceptions,
            'historiquesReaffectations' => $historiquesReaffectations,
            'allowedUnites' => $allowedUnites,
            'utilisateurCourant' => $acteur,
            'sujetCourant' => $sujet,
            'enRemplacement' => $this->delegationContext->enRemplacement(),
            'filtreDateDebut' => $dateDebut,
            'filtreDateFin' => $dateFin,
            'filtreNumero' => $filtreNumero,
            'filtreActif' => (bool) ($dateDebut || $dateFin || $filtreNumero),
        ]);
    }

    public function receptionner(ReceptionnerCourrierRequest $request): RedirectResponse
    {
        $this->authorize('courriers.receptionner');

        $acteur = $this->delegationContext->acteur();
        $sujet = $this->delegationContext->sujet();
        $ids = $request->validated('courrier_ids');

        foreach ($ids as $id) {
            $this->authorize('update', Courrier::findOrFail($id));
        }

        try {
            $confirmes = $this->receptionService->confirmerReceptions($ids, $acteur, $sujet);
        } catch (InvalidArgumentException $e) {
            return back()->with('error', $e->getMessage());
        }

        if ($confirmes === 0) {
            return back()->with('error', 'Aucun courrier éligible n\'a pu être réceptionné.');
        }

        return redirect()
            ->route('courriers.reception.index')
            ->with('success', $confirmes === 1
                ? 'La réception du courrier a été confirmée.'
                : "{$confirmes} courriers ont été réceptionnés.");
    }

    public function confirmerReception(Courrier $courrier): RedirectResponse
    {
        $this->authorize('courriers.receptionner');
        $this->authorize('update', $courrier);

        try {
            $this->receptionService->confirmerReception(
                $courrier,
                $this->delegationContext->acteur(),
                $this->delegationContext->sujet(),
            );
        } catch (InvalidArgumentException $e) {
            return back()->with('error', $e->getMessage());
        }

        return redirect()
            ->route('courriers.reception.index')
            ->with('success', 'La réception du courrier a été confirmée.');
    }

    public function annulerReception(Courrier $courrier, AnnulerReceptionRequest $request): RedirectResponse
    {
        $this->authorize('courriers.annuler_reception');
        $this->authorize('update', $courrier);

        try {
            $this->receptionService->annulerReception(
                $courrier,
                $this->delegationContext->acteur(),
                $this->delegationContext->sujet(),
            );
        } catch (InvalidArgumentException $e) {
            return back()->with('error', $e->getMessage());
        }

        return back()->with('success', 'La réception a été annulée. Le courrier est de nouveau au statut Déposé.');
    }

    public function refuserReception(Courrier $courrier, RefuserReceptionRequest $request): RedirectResponse
    {
        $this->authorize('courriers.refuser_reception');
        $this->authorize('update', $courrier);

        try {
            $this->receptionService->refuserReception(
                $courrier,
                $this->delegationContext->acteur(),
                $request->validated('motif'),
                $this->delegationContext->sujet(),
            );
        } catch (InvalidArgumentException $e) {
            return back()->with('error', $e->getMessage());
        }

        return redirect()
            ->route('courriers.reception.index')
            ->with('success', 'Réception refusée. Le courrier est retourné au Bureau d\'ordre.');
    }

    public function refuserReaffectation(Courrier $courrier, RefuserReaffectationRequest $request): RedirectResponse
    {
        $this->authorize('courriers.refuser_reception');
        $this->authorize('update', $courrier);

        try {
            $this->receptionService->refuserReaffectation(
                $courrier,
                $this->delegationContext->acteur(),
                $request->validated('motif'),
                $this->delegationContext->sujet(),
            );
        } catch (InvalidArgumentException $e) {
            return back()->with('error', $e->getMessage());
        }

        return redirect()
            ->route('courriers.reception.index')
            ->with('success', 'Réaffectation refusée. Le courrier est retourné à l\'unité précédente (n−1).');
    }

    public function reaffecter(Courrier $courrier, ReaffecterCourrierRequest $request): RedirectResponse
    {
        $this->authorize('courriers.reaffecter');
        $this->authorize('update', $courrier);
        $validated = $request->validated();

        try {
            $this->receptionService->reaffecter(
                $courrier,
                $this->delegationContext->acteur(),
                (int) $validated['unite_destination_id'],
                $validated['motif'],
                $this->delegationContext->sujet(),
            );
        } catch (InvalidArgumentException $e) {
            return back()->with('error', $e->getMessage());
        }

        return back()->with('success', 'Le courrier a été réaffecté. Le destinataire doit accepter ou refuser la réaffectation.');
    }
}
