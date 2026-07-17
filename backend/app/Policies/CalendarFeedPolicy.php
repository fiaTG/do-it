<?php

namespace App\Policies;

use App\Models\CalendarFeed;
use App\Models\User;

/**
 * Kalender-Abos (ADR-0023): alle Familienmitglieder sehen die Termine,
 * verwalten (anlegen/aktualisieren/löschen) dürfen nur Verwalter – Abos
 * betreffen den Kalender der ganzen Familie.
 */
class CalendarFeedPolicy
{
    public function update(User $user, CalendarFeed $feed): bool
    {
        return $this->canManage($user, $feed);
    }

    public function delete(User $user, CalendarFeed $feed): bool
    {
        return $this->canManage($user, $feed);
    }

    private function canManage(User $user, CalendarFeed $feed): bool
    {
        return $user->isGuardian()
            && $user->family_id !== null
            && (int) $user->family_id === (int) $feed->family_id;
    }
}
