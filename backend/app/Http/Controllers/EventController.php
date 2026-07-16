<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\InteractsWithFamily;
use App\Http\Resources\EventResource;
use App\Models\Event;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Exists;

class EventController extends Controller
{
    use InteractsWithFamily;

    public function index(Request $request): AnonymousResourceCollection
    {
        $familyId = $this->familyId($request);

        return EventResource::collection(
            Event::where('family_id', $familyId)->with('owner')->orderBy('starts_at')->get()
        );
    }

    public function store(Request $request): JsonResponse
    {
        $familyId = $this->familyId($request);

        $data = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'starts_at' => ['required', 'date'],
            'ends_at' => ['required', 'date', 'after_or_equal:starts_at'],
            'category' => ['nullable', 'string', 'max:50'],
            'car_reserved' => ['nullable', 'boolean'],
            // Wiederholung (Serie): Vorkommen expandiert das Frontend.
            'recurrence' => ['nullable', 'in:daily,weekly,biweekly,monthly,yearly'],
            'recurrence_until' => ['nullable', 'date', 'after:starts_at'],
            // Owner muss ein Mitglied derselben Familie sein; Standard = Ersteller.
            'owner_id' => ['nullable', 'integer', $this->memberRule($familyId)],
        ]);

        $user = $request->user();
        // Kinder dürfen nur für sich selbst eintragen; Verwalter für jeden.
        $ownerId = $user->isGuardian() ? ($data['owner_id'] ?? $user->id) : $user->id;

        $event = Event::create([
            'family_id' => $familyId,
            'user_id' => $user->id,
            'owner_id' => $ownerId,
            'title' => $data['title'],
            'starts_at' => $data['starts_at'],
            'ends_at' => $data['ends_at'],
            'recurrence' => $data['recurrence'] ?? null,
            'recurrence_until' => $data['recurrence_until'] ?? null,
            'category' => $data['category'] ?? 'Sonstiges',
            'car_reserved' => $data['car_reserved'] ?? false,
        ]);

        return (new EventResource($event->load('owner')))->response()->setStatusCode(201);
    }

    public function update(Request $request, Event $event): EventResource
    {
        $this->authorize('update', $event);

        $data = $request->validate([
            'title' => ['sometimes', 'string', 'max:255'],
            'starts_at' => ['sometimes', 'date'],
            'ends_at' => ['sometimes', 'date', 'after_or_equal:starts_at'],
            'category' => ['sometimes', 'string', 'max:50'],
            'car_reserved' => ['sometimes', 'boolean'],
            'recurrence' => ['sometimes', 'nullable', 'in:daily,weekly,biweekly,monthly,yearly'],
            'recurrence_until' => ['sometimes', 'nullable', 'date'],
            'owner_id' => ['sometimes', 'integer', $this->memberRule($event->family_id)],
        ]);

        // Kinder dürfen den Owner nicht umhängen.
        if (! $request->user()->isGuardian()) {
            unset($data['owner_id']);
        }

        $event->update($data);

        return new EventResource($event->load('owner'));
    }

    /** Validierungsregel: ID gehört einem Mitglied der angegebenen Familie. */
    private function memberRule(int $familyId): Exists
    {
        return Rule::exists('users', 'id')->where('family_id', $familyId);
    }

    public function destroy(Request $request, Event $event): Response
    {
        $this->authorize('delete', $event);

        $event->delete();

        return response()->noContent();
    }
}
