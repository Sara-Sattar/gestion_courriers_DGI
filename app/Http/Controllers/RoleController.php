<?php

namespace App\Http\Controllers;

use App\Models\Role;
use Illuminate\Http\JsonResponse;

class RoleController extends Controller
{
    /**
     * Droits du profil (JSON) pour préremplir le formulaire utilisateur.
     * Les droits se gèrent sur chaque utilisateur ; le profil sert de modèle.
     */
    public function permissionsJson(Role $role): JsonResponse
    {
        $this->authorize('users.gerer');

        return response()->json([
            'role' => $role->name,
            'permission_ids' => $role->permissions()->pluck('permissions.id'),
        ]);
    }
}
