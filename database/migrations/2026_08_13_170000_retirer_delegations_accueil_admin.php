<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Retire définitivement les permissions de délégation des comptes Accueil / Admin
 * (y compris permissions personnelles encore présentes en base).
 */
return new class extends Migration
{
    public function up(): void
    {
        $idsDelegation = DB::table('permissions')
            ->where('code', 'like', 'delegations.%')
            ->pluck('id');

        if ($idsDelegation->isEmpty()) {
            return;
        }

        $roleIds = DB::table('roles')
            ->whereIn('name', ['Accueil', 'Admin'])
            ->pluck('id');

        if ($roleIds->isNotEmpty()) {
            DB::table('permission_role')
                ->whereIn('role_id', $roleIds)
                ->whereIn('permission_id', $idsDelegation)
                ->delete();
        }

        $userIds = DB::table('users')
            ->whereIn('role_id', $roleIds->isEmpty() ? [-1] : $roleIds)
            ->pluck('id');

        // Profils additionnels (role_user)
        if (DB::getSchemaBuilder()->hasTable('role_user')) {
            $extraUserIds = DB::table('role_user')
                ->whereIn('role_id', $roleIds->isEmpty() ? [-1] : $roleIds)
                ->pluck('user_id');
            $userIds = $userIds->merge($extraUserIds)->unique()->values();
        }

        if ($userIds->isNotEmpty()) {
            DB::table('permission_user')
                ->whereIn('user_id', $userIds)
                ->whereIn('permission_id', $idsDelegation)
                ->delete();
        }
    }

    public function down(): void
    {
        // Irreversible volontairement : les délégations ne doivent pas revenir à Accueil/Admin.
    }
};
