<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\InteractsWithFamily;
use App\Http\Resources\TodoResource;
use App\Models\Todo;
use App\Models\TodoPoint;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;

class TodoController extends Controller
{
    use InteractsWithFamily;

    public function index(Request $request): AnonymousResourceCollection
    {
        $familyId = $this->familyId($request);

        return TodoResource::collection(
            Todo::where('family_id', $familyId)->latest()->get()
        );
    }

    public function store(Request $request): JsonResponse
    {
        $familyId = $this->familyId($request);

        $data = $request->validate([
            'title' => ['required', 'string', 'max:255'],
        ]);

        $todo = Todo::create([
            'family_id' => $familyId,
            'user_id' => $request->user()->id,
            'title' => $data['title'],
            'is_done' => false,
        ]);

        return (new TodoResource($todo))->response()->setStatusCode(201);
    }

    public function update(Request $request, Todo $todo): TodoResource
    {
        $this->authorize('update', $todo);

        $data = $request->validate([
            'title' => ['sometimes', 'string', 'max:255'],
            'is_done' => ['sometimes', 'boolean'],
        ]);

        // Nest-Blätter (ADR-0026): Abhaken schreibt einen Punkt ins Ledger
        // (1 Blatt je ToDo – Timos Entscheidung), Zurücknehmen entfernt ihn.
        if (array_key_exists('is_done', $data) && $data['is_done'] !== $todo->is_done) {
            if ($data['is_done']) {
                $data['completed_by'] = $request->user()->id;
                $data['completed_at'] = now();
                TodoPoint::create([
                    'family_id' => $todo->family_id,
                    'user_id' => $request->user()->id,
                    'todo_id' => $todo->id,
                ]);
            } else {
                $data['completed_by'] = null;
                $data['completed_at'] = null;
                TodoPoint::where('todo_id', $todo->id)->delete();
            }
        }

        $todo->update($data);

        return new TodoResource($todo);
    }

    /**
     * Nest-Blätter-Stände der Familie: Woche (ab Montag) + Gesamt je Mitglied.
     * Abzeichen-Schwellen rechnet das Frontend aus den Gesamtwerten.
     */
    public function points(Request $request): JsonResponse
    {
        $familyId = $this->familyId($request);

        $totals = TodoPoint::where('family_id', $familyId)
            ->selectRaw('user_id, SUM(points) as points')
            ->groupBy('user_id')->pluck('points', 'user_id');
        $week = TodoPoint::where('family_id', $familyId)
            ->where('created_at', '>=', now()->startOfWeek())
            ->selectRaw('user_id, SUM(points) as points')
            ->groupBy('user_id')->pluck('points', 'user_id');

        return response()->json(['data' => [
            'week' => $week->map(fn ($p) => (int) $p),
            'totals' => $totals->map(fn ($p) => (int) $p),
        ]]);
    }

    public function destroy(Request $request, Todo $todo): Response
    {
        $this->authorize('delete', $todo);

        $todo->delete();

        return response()->noContent();
    }
}
