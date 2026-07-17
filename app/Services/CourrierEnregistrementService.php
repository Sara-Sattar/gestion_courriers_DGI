<?php

namespace App\Services;

use App\Enums\ActionHistorique;
use App\Enums\StatutCourrier;
use App\Models\Contribuable;
use App\Models\Courrier;
use App\Models\CourrierAnnee;
use App\Models\CourrierHistorique;
use App\Models\CourrierImpot;
use App\Models\HistoriqueAffectation;
use App\Models\PieceJointe;
use App\Models\Unite;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class CourrierEnregistrementService
{
    public function __construct(
        private readonly CourrierNumeroService $numeroService,
    ) {}

    /**
     * @param  array<string, mixed>  $data
     * @param  list<UploadedFile>  $fichiers
     */
    public function enregistrer(array $data, User $user, array $fichiers = []): Courrier
    {
        return DB::transaction(function () use ($data, $user, $fichiers) {
            $contribuable = Contribuable::findOrFail($data['contribuable_id']);
            $unite = Unite::findOrFail($data['unite_destinataire_id']);
            $numero = $this->numeroService->generer($unite);
            $responsableInitial = User::where('unite_id', $unite->id)->orderBy('id')->first();

            $courrier = Courrier::create([
                'numero' => $numero['numero'],
                'contribuable_id' => $contribuable->id,
                'categorie_courrier_id' => $data['categorie_courrier_id'],
                'type_courrier_id' => $data['type_courrier_id'],
                'objet' => $data['objet'],
                'unite_destinataire_id' => $unite->id,
                'unite_actuelle_id' => $unite->id,
                'responsable_actuel_id' => $responsableInitial?->id,
                'code_unite' => $numero['code_unite'],
                'annee_enregistrement' => $numero['annee'],
                'sequence' => $numero['sequence'],
                'remarque' => $data['remarque'] ?? null,
                'statut' => StatutCourrier::Depose,
                'date_depot' => now(),
                'user_id' => $user->id,
            ]);

            foreach ($data['impots'] as $impot) {
                CourrierImpot::create([
                    'courrier_id' => $courrier->id,
                    'impot' => $impot,
                ]);
            }

            foreach ($data['annees'] as $annee) {
                CourrierAnnee::create([
                    'courrier_id' => $courrier->id,
                    'annee' => (int) $annee,
                ]);
            }

            $this->enregistrerPiecesJointes($courrier, $fichiers);

            CourrierHistorique::create([
                'courrier_id' => $courrier->id,
                'user_id' => $user->id,
                'action' => ActionHistorique::Depot,
            ]);

            // Enregistrer la première affectation dans l'historique
            HistoriqueAffectation::create([
                'courrier_id' => $courrier->id,
                'user_id' => $user->id,
                'unite_source_id' => null,
                'unite_destination_id' => $unite->id,
                'ancien_responsable_id' => null,
                'nouveau_responsable_id' => $responsableInitial?->id,
                'commentaire' => 'Dépôt initial du courrier',
                'action_type' => null,
            ]);

            return $courrier->load([
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
        });
    }

    /**
     * @param  list<UploadedFile>  $fichiers
     */
    private function enregistrerPiecesJointes(Courrier $courrier, array $fichiers): void
    {
        foreach ($fichiers as $index => $fichier) {
            if (! $fichier instanceof UploadedFile) {
                continue;
            }

            $extension = strtolower($fichier->getClientOriginalExtension());
            $nomStockage = Str::uuid()->toString().'.'.$extension;
            $chemin = $fichier->storeAs(
                'courriers/'.$courrier->id,
                $nomStockage,
                'local'
            );

            PieceJointe::create([
                'courrier_id' => $courrier->id,
                'nom_original' => $fichier->getClientOriginalName(),
                'nom_stockage' => $nomStockage,
                'chemin' => $chemin,
                'extension' => $extension,
                'mime_type' => $fichier->getMimeType() ?? 'application/octet-stream',
                'taille' => $fichier->getSize(),
                'ordre' => $index,
            ]);
        }
    }
}
