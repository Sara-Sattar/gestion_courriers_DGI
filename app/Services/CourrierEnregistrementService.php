<?php

namespace App\Services;

use App\Enums\ActionHistorique;
use App\Enums\StatutCourrier;
use App\Models\CategorieCourrier;
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
            $contribuable = $this->resoudreContribuable($data);
            $unite = Unite::findOrFail($data['unite_destinataire_id']);
            $numero = $this->numeroService->generer($unite);

            // « Nécessitant une réponse » est défini par la catégorie, jamais par la saisie.
            $necessiteReponse = CategorieCourrier::findOrFail($data['categorie_courrier_id'])->necessite_reponse;

            // Affecté à l'unité (pas à un agent) : tous les agents le voient
            // jusqu'à ce qu'un d'entre eux confirme la réception.
            $statutInitial = $necessiteReponse
                ? StatutCourrier::Depose
                : StatutCourrier::Archive;

            $courrier = Courrier::create([
                'numero' => $numero['numero'],
                'contribuable_id' => $contribuable->id,
                'categorie_courrier_id' => $data['categorie_courrier_id'],
                'type_courrier_id' => $data['type_courrier_id'],
                'objet' => $data['objet'] ?? null,
                'necessite_reponse' => $necessiteReponse,
                'unite_destinataire_id' => $unite->id,
                'unite_actuelle_id' => $unite->id,
                'responsable_actuel_id' => null,
                'code_unite' => $numero['code_unite'],
                'annee_enregistrement' => $numero['annee'],
                'sequence' => $numero['sequence'],
                'remarque' => $data['remarque'] ?? null,
                'statut' => $statutInitial,
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
                'commentaire' => sprintf(
                    'Dépôt initial du courrier — Unité destinataire : %s (%s)',
                    $unite->nom,
                    $unite->code
                ),
            ]);

            CourrierHistorique::create([
                'courrier_id' => $courrier->id,
                'user_id' => $user->id,
                'action' => ActionHistorique::Affectation,
                'commentaire' => sprintf(
                    'Affectation à l\'unité %s (%s)',
                    $unite->nom,
                    $unite->code
                ),
            ]);

            if (! $necessiteReponse) {
                CourrierHistorique::create([
                    'courrier_id' => $courrier->id,
                    'user_id' => $user->id,
                    'action' => ActionHistorique::Archivage,
                    'commentaire' => 'Courrier ne nécessitant pas de réponse — archivé / classé dès le dépôt.',
                ]);
            }

            // Enregistrer la première affectation dans l'historique
            HistoriqueAffectation::create([
                'courrier_id' => $courrier->id,
                'user_id' => $user->id,
                'unite_source_id' => null,
                'unite_destination_id' => $unite->id,
                'ancien_responsable_id' => null,
                'nouveau_responsable_id' => null,
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
     * Annulation soft du dépôt (CDC) — le dossier reste en base.
     */
    public function annulerDepot(Courrier $courrier, User $acteur, string $motif, ?User $sujet = null): void
    {
        $sujet ??= $acteur;

        DB::transaction(function () use ($courrier, $acteur, $sujet, $motif) {
            $courrier = Courrier::query()->lockForUpdate()->findOrFail($courrier->id);

            if ($courrier->statut !== StatutCourrier::Depose) {
                throw new \InvalidArgumentException(
                    'Seul un courrier encore au statut Déposé peut être annulé.'
                );
            }

            $courrier->update([
                'statut' => StatutCourrier::Annule,
                'date_annulation' => now(),
                'motif_annulation' => $motif,
                'annule_par_id' => $sujet->id,
                'responsable_actuel_id' => null,
            ]);

            CourrierHistorique::create([
                'courrier_id' => $courrier->id,
                'user_id' => $acteur->id,
                'pour_compte_de_id' => $acteur->id !== $sujet->id ? $sujet->id : null,
                'action' => ActionHistorique::AnnulationDepot,
                'commentaire' => 'Annulation de dépôt — Motif : '.$motif,
            ]);
        });
    }

    /**
     * Récupère le contribuable sélectionné, ou le crée à partir de la saisie manuelle.
     * Un contribuable déjà connu (même IFU, CIN ou ICE) est réutilisé et complété.
     *
     * @param  array<string, mixed>  $data
     */
    private function resoudreContribuable(array $data): Contribuable
    {
        if (($data['mode_contribuable'] ?? 'recherche') === 'recherche') {
            return Contribuable::findOrFail($data['contribuable_id']);
        }

        $champs = [
            'nom_raison_sociale',
            'type_contribuable',
            'ifu',
            'cin',
            'ice',
            'numero_tp',
            'numero_th',
            'adresse',
            'ville',
            'telephone',
            'email',
        ];

        $valeurs = collect($champs)
            ->mapWithKeys(fn (string $champ) => [$champ => $data[$champ] ?? null])
            ->filter(fn ($valeur) => filled($valeur))
            ->all();

        $existant = $this->trouverContribuableExistant($data);

        if ($existant) {
            $existant->fill($valeurs)->save();

            return $existant;
        }

        return Contribuable::create($valeurs);
    }

    /**
     * @param  array<string, mixed>  $data
     */
    private function trouverContribuableExistant(array $data): ?Contribuable
    {
        foreach (['ifu', 'cin', 'ice'] as $identifiant) {
            if (blank($data[$identifiant] ?? null)) {
                continue;
            }

            $contribuable = Contribuable::query()
                ->where($identifiant, $data[$identifiant])
                ->first();

            if ($contribuable) {
                return $contribuable;
            }
        }

        return null;
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
