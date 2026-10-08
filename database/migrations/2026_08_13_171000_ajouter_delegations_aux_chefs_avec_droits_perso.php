<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Ajoute les permissions de délégation aux utilisateurs chefs qui ont
 * des droits personnels (ceux-ci masquent sinon le profil Bureau/Service…).
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
            ->whereIn('name', ['Service', 'Brigade', 'Bureau', 'Section', 'Secteur'])
            ->pluck('id');

        if ($roleIds->isEmpty()) {
            return;
        }

        $userIds = DB::table('users')
            ->whereIn('role_id', $roleIds)
            ->whereIn('id', function ($q) {
                $q->select('user_id')->from('permission_user');
            })
            ->pluck('id');

        foreach ($userIds as $userId) {
            foreach ($idsDelegation as $permissionId) {
                $exists = DB::table('permission_user')
                    ->where('user_id', $userId)
                    ->where('permission_id', $permissionId)
                    ->exists();

                if (! $exists) {
                    DB::table('permission_user')->insert([
                        'user_id' => $userId,
                        'permission_id' => $permissionId,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }
            }
        }
    }

    public function down(): void
    {
        //
    }
};
