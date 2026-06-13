<?php

namespace App\Http\Controllers;

use App\Models\Subscription;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class SubscriptionController extends Controller
{
    /**
     * Aktueller Abo-Status der Familie.
     */
    public function show(Request $request): JsonResponse
    {
        return response()->json(['data' => $this->state($request)]);
    }

    /**
     * Premium aktivieren. Aktuell „manueller" Provider als Platzhalter – später
     * wird hier ein verifizierter Store-Kauf (Apple/Google) bzw. Stripe-Webhook
     * eingelöst (ADR-0013).
     */
    public function store(Request $request): JsonResponse
    {
        $family = $request->user()->family;
        abort_if($family === null, 409, 'Du gehörst noch keiner Familie an.');

        Subscription::updateOrCreate(
            ['family_id' => $family->id],
            [
                'plan' => 'premium',
                'status' => 'active',
                'provider' => 'manual',
                'expires_at' => now()->addMonth(),
            ],
        );
        $family->unsetRelation('subscription');

        return response()->json(['data' => $this->state($request)], 201);
    }

    /**
     * Premium kündigen.
     */
    public function destroy(Request $request): Response
    {
        $family = $request->user()->family;

        if ($family !== null) {
            Subscription::where('family_id', $family->id)->update(['status' => 'canceled']);
            $family->unsetRelation('subscription');
        }

        return response()->noContent();
    }

    /**
     * @return array<string, mixed>
     */
    private function state(Request $request): array
    {
        $family = $request->user()->family;
        $premium = (bool) $family?->isPremium();

        return [
            'is_premium' => $premium,
            'plan' => $premium ? 'premium' : 'free',
            'expires_at' => $family?->subscription?->expires_at?->toIso8601String(),
        ];
    }
}
