<?php

namespace App\Policies;

use App\Models\User;
use App\Policies\Concerns\ChecksFamilyMembership;
use Illuminate\Database\Eloquent\Model;

class EventPolicy
{
    use ChecksFamilyMembership;

    public function update(User $user, Model $model): bool
    {
        return $this->sameFamily($user, $model) && $this->canManage($user, $model);
    }

    public function delete(User $user, Model $model): bool
    {
        return $this->sameFamily($user, $model) && $this->canManage($user, $model);
    }

    /**
     * Verwalter dürfen alle Familientermine. Kinder nur Termine, die sie
     * SELBST angelegt haben (Owner-Schutz, ADR-0021): ein vom Verwalter für
     * das Kind angelegter Termin (owner = Kind, creator = Verwalter) ist für
     * das Kind nur lesbar – der Zahnarzttermin lässt sich nicht weglöschen.
     */
    private function canManage(User $user, Model $model): bool
    {
        return $user->isGuardian()
            || ((int) $model->owner_id === (int) $user->id && (int) $model->user_id === (int) $user->id);
    }
}
