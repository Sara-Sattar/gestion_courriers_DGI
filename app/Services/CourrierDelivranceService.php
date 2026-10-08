<?php

namespace App\Services;

use App\Enums\ActionHistorique;
use App\Enums\ModeLivraison;
use App\Enums\StatutCourrier;
use App\Models\Courrier;
use App\Models\CourrierHistorique;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class CourrierDelivranceService
{
    public function __construct(
        private readonly NumeroDepartService $numeroDepartService,
    ) {}

    /**
     * @param  array{
     *     date_delivrance: string,
     *     mode_livraison: ModeLivraison|string,
     *     mandataire_piece?: string|null,
     *     postal_cin?: string|null,
     *     postal_nom?: string|null,
     *     reference_envoi?: string|null,
     * }  $donnees
     */
    public function delivrer(Courrier $courrier, User $acteur, array $donnees, ?User $sujet = null): void
    {
        $sujet ??= $acteur;
        $mode = $donnees['mode_livraison'] instanceof ModeLivraison
            ? $donnees['mode_livraison']
            : ModeLivraison::from($donnees['mode_livraison']);

        DB::transaction(function () use ($courrier, $acteur, $sujet, $mode, $donnees) {
            $courrier = Courrier::query()->lockForUpdate()->findOrFail($courrier->id);

            if ($courrier->statut !== StatutCourrier::TraiteFavorable) {
                throw new InvalidArgumentException(
                    'Seuls les dossiers traités favorablement peuvent être délivrés.'
                );
            }

            if (! $courrier->document_genere) {
                throw new InvalidArgumentException(
                    'La délivrance n\'est possible que lorsqu\'un document a été généré.'
                );
            }

            if ($sujet->unite_id !== $courrier->unite_actuelle_id) {
                throw new InvalidArgumentException(
                    'Seul un agent de l\'unité Accueil destinataire peut délivrer ce document.'
                );
            }

            $unite = $sujet->unite;
            if (! $unite) {
                throw new InvalidArgumentException('L\'agent Accueil doit être rattaché à une unité.');
            }

            $champsMode = $this->champsSelonMode($mode, $donnees);
            $numero = $this->numeroDepartService->generer($unite);

            $courrier->update([
                ...$champsMode,
                'statut' => StatutCourrier::Cloture,
                'date_delivrance' => $donnees['date_delivrance'],
                'delivre_par_id' => $sujet->id,
                'mode_livraison' => $mode->value,
                'numero_depart' => $numero['numero'],
                'annee_depart' => $numero['annee'],
                'sequence_depart' => $numero['sequence'],
                'code_unite_depart' => $numero['code_unite'],
                'responsable_actuel_id' => $sujet->id,
            ]);

            CourrierHistorique::create([
                'user_id' => $acteur->id,
                'pour_compte_de_id' => $acteur->id !== $sujet->id ? $sujet->id : null,
                'courrier_id' => $courrier->id,
                'action' => ActionHistorique::Delivrance,
                'commentaire' => sprintf(
                    'Délivrance — Date : %s — Par : %s — Mode : %s — N° départ : %s%s',
                    $donnees['date_delivrance'],
                    $sujet->name,
                    $mode->label(),
                    $numero['numero'],
                    $this->complementCommentaire($mode, $champsMode)
                ),
            ]);

            CourrierHistorique::create([
                'user_id' => $acteur->id,
                'pour_compte_de_id' => $acteur->id !== $sujet->id ? $sujet->id : null,
                'courrier_id' => $courrier->id,
                'action' => ActionHistorique::Cloture,
                'commentaire' => 'Dossier clôturé après délivrance du document.',
            ]);
        });
    }

    public function cloturerSansDocument(Courrier $courrier, User $acteur, ?User $sujet = null): void
    {
        $sujet ??= $acteur;

        DB::transaction(function () use ($courrier, $acteur, $sujet) {
            $courrier = Courrier::query()->lockForUpdate()->findOrFail($courrier->id);

            if ($courrier->statut !== StatutCourrier::TraiteFavorable) {
                throw new InvalidArgumentException('Seul un dossier traité favorablement peut être clôturé.');
            }

            if ($courrier->document_genere) {
                throw new InvalidArgumentException(
                    'Un document a été généré : utilisez la délivrance.'
                );
            }

            if ($sujet->unite_id !== $courrier->unite_actuelle_id) {
                throw new InvalidArgumentException(
                    'Seul un agent de l\'unité Accueil destinataire peut clôturer ce dossier.'
                );
            }

            $courrier->update([
                'statut' => StatutCourrier::Cloture,
                'date_delivrance' => now()->toDateString(),
                'delivre_par_id' => $sujet->id,
                'responsable_actuel_id' => $sujet->id,
            ]);

            CourrierHistorique::create([
                'user_id' => $acteur->id,
                'pour_compte_de_id' => $acteur->id !== $sujet->id ? $sujet->id : null,
                'courrier_id' => $courrier->id,
                'action' => ActionHistorique::Cloture,
                'commentaire' => 'Dossier clôturé (traitement favorable sans document à délivrer).',
            ]);
        });
    }

    /**
     * @param  array<string, mixed>  $donnees
     * @return array{mandataire_piece: null|string, postal_cin: null|string, postal_nom: null|string, reference_envoi: null|string}
     */
    private function champsSelonMode(ModeLivraison $mode, array $donnees): array
    {
        return match ($mode) {
            ModeLivraison::Mandataire => [
                'mandataire_piece' => $donnees['mandataire_piece'] ?? null,
                'mandataire_nom' => $this->requis($donnees, 'mandataire_nom', 'Le nom du mandataire est obligatoire.'),
                'mandataire_prenom' => $this->requis($donnees, 'mandataire_prenom', 'Le prénom du mandataire est obligatoire.'),
                'mandataire_cin' => $this->requis($donnees, 'mandataire_cin', 'Le CIN du mandataire est obligatoire.'),
                'date_procuration' => $this->requis($donnees, 'date_procuration', 'La date de procuration est obligatoire.'),
                'postal_cin' => null,
                'postal_nom' => null,
                'reference_envoi' => null,
            ],
            ModeLivraison::EnvoiPostal => [
                'mandataire_piece' => null,
                'mandataire_nom' => null,
                'mandataire_prenom' => null,
                'mandataire_cin' => null,
                'date_procuration' => null,
                'postal_cin' => $this->requis($donnees, 'postal_cin', 'Le CIN est obligatoire pour un envoi postal.'),
                'postal_nom' => $this->requis($donnees, 'postal_nom', 'Le nom postal est obligatoire.'),
                'reference_envoi' => $this->requis($donnees, 'reference_envoi', 'La référence d\'envoi est obligatoire pour un envoi postal.'),
            ],
            ModeLivraison::Interesse => [
                'mandataire_piece' => null,
                'mandataire_nom' => null,
                'mandataire_prenom' => null,
                'mandataire_cin' => null,
                'date_procuration' => null,
                'postal_cin' => null,
                'postal_nom' => null,
                'reference_envoi' => null,
            ],
        };
    }

    /**
     * @param  array<string, mixed>  $donnees
     */
    private function requis(array $donnees, string $cle, string $message): string
    {
        $valeur = trim((string) ($donnees[$cle] ?? ''));
        if ($valeur === '') {
            throw new InvalidArgumentException($message);
        }

        return $valeur;
    }

    /**
     * @param  array<string, mixed>  $champs
     */
    private function complementCommentaire(ModeLivraison $mode, array $champs): string
    {
        return match ($mode) {
            ModeLivraison::Interesse => '',
            ModeLivraison::Mandataire => sprintf(
                ' — Mandataire : %s %s (CIN %s) — Procuration : %s',
                $champs['mandataire_prenom'],
                $champs['mandataire_nom'],
                $champs['mandataire_cin'],
                $champs['date_procuration']
            ),
            ModeLivraison::EnvoiPostal => sprintf(
                ' — CIN : %s — Nom postal : %s — Réf. envoi : %s',
                $champs['postal_cin'],
                $champs['postal_nom'],
                $champs['reference_envoi']
            ),
        };
    }
}
