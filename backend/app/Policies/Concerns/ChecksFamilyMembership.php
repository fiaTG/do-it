<?php

namespace App\Policies\Concerns;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;

/**
 * Gemeinsame Autorisierungslogik für familiengebundene Ressourcen:
 * Ein Nutzer darf eine Ressource nur sehen/ändern/löschen, wenn sie zu
 * seiner eigenen Familie gehört (ADR-0008).
 */
trait ChecksFamilyMembership
{
    public function view(User $user, Model $model): bool
    {
        return $this->sameFamily($user, $model);
    }

    public function update(User $user, Model $model): bool
    {
        return $this->sameFamily($user, $model);
    }

    public function delete(User $user, Model $model): bool
    {
        return $this->sameFamily($user, $model);
    }

    protected function sameFamily(User $user, Model $model): bool
    {
        return $user->family_id !== null
            && (int) $user->family_id === (int) $model->family_id;
    }
}
