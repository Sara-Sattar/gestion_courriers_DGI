<?php

namespace App\Policies;

use App\Models\Courrier;
use App\Models\User;

class CourrierPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return true;
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Courrier $courrier): bool
    {
        if ($user->isAdmin()) {
            return true;
        }

        // L'agent qui a enregistré le courrier peut toujours consulter le dossier / reçu.
        if ($courrier->user_id === $user->id) {
            return true;
        }

        // L'agent (ou le sujet en délégation) qui a traité le dossier conserve l'accès
        // après retour à l'Accueil (historique de mes traitements).
        $sujetId = app(\App\Services\DelegationContext::class)->sujet()?->id ?? $user->id;
        if ($courrier->traite_par_id !== null
            && in_array($courrier->traite_par_id, [$user->id, $sujetId], true)) {
            return true;
        }

        $userUnite = $user->unite;
        if (!$userUnite) {
            return false;
        }

        $hierarchyIds = $userUnite->hierarchy_ids;
        return in_array($courrier->unite_actuelle_id, $hierarchyIds);
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->hasPermission('courriers.creer');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Courrier $courrier): bool
    {
        return $this->view($user, $courrier);
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Courrier $courrier): bool
    {
        return $this->view($user, $courrier);
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Courrier $courrier): bool
    {
        return $this->view($user, $courrier);
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Courrier $courrier): bool
    {
        return $this->view($user, $courrier);
    }
}
