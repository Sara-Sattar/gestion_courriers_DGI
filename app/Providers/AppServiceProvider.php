<?php

namespace App\Providers;

use App\Models\User;
use App\Services\DelegationContext;
use App\Services\DelegationService;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;
use Illuminate\Validation\Rules\Password;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Alias historiques → codes de permission.
     *
     * @var array<string, string>
     */
    private const GATE_ALIASES = [
        'manage-users' => 'users.gerer',
        'manage-roles' => 'users.gerer',
        'manage-categories' => 'categories.gerer',
        'manage-types-courrier' => 'types_courrier.gerer',
        'manage-unites' => 'unites.gerer',
        'manage-settings' => 'users.gerer',
        'create-courrier' => 'courriers.creer',
    ];

    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        Paginator::useBootstrapFive();

        Password::defaults(fn () => Password::min(8)
            ->mixedCase()
            ->numbers()
            ->symbols());

        $this->enregistrerGatesPermissions();

        View::composer('layouts.*', function ($view) {
            if ($user = auth()->guard('web')->user()) {
                $user->loadMissing(['role.permissions', 'permissions']);

                /** @var DelegationContext $context */
                $context = app(DelegationContext::class);
                $view->with([
                    'delegationContext' => $context,
                    'delegationsActivesRemplacant' => $context->delegationsActivesPourRemplacant($user),
                    'espaceEnRemplacement' => $context->enRemplacement(),
                    'libelleEspaceActif' => $context->libelleEspaceActif(),
                    'doitChoisirEspaceDelegation' => $context->doitChoisirEspace(),
                ]);
            }
        });

        Gate::define('view-courrier', function (User $user, $courrier) {
            if ($user->isAdmin()) {
                return true;
            }

            $userUnite = $user->unite;
            if (! $userUnite) {
                return false;
            }

            return in_array($courrier->unite_actuelle_id, $userUnite->hierarchy_ids, true);
        });
    }

    private function enregistrerGatesPermissions(): void
    {
        $definir = function (string $ability, string $permissionCode): void {
            Gate::define($ability, fn (User $user) => $user->hasPermission($permissionCode));
        };

        foreach (self::GATE_ALIASES as $alias => $code) {
            $definir($alias, $code);
        }

        // Définition depuis le catalogue (pas de requête DB au boot).
        foreach (config('permissions.permissions', []) as $permission) {
            $definir($permission['code'], $permission['code']);
        }

        // Délégations : permission + être le responsable hiérarchique de son unité.
        // Empêche un agent (même profil Bureau/Section) d'accéder au menu de gestion.
        foreach (['delegations.voir', 'delegations.creer', 'delegations.modifier', 'delegations.annuler'] as $code) {
            Gate::define($code, function (User $user) use ($code) {
                if (! $user->hasPermission($code)) {
                    return false;
                }

                return app(DelegationService::class)->estResponsableHierarchique($user);
            });
        }

        // CDC : Accueil = dépôt / délivrance uniquement (pas réception ni traitement).
        $codesInterditsAccueil = [
            'menu.reception',
            'menu.traitement',
            'courriers.receptionner',
            'courriers.annuler_reception',
            'courriers.reaffecter',
            'courriers.refuser_reception',
        ];

        foreach ($codesInterditsAccueil as $code) {
            Gate::define($code, function (User $user) use ($code) {
                if ($user->hasRole('Accueil')) {
                    return false;
                }

                return $user->hasPermission($code);
            });
        }
    }
}
