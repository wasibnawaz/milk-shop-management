<?php

namespace App\Policies;

use App\Models\Dealer;
use App\Models\User;

class DealerPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, Dealer $dealer): bool
    {
        return true;
    }

    public function create(User $user): bool
    {
        return $user->role->managesCatalogue();
    }

    public function update(User $user, Dealer $dealer): bool
    {
        return $user->role->managesCatalogue();
    }

    public function delete(User $user, Dealer $dealer): bool
    {
        return $user->role->managesCatalogue();
    }
}
