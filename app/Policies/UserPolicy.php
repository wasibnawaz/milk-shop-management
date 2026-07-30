<?php

namespace App\Policies;

use App\Models\User;

/**
 * User administration is admin-only across the board.
 */
class UserPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->isAdmin();
    }

    public function view(User $user, User $model): bool
    {
        return $user->isAdmin();
    }

    public function create(User $user): bool
    {
        return $user->isAdmin();
    }

    public function update(User $user, User $model): bool
    {
        return $user->isAdmin();
    }

    public function delete(User $user, User $model): bool
    {
        // Never allow deleting yourself through the admin screen; the
        // controller repeats this check with a friendlier message.
        return $user->isAdmin() && ! $user->is($model);
    }
}
