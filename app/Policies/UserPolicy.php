<?php

namespace App\Policies;

use App\Models\User;

class UserPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasPermission('users.gerer');
    }

    public function view(User $user, User $model): bool
    {
        return $user->hasPermission('users.gerer');
    }

    public function create(User $user): bool
    {
        return $user->hasPermission('users.gerer');
    }

    public function update(User $user, User $model): bool
    {
        return $user->hasPermission('users.gerer');
    }

    public function delete(User $user, User $model): bool
    {
        return $user->hasPermission('users.gerer');
    }

    public function restore(User $user, User $model): bool
    {
        return $user->hasPermission('users.gerer');
    }

    public function forceDelete(User $user, User $model): bool
    {
        return $user->hasPermission('users.gerer');
    }
}
