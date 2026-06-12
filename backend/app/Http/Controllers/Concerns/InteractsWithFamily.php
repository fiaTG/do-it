<?php

namespace App\Http\Controllers\Concerns;

use Illuminate\Http\Request;

trait InteractsWithFamily
{
    /**
     * Liefert die family_id des eingeloggten Nutzers oder bricht mit 409 ab,
     * wenn der Nutzer noch keiner Familie angehört.
     */
    protected function familyId(Request $request): int
    {
        $id = $request->user()->family_id;

        abort_if($id === null, 409, 'Du gehörst noch keiner Familie an.');

        return (int) $id;
    }
}
