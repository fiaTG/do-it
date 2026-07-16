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
     * Premium aktivieren (Monats- oder Jahresplan, ADR-0022). Aktuell
     * „manueller" Provider als Platzhalter – später löst hier der
     * RevenueCat-Webhook den verifizierten Store-/Stripe-Kauf ein.
     */
    public function store(Request $request): JsonResponse
    {
        $family = $request->user()->family;
        abort_if($family === null, 409, 'Du gehörst noch keiner Familie an.');
        // Review C-01: Familienweiter Vertragszustand ist Verwalter-Sache.
        abort_unless($request->user()->isGuardian(), 403, 'Nur Verwalter können das Abo verwalten.');

        $plan = $request->validate([
            'plan' => ['nullable', 'in:monthly,yearly'],
        ])['plan'] ?? 'monthly';

        Subscription::updateOrCreate(
            ['family_id' => $family->id],
            [
                'plan' => $plan,
                'status' => 'active',
                'provider' => 'manual',
                'expires_at' => $plan === 'yearly' ? now()->addYear() : now()->addMonth(),
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
        abort_unless($request->user()->isGuardian(), 403, 'Nur Verwalter können das Abo verwalten.');

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
            'plan' => $premium ? ($family->subscription->plan ?? 'monthly') : 'free',
            'expires_at' => $family?->subscription?->expires_at?->toIso8601String(),
        ];
    }
}
