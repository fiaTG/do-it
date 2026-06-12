<?php

namespace App\Policies;

use App\Models\Todo;
use App\Models\User;
use App\Policies\Concerns\ChecksFamilyMembership;

class TodoPolicy
{
    use ChecksFamilyMembership;

    /**
     * Löschen darf nur der Ersteller (wie im Original über usertodo abgebildet):
     * Familienmitglieder können die Aufgaben anderer nicht löschen.
     */
    public function delete(User $user, Todo $todo): bool
    {
        return $this->sameFamily($user, $todo) && (int) $todo->user_id === (int) $user->id;
    }
}
