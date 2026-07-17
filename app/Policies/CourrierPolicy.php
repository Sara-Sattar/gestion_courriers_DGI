<?php

namespace App\Policies;

use App\Models\Courrier;
use App\Models\User;
use Illuminate\Auth\Access\Response;

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
        return $user->role && $user->role->name === 'Direction Régionale';
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
