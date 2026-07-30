<?php

namespace App\Policies;

use App\Models\Sale;
use App\Models\User;

class SalePolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, Sale $sale): bool
    {
        return true;
    }

    public function create(User $user): bool
    {
        return true;
    }

    /**
     * Managers and admins may correct any sale. A cashier may only correct
     * their own, and only on the day it was recorded — after that a
     * supervisor has to make the adjustment.
     */
    public function update(User $user, Sale $sale): bool
    {
        if ($user->role->managesCatalogue()) {
            return true;
        }

        return $sale->user_id === $user->id
            && $sale->created_at->isToday();
    }

    public function delete(User $user, Sale $sale): bool
    {
        return $user->role->canDelete();
    }

    public function restore(User $user, Sale $sale): bool
    {
        return $user->role->canDelete();
    }

    public function forceDelete(User $user, Sale $sale): bool
    {
        return $user->isAdmin();
    }
}
