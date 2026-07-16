<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\InteractsWithFamily;
use App\Http\Resources\ContactResource;
use App\Models\Contact;
use App\Support\ImageUpload;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Storage;

class ContactController extends Controller
{
    use InteractsWithFamily;

    public function index(Request $request): AnonymousResourceCollection
    {
        $familyId = $this->familyId($request);

        return ContactResource::collection(
            Contact::where('family_id', $familyId)->orderBy('name')->get()
        );
    }

    public function store(Request $request): JsonResponse
    {
        $familyId = $this->familyId($request);
        $data = $this->validated($request);

        $contact = Contact::create([
            ...$data,
            'family_id' => $familyId,
            'user_id' => $request->user()->id,
            'photo_path' => $this->storePhoto($request, $familyId),
        ]);

        return (new ContactResource($contact))->response()->setStatusCode(201);
    }

    public function update(Request $request, Contact $contact): ContactResource
    {
        $this->authorize('update', $contact);

        $data = $this->validated($request);

        // Review M-01: neues Foto zuerst speichern + verlinken, altes erst
        // danach löschen – kein Bildverlust bei fehlgeschlagener Verarbeitung.
        $old = null;
        if ($request->hasFile('photo')) {
            $old = $contact->photo_path;
            $data['photo_path'] = $this->storePhoto($request, (int) $contact->family_id);
        }

        $contact->update($data);

        if ($old) {
            Storage::disk(config('filesystems.media'))->delete($old);
        }

        return new ContactResource($contact->fresh());
    }

    public function destroy(Request $request, Contact $contact): Response
    {
        $this->authorize('delete', $contact);

        if ($contact->photo_path) {
            Storage::disk(config('filesystems.media'))->delete($contact->photo_path);
        }
        $contact->delete();

        return response()->noContent();
    }

    /**
     * @return array<string, mixed>
     */
    private function validated(Request $request): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'category' => ['nullable', 'string', 'max:100'],
            'phone' => ['nullable', 'string', 'max:50'],
            'email' => ['nullable', 'email', 'max:255'],
            'website' => ['nullable', 'url:http,https', 'max:255'],
            'address' => ['nullable', 'string', 'max:1000'],
            'notes' => ['nullable', 'string', 'max:2000'],
            'photo' => ['nullable', 'image', 'max:5120', 'dimensions:max_width=8000,max_height=8000'],
        ]);
    }

    private function storePhoto(Request $request, int $familyId): ?string
    {
        if (! $request->hasFile('photo')) {
            return null;
        }

        // EXIF-Strip wie überall (ADR-0015); Maße/Datum braucht das Adressbuch nicht.
        return ImageUpload::storeStripped($request->file('photo'), "contacts/{$familyId}")['path'];
    }
}
