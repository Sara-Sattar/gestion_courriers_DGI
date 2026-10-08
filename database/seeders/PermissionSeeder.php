<?php

namespace Database\Seeders;

use App\Models\Permission;
use App\Models\Role;
use Illuminate\Database\Seeder;

class PermissionSeeder extends Seeder
{
    public function run(): void
    {
        $catalogue = config('permissions.permissions', []);
        $parProfil = config('permissions.par_profil', []);

        $permissionIds = [];

        foreach ($catalogue as $permission) {
            $modele = Permission::query()->updateOrCreate(
                ['code' => $permission['code']],
                [
                    'label' => $permission['label'],
                    'groupe' => $permission['groupe'],
                ]
            );
            $permissionIds[$permission['code']] = $modele->id;
        }

        foreach ($parProfil as $nomProfil => $codes) {
            $role = Role::query()->where('name', $nomProfil)->first();

            if (! $role) {
                continue;
            }

            $ids = collect($codes)
                ->map(fn (string $code) => $permissionIds[$code] ?? null)
                ->filter()
                ->values()
                ->all();

            $role->permissions()->sync($ids);
        }

        // CDC : Accueil et Admin n'ont aucune permission de délégation métier
        // (ni sur le profil, ni en permissions personnelles héritées d'anciens seeds).
        $idsDelegation = collect($permissionIds)
            ->filter(fn ($id, $code) => str_starts_with((string) $code, 'delegations.'))
            ->values()
            ->all();

        if ($idsDelegation !== []) {
            \App\Models\User::query()
                ->where(function ($q) {
                    $q->whereHas('role', fn ($r) => $r->whereIn('name', ['Accueil', 'Admin']))
                        ->orWhereHas('roles', fn ($r) => $r->whereIn('name', ['Accueil', 'Admin']));
                })
                ->each(function (\App\Models\User $user) use ($idsDelegation) {
                    $user->permissions()->detach($idsDelegation);
                });

            // Responsables hiérarchiques : si des droits personnels existent, ils
            // remplacent le profil — on y rattache donc les permissions de délégation.
            $rolesChefs = ['Service', 'Brigade', 'Bureau', 'Section', 'Secteur'];
            \App\Models\User::query()
                ->whereHas('role', fn ($r) => $r->whereIn('name', $rolesChefs))
                ->whereHas('permissions')
                ->each(function (\App\Models\User $user) use ($idsDelegation) {
                    $user->permissions()->syncWithoutDetaching($idsDelegation);
                });
        }

        // Les permissions personnelles remplacent le profil : compléter les droits
        // manquants du profil Accueil (ex. délivrance) SANS réintroduire réception,
        // traitement, réaffectation ni délégations.
        $codesAccueil = $parProfil['Accueil'] ?? [];
        $codesInterditsAccueil = [
            'menu.reception',
            'menu.traitement',
            'courriers.receptionner',
            'courriers.annuler_reception',
            'courriers.reaffecter',
            'courriers.refuser_reception',
        ];
        $idsInterditsAccueil = collect($codesInterditsAccueil)
            ->map(fn (string $code) => $permissionIds[$code] ?? null)
            ->filter()
            ->values()
            ->all();

        if ($codesAccueil !== []) {
            $idsAccueil = collect($codesAccueil)
                ->reject(fn (string $code) => str_starts_with($code, 'delegations.'))
                ->map(fn (string $code) => $permissionIds[$code] ?? null)
                ->filter()
                ->values()
                ->all();

            \App\Models\User::query()
                ->whereHas('role', fn ($q) => $q->where('name', 'Accueil'))
                ->whereHas('permissions')
                ->each(function (\App\Models\User $user) use ($idsAccueil, $idsDelegation, $idsInterditsAccueil) {
                    $user->permissions()->syncWithoutDetaching($idsAccueil);
                    if ($idsDelegation !== []) {
                        $user->permissions()->detach($idsDelegation);
                    }
                    if ($idsInterditsAccueil !== []) {
                        $user->permissions()->detach($idsInterditsAccueil);
                    }
                });
        }

        // CDC : Accueil n'a jamais réception / traitement (profil + permissions personnelles).
        if ($idsInterditsAccueil !== []) {
            Role::query()
                ->where('name', 'Accueil')
                ->first()
                ?->permissions()
                ->detach($idsInterditsAccueil);

            \App\Models\User::query()
                ->whereHas('role', fn ($q) => $q->where('name', 'Accueil'))
                ->each(function (\App\Models\User $user) use ($idsInterditsAccueil) {
                    $user->permissions()->detach($idsInterditsAccueil);
                });
        }

        // Retire les permissions obsolètes du catalogue (sans toucher aux utilisateurs).
        $codesValides = collect($catalogue)->pluck('code')->all();
        Permission::query()->whereNotIn('code', $codesValides)->delete();
    }
}
