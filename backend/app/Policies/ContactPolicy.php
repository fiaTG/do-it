<?php

namespace App\Policies;

use App\Models\Contact;
use App\Models\User;

/**
 * Adressbuch: alle Familienmitglieder sehen und erstellen; bearbeiten/löschen
 * dürfen der Ersteller und Verwalter (Timos Vorgabe 2026-07-15).
 */
class ContactPolicy
{
    public function view(User $user, Contact $contact): bool
    {
        return $this->sameFamily($user, $contact);
    }

    public function update(User $user, Contact $contact): bool
    {
        return $this->canManage($user, $contact);
    }

    public function delete(User $user, Contact $contact): bool
    {
        return $this->canManage($user, $contact);
    }

    private function canManage(User $user, Contact $contact): bool
    {
        return $this->sameFamily($user, $contact)
            && ($user->isGuardian() || (int) $contact->user_id === (int) $user->id);
    }

    private function sameFamily(User $user, Contact $contact): bool
    {
        return $user->family_id !== null
            && (int) $user->family_id === (int) $contact->family_id;
    }
}
