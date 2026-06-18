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
            // Owner muss ein Mitglied derselben Familie sein; Standard = Ersteller.
            'owner_id' => ['nullable', 'integer', $this->memberRule($familyId)],
        ]);

        $event = Event::create([
            'family_id' => $familyId,
            'user_id' => $request->user()->id,
            'owner_id' => $data['owner_id'] ?? $request->user()->id,
            'title' => $data['title'],
            'starts_at' => $data['starts_at'],
            'ends_at' => $data['ends_at'],
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
            'owner_id' => ['sometimes', 'integer', $this->memberRule($event->family_id)],
        ]);

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
