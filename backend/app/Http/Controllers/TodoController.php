<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\InteractsWithFamily;
use App\Http\Resources\TodoResource;
use App\Models\Todo;
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

        $todo->update($data);

        return new TodoResource($todo);
    }

    public function destroy(Request $request, Todo $todo): Response
    {
        $this->authorize('delete', $todo);

        $todo->delete();

        return response()->noContent();
    }
}
