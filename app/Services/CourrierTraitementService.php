<?php

namespace App\Services;

use App\Enums\ActionHistorique;
use App\Enums\ResultatTraitement;
use App\Enums\StatutCourrier;
use App\Models\Courrier;
use App\Models\CourrierHistorique;
use App\Models\Role;
use App\Models\Unite;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class CourrierTraitementService
{
    /**
     * @param  array{
     *     resultat: ResultatTraitement|string,
     *     date_traitement: string,
     *     document_genere?: bool|null,
     *     unite_retour_id?: int|null,
     *     motif_rejet?: string|null,
     *     motif_suspension?: string|null,
     * }  $donnees
     */
    public function traiter(Courrier $courrier, User $acteur, array $donnees, ?User $sujet = null): void
    {
        $sujet ??= $acteur;
        $resultat = $donnees['resultat'] instanceof ResultatTraitement
            ? $donnees['resultat']
            : ResultatTraitement::from($donnees['resultat']);

        DB::transaction(function () use ($courrier, $acteur, $sujet, $resultat, $donnees) {
            $courrier = Courrier::query()->lockForUpdate()->findOrFail($courrier->id);

            if ($courrier->statut === StatutCourrier::Suspendu) {
                throw new InvalidArgumentException(
                    'Ce dossier est suspendu. Utilisez « Reprendre le traitement » avant de le traiter.'
                );
            }

            if ($courrier->statut !== StatutCourrier::Receptionne) {
                throw new InvalidArgumentException(
                    'Seul un courrier réceptionné peut être traité.'
                );
            }

            if ($courrier->responsable_actuel_id !== $sujet->id) {
                throw new InvalidArgumentException(
                    'Seul le responsable du dossier peut le traiter.'
                );
            }

            $this->assurerDebutTraitement($courrier, $acteur, $sujet);

            match ($resultat) {
                ResultatTraitement::Favorable => $this->appliquerFavorable($courrier, $acteur, $sujet, $donnees),
                ResultatTraitement::Rejet => $this->appliquerRejet($courrier, $acteur, $sujet, $donnees),
                ResultatTraitement::Suspendu => $this->appliquerSuspendu($courrier, $acteur, $sujet, $donnees),
            };
        });
    }

    /**
     * Remet un dossier suspendu dans la file de traitement (statut réceptionné).
     */
    public function reprendre(Courrier $courrier, User $acteur, ?User $sujet = null): void
    {
        $sujet ??= $acteur;

        DB::transaction(function () use ($courrier, $acteur, $sujet) {
            $courrier = Courrier::query()->lockForUpdate()->findOrFail($courrier->id);

            if ($courrier->statut !== StatutCourrier::Suspendu) {
                throw new InvalidArgumentException('Seul un dossier suspendu peut être repris.');
            }

            if ($courrier->responsable_actuel_id !== $sujet->id) {
                throw new InvalidArgumentException('Seul le responsable du dossier peut le reprendre.');
            }

            $courrier->update([
                'statut' => StatutCourrier::Receptionne,
                'motif_suspension' => null,
            ]);

            CourrierHistorique::create([
                ...$this->trace($acteur, $sujet),
                'courrier_id' => $courrier->id,
                'action' => ActionHistorique::Reprise,
                'commentaire' => 'Reprise du traitement — dossier remis dans la file de traitement.',
            ]);

            $this->assurerDebutTraitement($courrier, $acteur, $sujet);
        });
    }

    public function uniteAccueil(): Unite
    {
        $code = (string) config('dgi.unite_accueil_code', 'BO001');
        $unite = Unite::query()->where('code', $code)->actives()->first();

        if ($unite) {
            return $unite;
        }

        $roleAccueil = Role::query()->where('name', 'Accueil')->first();
        if ($roleAccueil) {
            $userAccueil = User::query()
                ->where('role_id', $roleAccueil->id)
                ->whereNotNull('unite_id')
                ->with('unite')
                ->first();

            if ($userAccueil?->unite) {
                return $userAccueil->unite;
            }
        }

        throw new InvalidArgumentException(
            'Aucune unité Accueil configurée. Vérifiez config dgi.unite_accueil_code.'
        );
    }

    private function assurerDebutTraitement(Courrier $courrier, User $acteur, User $sujet): void
    {
        $deja = CourrierHistorique::query()
            ->where('courrier_id', $courrier->id)
            ->where('action', ActionHistorique::DebutTraitement)
            ->exists();

        if ($deja) {
            return;
        }

        CourrierHistorique::create([
            ...$this->trace($acteur, $sujet),
            'courrier_id' => $courrier->id,
            'action' => ActionHistorique::DebutTraitement,
            'commentaire' => sprintf(
                'Début du traitement — Unité : %s',
                $sujet->unite?->nom ?? 'non renseignée'
            ),
        ]);
    }

    /**
     * @param  array<string, mixed>  $donnees
     */
    private function appliquerFavorable(Courrier $courrier, User $acteur, User $sujet, array $donnees): void
    {
        $accueil = $this->uniteAccueil();
        $documentGenere = (bool) ($donnees['document_genere'] ?? false);

        $courrier->update([
            'statut' => StatutCourrier::TraiteFavorable,
            'date_traitement' => $donnees['date_traitement'],
            'traite_par_id' => $sujet->id,
            'document_genere' => $documentGenere,
            'motif_rejet' => null,
            'motif_suspension' => null,
            'unite_retour_id' => $accueil->id,
            // Uniquement l'unité actuelle : unite_destinataire_id reste lié à la numérotation d'origine.
            'unite_actuelle_id' => $accueil->id,
            'responsable_actuel_id' => null,
        ]);

        CourrierHistorique::create([
            ...$this->trace($acteur, $sujet),
            'courrier_id' => $courrier->id,
            'action' => ActionHistorique::TraitementFavorable,
            'commentaire' => sprintf(
                'Traitement favorable — Document généré : %s',
                $documentGenere ? 'Oui' : 'Non'
            ),
        ]);

        CourrierHistorique::create([
            ...$this->trace($acteur, $sujet),
            'courrier_id' => $courrier->id,
            'action' => ActionHistorique::RetourAccueil,
            'commentaire' => sprintf(
                'Retour à l\'Accueil — Unité : %s (%s) — file Documents à délivrer',
                $accueil->nom,
                $accueil->code
            ),
        ]);
    }

    /**
     * @param  array<string, mixed>  $donnees
     */
    private function appliquerRejet(Courrier $courrier, User $acteur, User $sujet, array $donnees): void
    {
        $motif = trim((string) ($donnees['motif_rejet'] ?? ''));
        if ($motif === '') {
            throw new InvalidArgumentException('Le motif du rejet est obligatoire.');
        }

        $accueil = $this->uniteAccueil();

        $courrier->update([
            'statut' => StatutCourrier::Rejete,
            'date_traitement' => $donnees['date_traitement'],
            'traite_par_id' => $sujet->id,
            'document_genere' => false,
            'motif_rejet' => $motif,
            'motif_suspension' => null,
            'unite_retour_id' => $accueil->id,
            // Uniquement l'unité actuelle : unite_destinataire_id reste lié à la numérotation d'origine.
            'unite_actuelle_id' => $accueil->id,
            'responsable_actuel_id' => null,
        ]);

        CourrierHistorique::create([
            ...$this->trace($acteur, $sujet),
            'courrier_id' => $courrier->id,
            'action' => ActionHistorique::TraitementRejete,
            'commentaire' => sprintf('Traitement rejeté — Motif : %s', $motif),
        ]);

        CourrierHistorique::create([
            ...$this->trace($acteur, $sujet),
            'courrier_id' => $courrier->id,
            'action' => ActionHistorique::RetourAccueil,
            'commentaire' => sprintf(
                'Retour à l\'Accueil — Unité : %s (%s)',
                $accueil->nom,
                $accueil->code
            ),
        ]);
    }

    /**
     * @param  array<string, mixed>  $donnees
     */
    private function appliquerSuspendu(Courrier $courrier, User $acteur, User $sujet, array $donnees): void
    {
        $motif = trim((string) ($donnees['motif_suspension'] ?? ''));
        if ($motif === '') {
            throw new InvalidArgumentException('Le motif de suspension est obligatoire.');
        }

        $courrier->update([
            'statut' => StatutCourrier::Suspendu,
            'date_traitement' => $donnees['date_traitement'],
            'traite_par_id' => $sujet->id,
            'document_genere' => null,
            'motif_rejet' => null,
            'motif_suspension' => $motif,
            'unite_retour_id' => null,
        ]);

        CourrierHistorique::create([
            ...$this->trace($acteur, $sujet),
            'courrier_id' => $courrier->id,
            'action' => ActionHistorique::Suspension,
            'commentaire' => sprintf('Suspension — Motif : %s', $motif),
        ]);
    }

    /**
     * @return array{user_id: int, pour_compte_de_id: int|null}
     */
    private function trace(User $acteur, User $sujet): array
    {
        return [
            'user_id' => $acteur->id,
            'pour_compte_de_id' => $acteur->id !== $sujet->id ? $sujet->id : null,
        ];
    }
}
