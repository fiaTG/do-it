<?php

namespace App\Policies;

use App\Models\ShoppingItem;
use App\Models\User;
use App\Policies\Concerns\ChecksFamilyMembership;

class ShoppingItemPolicy
{
    use ChecksFamilyMembership;

    /**
     * Löschen darf nur der Ersteller (wie im Original über useritems abgebildet).
     * Abhaken/Bearbeiten bleibt der ganzen Familie erlaubt (gemeinsame Liste).
     */
    public function delete(User $user, ShoppingItem $item): bool
    {
        return $this->sameFamily($user, $item) && (int) $item->user_id === (int) $user->id;
    }
}
