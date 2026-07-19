<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\InteractsWithFamily;
use App\Models\GameScore;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class GameScoreController extends Controller
{
    /**
     * Bekannte Spiele der Fun Area. Eigene Namen/Themen statt geschützter
     * Marken (Mechanik ist frei, Name/Grafik nicht – siehe Produkt-Backlog).
     * Künftige Premium-Spiele bekommen hier ihren Slug + premium-Middleware.
     */
    // 'ballons' = Ballon-Knallerei (erstes Premium-Spiel; das Gate liegt in
    // der UI – Scores speichern schadet nie, falls Premium ausläuft).
    public const GAMES = ['raupe', 'ballons'];

    use InteractsWithFamily;

    /**
     * Familien-Bestenliste (bester Wert je Mitglied, absteigend) + eigener
     * Bestwert. Avatare/Namen joint das Frontend über die Mitgliederliste.
     */
    public function index(Request $request, string $game): JsonResponse
    {
        abort_unless(in_array($game, self::GAMES, true), 404);
        $familyId = $this->familyId($request);

        $top = GameScore::where('family_id', $familyId)
            ->where('game', $game)
            ->selectRaw('user_id, MAX(score) as best')
            ->groupBy('user_id')
            ->orderByDesc('best')
            ->limit(10)
            ->get()
            ->map(fn (GameScore $row) => [
                'user_id' => $row->user_id,
                'score' => (int) $row->best,
            ]);

        $myBest = GameScore::where('family_id', $familyId)
            ->where('game', $game)
            ->where('user_id', $request->user()->id)
            ->max('score');

        return response()->json(['data' => [
            'top' => $top,
            'my_best' => $myBest !== null ? (int) $myBest : null,
        ]]);
    }

    /**
     * Rundenergebnis speichern. Meldet zurück, ob es ein persönlicher bzw.
     * Familien-Rekord war (fürs Feiern im Frontend).
     */
    public function store(Request $request, string $game): JsonResponse
    {
        abort_unless(in_array($game, self::GAMES, true), 404);
        $familyId = $this->familyId($request);

        $data = $request->validate([
            'score' => ['required', 'integer', 'min:0', 'max:100000'],
        ]);

        $previousPersonal = GameScore::where('family_id', $familyId)
            ->where('game', $game)
            ->where('user_id', $request->user()->id)
            ->max('score');
        $previousFamily = GameScore::where('family_id', $familyId)
            ->where('game', $game)
            ->max('score');

        GameScore::create([
            'family_id' => $familyId,
            'user_id' => $request->user()->id,
            'game' => $game,
            'score' => $data['score'],
        ]);

        return response()->json(['data' => [
            'personal_record' => $previousPersonal === null || $data['score'] > (int) $previousPersonal,
            'family_record' => $previousFamily === null || $data['score'] > (int) $previousFamily,
        ]], 201);
    }
}
