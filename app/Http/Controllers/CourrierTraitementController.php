<?php

namespace App\Http\Controllers;

use App\Enums\StatutCourrier;
use App\Http\Requests\DelivrerCourrierRequest;
use App\Http\Requests\TraiterCourrierRequest;
use App\Models\Courrier;
use App\Services\CourrierDelivranceService;
use App\Services\CourrierTraitementService;
use App\Services\DelegationContext;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use InvalidArgumentException;

class CourrierTraitementController extends Controller
{
    public function __construct(
        private readonly CourrierTraitementService $traitementService,
        private readonly CourrierDelivranceService $delivranceService,
        private readonly DelegationContext $delegationContext,
    ) {}

    public function index(Request $request): View
    {
        $this->authorize('menu.traitement');

        $sujet = $this->delegationContext->sujet();
        $filtreNumero = $request->filled('numero') ? trim((string) $request->input('numero')) : null;
        $dateDebut = $request->filled('date_debut') ? $request->date('date_debut')?->toDateString() : null;
        $dateFin = $request->filled('date_fin') ? $request->date('date_fin')?->toDateString() : null;

        $courriers = Courrier::with(['contribuable', 'utilisateur', 'categorie', 'typeCourrier', 'uniteActuelle'])
            ->whereIn('statut', StatutCourrier::pourTraitement())
            ->where('responsable_actuel_id', $sujet?->id)
            ->when($filtreNumero, fn ($q) => $q->where('numero', 'like', '%'.$filtreNumero.'%'))
            ->when($dateDebut, fn ($q) => $q->whereDate('date_reception', '>=', $dateDebut))
            ->when($dateFin, fn ($q) => $q->whereDate('date_reception', '<=', $dateFin))
            ->orderByDesc('date_reception')
            ->get();

        $enRemplacement = $this->delegationContext->enRemplacement();

        if ($request->ajax()) {
            return view('courriers._traitement-table', [
                'courriers' => $courriers,
                'sujetCourant' => $sujet,
                'enRemplacement' => $enRemplacement,
            ]);
        }

        // Historique personnel : réutilise traite_par_id + CourrierHistorique (pas de 2ᵉ système).
        $historiquesTraitements = Courrier::with([
            'contribuable',
            'typeCourrier',
            'uniteDestinataire',
            'uniteActuelle',
        ])
            ->where('traite_par_id', $sujet?->id)
            ->whereIn('statut', StatutCourrier::pourHistoriqueTraitement())
            ->orderByDesc('date_traitement')
            ->orderByDesc('id')
            ->paginate(10, ['*'], 'historique_page');

        return view('courriers.traitement', [
            'courriers' => $courriers,
            'historiquesTraitements' => $historiquesTraitements,
            'sujetCourant' => $sujet,
            'enRemplacement' => $enRemplacement,
            'filtreNumero' => $filtreNumero,
            'filtreDateDebut' => $dateDebut,
            'filtreDateFin' => $dateFin,
        ]);
    }

    public function show(Courrier $courrier): View
    {
        $this->authorize('menu.traitement');
        $this->authorize('view', $courrier);

        $courrier->load([
            'contribuable',
            'categorie',
            'typeCourrier',
            'uniteActuelle',
            'responsableActuel',
            'traitePar',
            'piecesJointes',
            'suiviDossier.utilisateur',
            'suiviDossier.pourCompteDe',
        ]);

        $sujet = $this->delegationContext->sujet();
        $estResponsable = $courrier->responsable_actuel_id === $sujet?->id;
        $peutTraiter = in_array($courrier->statut, StatutCourrier::traitablesActivement(), true) && $estResponsable;
        $peutReprendre = $courrier->statut === StatutCourrier::Suspendu && $estResponsable;
        $peutReaffecter = $peutTraiter && ($sujet?->hasPermission('courriers.reaffecter') ?? false);
        $allowedUnites = $peutReaffecter
            ? app(\App\Services\CourrierReceptionService::class)->unitesReaffectationAutorisees($sujet)
            : collect();

        return view('courriers.show-traitement', [
            'courrier' => $courrier,
            'peutTraiter' => $peutTraiter,
            'peutReprendre' => $peutReprendre,
            'peutReaffecter' => $peutReaffecter,
            'allowedUnites' => $allowedUnites,
            'enRemplacement' => $this->delegationContext->enRemplacement(),
            'sujetCourant' => $sujet,
        ]);
    }

    public function store(TraiterCourrierRequest $request, Courrier $courrier): RedirectResponse
    {
        $this->authorize('menu.traitement');
        $this->authorize('update', $courrier);

        try {
            $this->traitementService->traiter(
                $courrier,
                $this->delegationContext->acteur(),
                $request->validated(),
                $this->delegationContext->sujet(),
            );
        } catch (InvalidArgumentException $e) {
            return back()->withInput()->with('error', $e->getMessage());
        }

        $message = match ($request->validated('resultat')) {
            'favorable' => 'Traitement favorable enregistré. Le dossier est transmis à l\'Accueil (Documents à délivrer).',
            'rejet' => 'Rejet enregistré. Le dossier quitte votre file active et est transmis à l\'Accueil. Il reste consultable dans « Historique de mes traitements ».',
            'suspendu' => 'Le dossier a été suspendu. Il reste dans votre file de traitement — utilisez « Reprendre le traitement » pour le poursuivre.',
            default => 'Le traitement a été enregistré.',
        };

        return redirect()
            ->route('courriers.traitement.index')
            ->with('success', $message);
    }

    public function reprendre(Courrier $courrier): RedirectResponse
    {
        $this->authorize('menu.traitement');
        $this->authorize('update', $courrier);

        try {
            $this->traitementService->reprendre(
                $courrier,
                $this->delegationContext->acteur(),
                $this->delegationContext->sujet(),
            );
        } catch (InvalidArgumentException $e) {
            return back()->with('error', $e->getMessage());
        }

        return redirect()
            ->route('courriers.traitement.show', $courrier)
            ->with('success', 'Traitement repris. Vous pouvez maintenant valider un résultat favorable ou un rejet.');
    }

    public function indexDelivrance(\Illuminate\Http\Request $request): View
    {
        $this->authorize('menu.delivrance');

        $sujet = $this->delegationContext->sujet();
        $uniteId = $sujet?->unite_id;

        $courriers = Courrier::with(['contribuable', 'categorie', 'typeCourrier', 'traitePar', 'uniteActuelle'])
            ->where('statut', StatutCourrier::TraiteFavorable)
            ->when($uniteId, fn ($q) => $q->where('unite_actuelle_id', $uniteId))
            ->when($request->filled('numero'), function ($q) use ($request) {
                $q->where('numero', 'like', '%'.$request->string('numero')->trim().'%');
            })
            ->when($request->filled('annee'), function ($q) use ($request) {
                $q->where('annee_enregistrement', (int) $request->input('annee'));
            })
            ->orderByDesc('date_traitement')
            ->get();

        if ($request->ajax()) {
            return view('courriers._delivrance-table', compact('courriers'));
        }

        return view('courriers.delivrance', [
            'courriers' => $courriers,
            'sujetCourant' => $sujet,
            'enRemplacement' => $this->delegationContext->enRemplacement(),
            'filtreNumero' => $request->input('numero'),
            'filtreAnnee' => $request->input('annee'),
        ]);
    }

    public function showDelivrance(Courrier $courrier): View
    {
        $this->authorize('menu.delivrance');
        $this->authorize('view', $courrier);

        $courrier->load([
            'contribuable',
            'categorie',
            'typeCourrier',
            'uniteActuelle',
            'traitePar',
            'piecesJointes',
            'suiviDossier.utilisateur',
            'suiviDossier.pourCompteDe',
        ]);

        return view('courriers.show-delivrance', [
            'courrier' => $courrier,
            'sujetCourant' => $this->delegationContext->sujet(),
            'enRemplacement' => $this->delegationContext->enRemplacement(),
        ]);
    }

    public function delivrer(DelivrerCourrierRequest $request, Courrier $courrier): RedirectResponse
    {
        $this->authorize('courriers.delivrer');
        $this->authorize('update', $courrier);

        try {
            $this->delivranceService->delivrer(
                $courrier,
                $this->delegationContext->acteur(),
                $request->validated(),
                $this->delegationContext->sujet(),
            );
        } catch (InvalidArgumentException $e) {
            return back()->withInput()->with('error', $e->getMessage());
        }

        $courrier->refresh();

        return redirect()
            ->route('courriers.delivrance.index')
            ->with('success', 'Document délivré. N° de départ : '.$courrier->numero_depart.'. Dossier clôturé.');
    }

    public function cloturerSansDocument(Courrier $courrier): RedirectResponse
    {
        $this->authorize('courriers.delivrer');
        $this->authorize('update', $courrier);

        try {
            $this->delivranceService->cloturerSansDocument(
                $courrier,
                $this->delegationContext->acteur(),
                $this->delegationContext->sujet(),
            );
        } catch (InvalidArgumentException $e) {
            return back()->with('error', $e->getMessage());
        }

        return redirect()
            ->route('courriers.delivrance.index')
            ->with('success', 'Le dossier a été clôturé (aucun document à délivrer).');
    }
}
