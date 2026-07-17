<?php

namespace App\Providers;

use App\Models\User;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Admin gates
        Gate::define('manage-users', function (User $user) {
            return $user->isAdmin();
        });

        Gate::define('manage-roles', function (User $user) {
            return $user->isAdmin();
        });

        Gate::define('manage-categories', function (User $user) {
            return $user->isAdmin();
        });

        Gate::define('manage-types-courrier', function (User $user) {
            return $user->isAdmin();
        });

        Gate::define('manage-unites', function (User $user) {
            return $user->isAdmin();
        });

        Gate::define('manage-settings', function (User $user) {
            return $user->isAdmin();
        });

        // Courrier gates
        Gate::define('create-courrier', function (User $user) {
            return $user->role && $user->role->name === 'Direction Régionale';
        });

        Gate::define('view-courrier', function (User $user, $courrier) {
            if ($user->isAdmin()) {
                return true;
            }
            // Check if courrier is in user's unite hierarchy
            $userUnite = $user->unite;
            if (!$userUnite) {
                return false;
            }
            $hierarchyIds = $userUnite->hierarchy_ids;
            return in_array($courrier->unite_actuelle_id, $hierarchyIds);
        });
    }
}
