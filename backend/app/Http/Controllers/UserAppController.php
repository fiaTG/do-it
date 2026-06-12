<?php

namespace App\Http\Controllers;

use App\Http\Resources\AppResource;
use App\Models\App;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;

/**
 * Verwaltung der Dashboard-Apps eines Nutzers (vormals userapps).
 */
class UserAppController extends Controller
{
    /**
     * Gesamter App-Katalog.
     */
    public function index(): AnonymousResourceCollection
    {
        return AppResource::collection(App::orderBy('id')->get());
    }

    /**
     * Die Apps des eingeloggten Nutzers.
     */
    public function mine(Request $request): AnonymousResourceCollection
    {
        return AppResource::collection(
            $request->user()->apps()->orderBy('apps.id')->get()
        );
    }

    /**
     * App zum Dashboard hinzufügen.
     */
    public function store(Request $request): Response
    {
        $data = $request->validate(['app_id' => ['required', 'integer', 'exists:apps,id']]);

        $request->user()->apps()->syncWithoutDetaching([$data['app_id']]);

        return response()->noContent();
    }

    /**
     * App vom Dashboard entfernen.
     */
    public function destroy(Request $request, App $app): Response
    {
        $request->user()->apps()->detach($app->id);

        return response()->noContent();
    }
}
