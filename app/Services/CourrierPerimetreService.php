<?php

namespace App\Services;

use App\Enums\StatutCourrier;
use App\Models\Courrier;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;

/**
 * Périmètre des courriers tel qu'affiché sur la page Accueil (tableau de bord).
 */
class CourrierPerimetreService
{
    /**
     * @return list<StatutCourrier>
     */
    public static function statutsEntrants(): array
    {
        return [
            StatutCourrier::Depose,
            StatutCourrier::Reaffecte,
            StatutCourrier::Receptionne,
            StatutCourrier::Suspendu,
        ];
    }

    /**
     * @return list<StatutCourrier>
     */
    public static function statutsSortants(): array
    {
        return [
            StatutCourrier::TraiteFavorable,
            StatutCourrier::Rejete,
            StatutCourrier::Cloture,
            StatutCourrier::Archive,
        ];
    }

    /**
     * Même requête que les cartes Total / Entrants / Sortants / Aujourd'hui / Ce mois.
     */
    public function requeteTableauDeBord(?User $sujet): Builder
    {
        $query = Courrier::query();
        $unite = $sujet?->unite;
        $hierarchyIds = $unite ? $unite->hierarchy_ids : [];
        $uniteId = $unite?->id;
        $unitesFillesIds = $uniteId
            ? array_values(array_diff($hierarchyIds, [$uniteId]))
            : [];

        if ($sujet && $hierarchyIds !== []) {
            $query->where(function ($q) use ($sujet, $hierarchyIds, $unitesFillesIds) {
                $q->where(function ($attente) use ($hierarchyIds) {
                    $attente->whereIn('statut', [StatutCourrier::Depose, StatutCourrier::Reaffecte])
                        ->whereIn('unite_actuelle_id', $hierarchyIds);
                })
                    ->orWhere('responsable_actuel_id', $sujet->id)
                    // Le déposant garde ses courriers dans sa liste, quelle que soit l'unité destinataire.
                    ->orWhere('user_id', $sujet->id);

                if ($unitesFillesIds !== []) {
                    $q->orWhereIn('unite_actuelle_id', $unitesFillesIds);
                }
            });
        }

        return $query;
    }
}
