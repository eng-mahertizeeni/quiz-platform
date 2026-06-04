<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\KuraiyatGame;
use App\Models\KuraiyatPlayer;
use App\Services\KuraiyatService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class KuraiyatController extends Controller
{
    public function __construct(private KuraiyatService $kuraiyatService) {}

    public function games(): JsonResponse
    {
        return response()->json([
            'games' => [
                [
                    'type' => 'who_am_i',
                    'name' => 'أنا مين',
                    'description' => '5 أدلة عن لاعب أو نادي — خمّن من هو قبل أن تظهر كل الأدلة',
                    'max_players' => 1,
                    'icon' => '🕵️',
                ],
            ],
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        try {
            $game = $this->kuraiyatService->createGame(Auth::id());
            $this->kuraiyatService->startGame($game);

            return response()->json([
                'game' => [
                    'id' => $game->id,
                    'code' => $game->code,
                    'type' => $game->type,
                    'status' => $game->status,
                ],
            ], 201);
        } catch (\RuntimeException $e) {
            return response()->json(['message' => $e->getMessage()], 400);
        }
    }

    public function state($code): JsonResponse
    {
        $game = KuraiyatGame::where('code', $code)->firstOrFail();
        return response()->json($this->kuraiyatService->getGameState($game));
    }

    public function submitAnswer(Request $request, $code): JsonResponse
    {
        $validated = $request->validate([
            'round_id' => 'required|integer',
            'answer' => 'required|string|max:300',
        ]);

        $game = KuraiyatGame::where('code', $code)->firstOrFail();
        $player = KuraiyatPlayer::where('kuraiyat_game_id', $game->id)
            ->where('user_id', Auth::id())
            ->firstOrFail();

        $result = $this->kuraiyatService->submitAnswer($game, $player, $validated['round_id'], $validated['answer']);
        return response()->json($result);
    }

    public function clue($code): JsonResponse
    {
        $game = KuraiyatGame::where('code', $code)->firstOrFail();
        return response()->json($this->kuraiyatService->getClue($game));
    }

    public function skip($code): JsonResponse
    {
        $game = KuraiyatGame::where('code', $code)->firstOrFail();
        return response()->json($this->kuraiyatService->skip($game));
    }

    public function results($code): JsonResponse
    {
        $game = KuraiyatGame::where('code', $code)->with('players.user')->firstOrFail();
        return response()->json($this->kuraiyatService->getResults($game));
    }

    public function index(): JsonResponse
    {
        $pastGames = KuraiyatGame::where('status', 'finished')
            ->where('type', 'who_am_i')
            ->whereIn('id', KuraiyatPlayer::where('user_id', Auth::id())->pluck('kuraiyat_game_id'))
            ->with('players.user', 'creator')
            ->latest()
            ->take(10)
            ->get();

        return response()->json([
            'past_games' => $pastGames->map(fn($g) => [
                'code' => $g->code,
                'type' => $g->type,
                'my_score' => $g->players->firstWhere('user_id', Auth::id())?->score ?? 0,
                'finished_at' => $g->finished_at,
            ]),
        ]);
    }
}
