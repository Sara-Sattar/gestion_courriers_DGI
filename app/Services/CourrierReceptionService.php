<?php

namespace App\Services;

use App\Enums\ActionHistorique;
use App\Enums\ActionType;
use App\Enums\StatutCourrier;
use App\Models\Courrier;
use App\Models\CourrierHistorique;
use App\Models\HistoriqueAffectation;
use App\Models\Unite;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class CourrierReceptionService
{
    /**
     * @param  list<int|string>  $courrierIds
     * @return int Nombre de courriers effectivement réceptionnés
     */
    public function confirmerReceptions(array $courrierIds, User $acteur, ?User $sujet = null): int
    {
        $sujet ??= $acteur;
        $maintenant = now();
        $confirmes = 0;

        DB::transaction(function () use ($courrierIds, $acteur, $sujet, $maintenant, &$confirmes) {
            foreach ($courrierIds as $courrierId) {
                $courrier = Courrier::query()->lockForUpdate()->findOrFail($courrierId);

                if (! in_array($courrier->statut, [StatutCourrier::Depose, StatutCourrier::Reaffecte], true)) {
                    continue;
                }

                if ($sujet->unite_id !== $courrier->unite_actuelle_id) {
                    continue;
                }

                $this->marquerReceptionne($courrier, $acteur, $sujet, $maintenant);
                $confirmes++;
            }
        });

        return $confirmes;
    }

    public function confirmerReception(Courrier $courrier, User $acteur, ?User $sujet = null): void
    {
        $sujet ??= $acteur;

        DB::transaction(function () use ($courrier, $acteur, $sujet) {
            $courrier = Courrier::query()->lockForUpdate()->findOrFail($courrier->id);

            if ($courrier->statut->bloqueReceptionOuReaffectation()) {
                throw new InvalidArgumentException('Impossible de confirmer la réception d\'un courrier déjà traité ou clôturé.');
            }

            if (! in_array($courrier->statut, [StatutCourrier::Depose, StatutCourrier::Reaffecte], true)) {
                throw new InvalidArgumentException('Seul un courrier en attente de réception peut être réceptionné.');
            }

            $this->assurerAppartenanceUnite($courrier, $sujet);
            $this->marquerReceptionne($courrier, $acteur, $sujet, now());
        });
    }

    public function annulerReception(Courrier $courrier, User $acteur, ?User $sujet = null): void
    {
        $sujet ??= $acteur;

        if ($courrier->statut->bloqueReceptionOuReaffectation()) {
            throw new InvalidArgumentException('Impossible d\'annuler la réception d\'un courrier déjà traité ou clôturé.');
        }

        if ($courrier->statut !== StatutCourrier::Receptionne) {
            throw new InvalidArgumentException('Seul un courrier déjà réceptionné peut voir sa réception annulée.');
        }

        $this->assurerResponsable($courrier, $sujet);

        DB::transaction(function () use ($courrier, $acteur, $sujet) {
            $courrier = $courrier->fresh();
            $unite = $sujet->unite;
            $dateReception = $courrier->date_reception?->format('d/m/Y');
            $heureReception = $courrier->heure_reception
                ? \Illuminate\Support\Carbon::parse($courrier->heure_reception)->format('H:i')
                : null;
            $ancienResponsableId = $courrier->responsable_actuel_id ?? $sujet->id;

            $courrier->update([
                'statut' => StatutCourrier::Depose,
                'responsable_actuel_id' => null,
                'date_reception' => null,
                'heure_reception' => null,
                'responsable_reception_id' => null,
            ]);

            CourrierHistorique::create([
                ...$this->trace($acteur, $sujet),
                'courrier_id' => $courrier->id,
                'action' => ActionHistorique::AnnulationReception,
                'commentaire' => sprintf(
                    'Annulation de la réception%s%s — Unité : %s',
                    $dateReception ? ' du '.$dateReception : '',
                    $heureReception ? ' à '.$heureReception : '',
                    $unite?->nom ?? 'non renseignée'
                ),
            ]);

            HistoriqueAffectation::create([
                ...$this->trace($acteur, $sujet),
                'courrier_id' => $courrier->id,
                'unite_source_id' => $unite?->id,
                'unite_destination_id' => $unite?->id,
                'ancien_responsable_id' => $ancienResponsableId,
                'nouveau_responsable_id' => null,
                'action_type' => ActionType::AnnulationReception,
                'motif' => null,
                'commentaire' => 'Annulation de la réception',
            ]);
        });
    }

    public function reaffecter(Courrier $courrier, User $acteur, int $uniteDestinationId, string $motif, ?User $sujet = null): void
    {
        $sujet ??= $acteur;

        if ($courrier->statut->bloqueReceptionOuReaffectation()) {
            throw new InvalidArgumentException('Impossible de réaffecter un courrier déjà traité ou clôturé.');
        }

        // La réception doit être confirmée avant toute réaffectation.
        if (in_array($courrier->statut, [StatutCourrier::Depose, StatutCourrier::Reaffecte], true)) {
            throw new InvalidArgumentException('Confirmez d\'abord la réception de ce courrier avant de le réaffecter.');
        }

        if ($courrier->statut !== StatutCourrier::Receptionne) {
            throw new InvalidArgumentException('Le courrier ne peut pas être réaffecté dans son état actuel.');
        }

        $this->assurerResponsable($courrier, $sujet);

        $unitesAutorisees = $this->unitesReaffectationAutorisees($sujet)->pluck('id');

        if (! $unitesAutorisees->contains($uniteDestinationId)) {
            throw new InvalidArgumentException('Réaffectation vers cette unité non autorisée.');
        }

        $nouvelleUnite = Unite::query()->findOrFail($uniteDestinationId);

        DB::transaction(function () use ($courrier, $acteur, $sujet, $nouvelleUnite, $motif) {
            $courrier = $courrier->fresh();
            $ancienneUniteId = $courrier->unite_actuelle_id;
            $ancienResponsableId = $courrier->responsable_actuel_id;

            $courrier->update([
                'statut' => StatutCourrier::Reaffecte,
                'unite_actuelle_id' => $nouvelleUnite->id,
                'unite_destinataire_id' => $nouvelleUnite->id,
                'responsable_actuel_id' => null,
                'date_reception' => null,
                'heure_reception' => null,
                'responsable_reception_id' => null,
            ]);

            HistoriqueAffectation::create([
                ...$this->trace($acteur, $sujet),
                'courrier_id' => $courrier->id,
                'unite_source_id' => $ancienneUniteId,
                'unite_destination_id' => $nouvelleUnite->id,
                'ancien_responsable_id' => $ancienResponsableId,
                'nouveau_responsable_id' => null,
                'action_type' => ActionType::Reaffectation,
                'motif' => $motif,
                'commentaire' => $motif,
            ]);

            CourrierHistorique::create([
                ...$this->trace($acteur, $sujet),
                'courrier_id' => $courrier->id,
                'action' => ActionHistorique::Reaffectation,
                'commentaire' => sprintf(
                    'Réaffectation vers %s (%s) — Motif : %s',
                    $nouvelleUnite->nom,
                    $nouvelleUnite->code,
                    $motif
                ),
            ]);
        });
    }

    /**
     * Refus d'une réaffectation → retour à l'unité précédente (n−1) avec motif.
     */
    public function refuserReaffectation(Courrier $courrier, User $acteur, string $motif, ?User $sujet = null): void
    {
        $sujet ??= $acteur;

        if ($courrier->statut !== StatutCourrier::Reaffecte) {
            throw new InvalidArgumentException('Seul un courrier réaffecté peut faire l\'objet d\'un refus de réaffectation.');
        }

        $this->assurerAppartenanceUnite($courrier, $sujet);

        $derniere = HistoriqueAffectation::query()
            ->where('courrier_id', $courrier->id)
            ->where('action_type', ActionType::Reaffectation)
            ->orderByDesc('id')
            ->first();

        if (! $derniere?->unite_source_id) {
            throw new InvalidArgumentException('Impossible de déterminer l\'unité précédente (n−1).');
        }

        $unitePrecedente = Unite::query()->findOrFail($derniere->unite_source_id);

        DB::transaction(function () use ($courrier, $acteur, $sujet, $motif, $unitePrecedente) {
            $courrier = Courrier::query()->lockForUpdate()->findOrFail($courrier->id);
            $ancienneUniteId = $courrier->unite_actuelle_id;

            $courrier->update([
                'statut' => StatutCourrier::Depose,
                'unite_actuelle_id' => $unitePrecedente->id,
                'unite_destinataire_id' => $unitePrecedente->id,
                'responsable_actuel_id' => null,
                'date_reception' => null,
                'heure_reception' => null,
                'responsable_reception_id' => null,
            ]);

            CourrierHistorique::create([
                ...$this->trace($acteur, $sujet),
                'courrier_id' => $courrier->id,
                'action' => ActionHistorique::RefusReaffectation,
                'commentaire' => sprintf(
                    'Refus de réaffectation — Motif : %s — Retour vers %s (%s)',
                    $motif,
                    $unitePrecedente->nom,
                    $unitePrecedente->code
                ),
            ]);

            HistoriqueAffectation::create([
                ...$this->trace($acteur, $sujet),
                'courrier_id' => $courrier->id,
                'unite_source_id' => $ancienneUniteId,
                'unite_destination_id' => $unitePrecedente->id,
                'ancien_responsable_id' => null,
                'nouveau_responsable_id' => null,
                'action_type' => ActionType::RefusReaffectation,
                'motif' => $motif,
                'commentaire' => 'Refus de réaffectation — retour n−1',
            ]);
        });
    }

    /**
     * Refus de réception (mauvaise destination) → retour Bureau d'ordre avec motif.
     */
    public function refuserReception(Courrier $courrier, User $acteur, string $motif, ?User $sujet = null): void
    {
        $sujet ??= $acteur;

        if (! in_array($courrier->statut, [StatutCourrier::Depose, StatutCourrier::Reaffecte], true)) {
            throw new InvalidArgumentException('Seul un courrier en attente de réception peut être refusé.');
        }

        $this->assurerAppartenanceUnite($courrier, $sujet);

        $bureauOrdre = Unite::query()
            ->actives()
            ->where(function ($q) {
                $q->where('est_bureau_ordre', true)->orWhere('code', config('dgi.unite_accueil_code', 'BO001'));
            })
            ->first();

        if (! $bureauOrdre) {
            throw new InvalidArgumentException('Aucun bureau d\'ordre n\'est configuré pour le retour.');
        }

        DB::transaction(function () use ($courrier, $acteur, $sujet, $motif, $bureauOrdre) {
            $courrier = Courrier::query()->lockForUpdate()->findOrFail($courrier->id);
            $ancienneUniteId = $courrier->unite_actuelle_id;

            $courrier->update([
                'statut' => StatutCourrier::Depose,
                'unite_actuelle_id' => $bureauOrdre->id,
                'responsable_actuel_id' => null,
                'date_reception' => null,
                'heure_reception' => null,
                'responsable_reception_id' => null,
            ]);

            CourrierHistorique::create([
                ...$this->trace($acteur, $sujet),
                'courrier_id' => $courrier->id,
                'action' => ActionHistorique::RefusReception,
                'commentaire' => sprintf(
                    'Refus de réception — Motif : %s — Retour B.O. %s (%s)',
                    $motif,
                    $bureauOrdre->nom,
                    $bureauOrdre->code
                ),
            ]);

            HistoriqueAffectation::create([
                ...$this->trace($acteur, $sujet),
                'courrier_id' => $courrier->id,
                'unite_source_id' => $ancienneUniteId,
                'unite_destination_id' => $bureauOrdre->id,
                'ancien_responsable_id' => null,
                'nouveau_responsable_id' => null,
                'action_type' => ActionType::Reaffectation,
                'motif' => $motif,
                'commentaire' => 'Refus de réception — retour B.O.',
            ]);
        });
    }

    /**
     * Types autorisés selon le niveau CDC.
     *
     * @return list<string>
     */
    public function typesReaffectationAutorises(?Unite $unite): array
    {
        if (! $unite) {
            return array_keys(Unite::NIVEAUX);
        }

        if ($unite->est_bureau_ordre || in_array($unite->type_unite, ['DR', 'Direction'], true)) {
            return array_keys(Unite::NIVEAUX);
        }

        return match ($unite->type_unite) {
            'Service' => ['Subdivision', 'RAF', 'BET', 'Brigade', 'Bureau', 'Centre', 'Secteur', 'Section'],
            'Subdivision', 'RAF', 'BET', 'Brigade', 'Bureau', 'Centre' => ['Secteur', 'Section'],
            default => ['Secteur', 'Section'],
        };
    }

    /**
     * @return Collection<int, Unite>
     */
    public function unitesReaffectationAutorisees(User $utilisateur): Collection
    {
        $uniteCourante = $utilisateur->unite;
        $uniteCouranteId = $utilisateur->unite_id;
        $types = $this->typesReaffectationAutorises($uniteCourante);

        // Accueil / Admin : toutes les unités (comportement CDC Direction / B.O.).
        if ($utilisateur->isAdmin() || $utilisateur->hasRole('Accueil') || ($uniteCourante?->est_bureau_ordre)) {
            $types = array_keys(Unite::NIVEAUX);
        }

        return Unite::query()
            ->actives()
            ->whereIn('type_unite', $types)
            ->when($uniteCouranteId, fn ($query) => $query->where('id', '!=', $uniteCouranteId))
            ->orderBy('type_unite')
            ->orderBy('nom')
            ->get();
    }

    private function assurerAppartenanceUnite(Courrier $courrier, User $sujet): void
    {
        if ($sujet->unite_id !== $courrier->unite_actuelle_id) {
            throw new InvalidArgumentException(
                'Seul un agent de l\'unité destinataire peut confirmer la réception de ce courrier.'
            );
        }
    }

    /** Seul l'agent qui a réceptionné le courrier (ou son remplaçant) agit dessus. */
    private function assurerResponsable(Courrier $courrier, User $sujet): void
    {
        if ($courrier->responsable_actuel_id !== $sujet->id) {
            throw new InvalidArgumentException('Seul l\'agent qui a réceptionné ce courrier peut effectuer cette action.');
        }
    }

    private function marquerReceptionne(Courrier $courrier, User $acteur, User $sujet, $maintenant): void
    {
        $unite = $sujet->unite;
        $uniteDestinataireId = $courrier->unite_destinataire_id;

        $courrier->update([
            'statut' => StatutCourrier::Receptionne,
            'date_reception' => $maintenant->toDateString(),
            'heure_reception' => $maintenant->format('H:i:s'),
            'responsable_reception_id' => $sujet->id,
            'responsable_actuel_id' => $sujet->id,
            'unite_actuelle_id' => $unite?->id ?? $courrier->unite_actuelle_id,
        ]);

        CourrierHistorique::create([
            ...$this->trace($acteur, $sujet),
            'courrier_id' => $courrier->id,
            'action' => ActionHistorique::Reception,
            'commentaire' => sprintf(
                'Réception confirmée — Unité : %s',
                $unite?->nom ?? 'non renseignée'
            ),
        ]);

        HistoriqueAffectation::create([
            ...$this->trace($acteur, $sujet),
            'courrier_id' => $courrier->id,
            'unite_source_id' => $uniteDestinataireId,
            'unite_destination_id' => $unite?->id,
            'ancien_responsable_id' => null,
            'nouveau_responsable_id' => $sujet->id,
            'action_type' => ActionType::Reception,
            'motif' => null,
            'commentaire' => 'Réception confirmée',
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
