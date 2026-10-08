<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Remplace delegations.supprimer par delegations.annuler (CDC)
 * et retire les droits délégation aux profils Accueil / Admin.
 */
return new class extends Migration
{
    public function up(): void
    {
        $supprimer = DB::table('permissions')->where('code', 'delegations.supprimer')->first();
        $annulerId = DB::table('permissions')->where('code', 'delegations.annuler')->value('id');

        if ($supprimer && ! $annulerId) {
            DB::table('permissions')->where('id', $supprimer->id)->update([
                'code' => 'delegations.annuler',
                'label' => 'Annuler une délégation',
                'updated_at' => now(),
            ]);
            $annulerId = $supprimer->id;
        } elseif (! $annulerId) {
            $annulerId = DB::table('permissions')->insertGetId([
                'code' => 'delegations.annuler',
                'label' => 'Annuler une délégation',
                'groupe' => 'delegations',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        if ($supprimer && $annulerId && (int) $supprimer->id !== (int) $annulerId) {
            DB::table('permission_role')
                ->where('permission_id', $supprimer->id)
                ->update(['permission_id' => $annulerId]);
            DB::table('permission_user')
                ->where('permission_id', $supprimer->id)
                ->update(['permission_id' => $annulerId]);
            DB::table('permissions')->where('id', $supprimer->id)->delete();
        }

        $codesDelegation = ['delegations.voir', 'delegations.creer', 'delegations.modifier', 'delegations.annuler'];
        $permissionIds = DB::table('permissions')->whereIn('code', $codesDelegation)->pluck('id');

        $rolesSansDelegation = DB::table('roles')->whereIn('name', ['Admin', 'Accueil'])->pluck('id');
        if ($rolesSansDelegation->isNotEmpty() && $permissionIds->isNotEmpty()) {
            DB::table('permission_role')
                ->whereIn('role_id', $rolesSansDelegation)
                ->whereIn('permission_id', $permissionIds)
                ->delete();
        }

        $rolesChefs = DB::table('roles')
            ->whereIn('name', ['Service', 'Brigade', 'Bureau', 'Section', 'Secteur'])
            ->pluck('id');

        foreach ($rolesChefs as $roleId) {
            foreach ($permissionIds as $permissionId) {
                $exists = DB::table('permission_role')
                    ->where('role_id', $roleId)
                    ->where('permission_id', $permissionId)
                    ->exists();
                if (! $exists) {
                    DB::table('permission_role')->insert([
                        'role_id' => $roleId,
                        'permission_id' => $permissionId,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }
            }
        }

        // Statuts : les actives futures deviennent planifiées
        DB::table('delegations')
            ->where('statut', 'active')
            ->whereDate('date_debut', '>', now()->toDateString())
            ->update(['statut' => 'planifiee', 'updated_at' => now()]);
    }

    public function down(): void
    {
        $annuler = DB::table('permissions')->where('code', 'delegations.annuler')->first();
        if ($annuler) {
            DB::table('permissions')->where('id', $annuler->id)->update([
                'code' => 'delegations.supprimer',
                'label' => 'Supprimer une délégation',
                'updated_at' => now(),
            ]);
        }

        DB::table('delegations')
            ->where('statut', 'planifiee')
            ->update(['statut' => 'active', 'updated_at' => now()]);

        DB::table('delegations')
            ->where('statut', 'annulee')
            ->update(['statut' => 'terminee', 'updated_at' => now()]);
    }
};
