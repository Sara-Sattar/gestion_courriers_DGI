<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreCourrierRequest;
use App\Models\CategorieCourrier;
use App\Models\Courrier;
use App\Models\PieceJointe;
use App\Services\CourrierEnregistrementService;
use App\Services\RecuDepotPdfService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Illuminate\View\View;

class CourrierController extends Controller
{
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

        return redirect()
            ->route('courriers.show', $courrier)
            ->with('success', 'Courrier enregistré avec succès. Numéro : '.$courrier->numero);
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
            'historiques.utilisateur',
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
}
