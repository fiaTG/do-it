<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\InteractsWithFamily;
use App\Http\Resources\FamilyResource;
use App\Http\Resources\UserResource;
use App\Models\Family;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class FamilyController extends Controller
{
    use InteractsWithFamily;

    /**
     * Familie gründen – der eingeloggte Nutzer (ohne Familie) wird Mitglied.
     */
    public function store(Request $request): JsonResponse
    {
        $user = $request->user();

        abort_if($user->family_id !== null, 409, 'Du gehörst bereits einer Familie an.');

        $data = $request->validate(['name' => ['required', 'string', 'max:255']]);

        $family = Family::create($data);
        $user->update(['family_id' => $family->id]);

        return (new FamilyResource($family))->response()->setStatusCode(201);
    }

    /**
     * Mitglieder der eigenen Familie auflisten.
     */
    public function members(Request $request): AnonymousResourceCollection
    {
        $familyId = $this->familyId($request);

        return UserResource::collection(
            User::where('family_id', $familyId)->orderBy('first_name')->get()
        );
    }

    /**
     * Heimatort der Familie setzen (nur Verwalter) – Koordinaten kommen aus der
     * Ortssuche im Frontend (Open-Meteo Geocoding), das Wetter-Widget liest sie.
     */
    public function updateLocation(Request $request): FamilyResource
    {
        $user = $request->user();
        $this->familyId($request);

        abort_unless($user->isGuardian(), 403, 'Nur Verwalter dürfen den Familienort ändern.');

        $data = $request->validate([
            'location_name' => ['required', 'string', 'max:255'],
            'latitude' => ['required', 'numeric', 'between:-90,90'],
            'longitude' => ['required', 'numeric', 'between:-180,180'],
        ]);

        $user->family->update($data);

        return new FamilyResource($user->family->fresh());
    }

    /**
     * Rolle eines Familienmitglieds setzen (nur Verwalter, nicht die eigene).
     */
    public function updateRole(Request $request, User $member): UserResource
    {
        $actor = $request->user();

        abort_unless($actor->isGuardian(), 403, 'Nur Verwalter dürfen Rollen ändern.');
        abort_unless(
            $member->family_id !== null && (int) $member->family_id === (int) $actor->family_id,
            403,
        );
        abort_if((int) $member->id === (int) $actor->id, 422, 'Die eigene Rolle lässt sich nicht ändern.');

        $data = $request->validate(['role' => ['required', 'in:guardian,child']]);

        // Review M-03: Eine Familie braucht immer mindestens einen Verwalter –
        // der letzte Guardian kann nicht herabgestuft werden.
        if ($data['role'] === 'child' && $member->isGuardian()) {
            $otherGuardians = User::where('family_id', $member->family_id)
                ->where('id', '!=', $member->id)
                ->where('role', 'guardian')
                ->count();
            abort_if(
                $otherGuardians === 0,
                422,
                'Der letzte Verwalter einer Familie kann nicht zum Kind gemacht werden.',
            );
        }

        $member->update(['role' => $data['role']]);

        return new UserResource($member);
    }
}
