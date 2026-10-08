<?php

namespace App\Http\Controllers;

use App\Http\Requests\AnnulerDepotRequest;
use App\Http\Requests\ImportCourrierRequest;
use App\Http\Requests\StoreCourrierRequest;
use App\Exports\CourrierImportTemplateExport;
use App\Models\CategorieCourrier;
use App\Models\Courrier;
use App\Models\PieceJointe;
use App\Services\CourrierEnregistrementService;
use App\Services\CourrierImportService;
use App\Services\DelegationContext;
use App\Services\RecuDepotPdfService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Storage;
use InvalidArgumentException;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Illuminate\View\View;

class CourrierController extends Controller
{
    public function __construct(
        private readonly DelegationContext $delegationContext,
    ) {}
    public function create(): View
    {
        $this->authorize('create', Courrier::class);
        
        $categories = CategorieCourrier::actives()->orderBy('nom')->get();
        $anneesDisponibles = range((int) date('Y'), 1990);

        return view('courriers.create', compact('categories', 'anneesDisponibles'));
    }

    public function store(
        StoreCourrierRequest $request,
        CourrierEnregistrementService $service
    ): RedirectResponse {
        $this->authorize('create', Courrier::class);
        
        $courrier = $service->enregistrer(
            $request->validated(),
            $request->user(),
            $request->file('pieces_jointes', [])
        );

        $message = 'Courrier enregistré avec succès. Numéro : '.$courrier->numero;
        if (! $courrier->necessite_reponse) {
            $message .= '. Sa catégorie ne nécessite pas de réponse : il est archivé / classé dès le dépôt et ne passe pas par la réception.';
        }

        return redirect()
            ->route('courriers.show', $courrier)
            ->with('success', $message)
            ->with('ouvrir_recu', true);
    }

    /**
     * Modèle Excel à remplir pour l'import en masse.
     */
    public function telechargerModeleImport(): BinaryFileResponse
    {
        $this->authorize('create', Courrier::class);

        return Excel::download(
            new CourrierImportTemplateExport(),
            'modele-import-courriers.xlsx'
        );
    }

    /**
     * Import de courriers depuis un fichier Excel. Les lignes valides sont
     * enregistrées, les lignes refusées sont restituées à l'utilisateur.
     */
    public function import(
        ImportCourrierRequest $request,
        CourrierImportService $service
    ): RedirectResponse {
        $resultat = $service->importer($request->file('fichier'), $request->user());

        $redirection = redirect()
            ->route('courriers.create')
            ->with('import_resultat', [
                'succes' => $resultat->succes,
                'erreurs' => $resultat->erreurs,
            ]);

        if ($resultat->aDesSucces() && ! $resultat->aDesErreurs()) {
            return $redirection->with(
                'success',
                $resultat->nbSucces().' courrier(s) importé(s) avec succès.'
            );
        }

        if (! $resultat->aDesSucces()) {
            return $redirection->with(
                'error',
                'Aucun courrier importé : '.$resultat->nbErreurs().' ligne(s) en erreur.'
            );
        }

        return $redirection->with(
            'success',
            $resultat->nbSucces().' courrier(s) importé(s), '.$resultat->nbErreurs().' ligne(s) ignorée(s).'
        );
    }

    public function show(Courrier $courrier): View
    {
        $this->authorize('view', $courrier);

        $courrier->load([
            'contribuable',
            'categorie',
            'typeCourrier',
            'uniteDestinataire',
            'uniteActuelle',
            'responsableActuel',
            'impots',
            'annees',
            'piecesJointes',
            'utilisateur',
            'suiviDossier.utilisateur',
            'suiviDossier.pourCompteDe',
            'historiqueAffectations',
        ]);

        return view('courriers.show', compact('courrier'));
    }

    public function recu(Courrier $courrier, RecuDepotPdfService $pdfService)
    {
        $this->authorize('view', $courrier);

        return $pdfService->afficher($courrier);
    }

    public function telechargerRecu(Courrier $courrier, RecuDepotPdfService $pdfService)
    {
        $this->authorize('view', $courrier);

        return $pdfService->telecharger($courrier);
    }

    public function afficherPieceJointe(Courrier $courrier, PieceJointe $pieceJointe): StreamedResponse
    {
        $this->authorize('view', $courrier);
        abort_unless($pieceJointe->courrier_id === $courrier->id, 404);

        return Storage::disk('local')->response(
            $pieceJointe->chemin,
            $pieceJointe->nom_original,
            ['Content-Type' => $pieceJointe->mime_type]
        );
    }

    public function telechargerPieceJointe(Courrier $courrier, PieceJointe $pieceJointe): StreamedResponse
    {
        $this->authorize('view', $courrier);
        abort_unless($pieceJointe->courrier_id === $courrier->id, 404);

        return Storage::disk('local')->download(
            $pieceJointe->chemin,
            $pieceJointe->nom_original,
            ['Content-Type' => $pieceJointe->mime_type]
        );
    }

    public function annulerDepot(
        AnnulerDepotRequest $request,
        Courrier $courrier,
        CourrierEnregistrementService $enregistrementService,
    ): RedirectResponse {
        $this->authorize('courriers.annuler_depot');
        $this->authorize('update', $courrier);

        try {
            $enregistrementService->annulerDepot(
                $courrier,
                $this->delegationContext->acteur(),
                $request->validated('motif'),
                $this->delegationContext->sujet(),
            );
        } catch (InvalidArgumentException $e) {
            return back()->with('error', $e->getMessage());
        }

        return redirect()
            ->route('dashboard')
            ->with('success', 'Le dépôt a été annulé. Le dossier est conservé en base.');
    }
}
