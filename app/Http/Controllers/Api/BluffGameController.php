<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\BluffGameResource;
use App\Models\BluffGame;
use App\Models\BluffPlayer;
use App\Models\BluffRound;
use App\Services\BluffService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class BluffGameController extends Controller
{
    public function __construct(
        protected BluffService $bluffService
    ) {}

    public function index(): JsonResponse
    {
        $activeGames = BluffGame::where('status', 'playing')
            ->whereIn('id', BluffPlayer::where('user_id', auth()->id())->pluck('bluff_game_id'))
            ->with('players.user', 'creator')
            ->latest()
            ->get();

        $pastGames = BluffGame::where('status', 'finished')
            ->whereIn('id', BluffPlayer::where('user_id', auth()->id())->pluck('bluff_game_id'))
            ->with('players.user', 'creator')
            ->latest()
            ->take(10)
            ->get();

        return response()->json([
            'active_games' => BluffGameResource::collection($activeGames),
            'past_games'   => BluffGameResource::collection($pastGames),
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'total_rounds' => 'integer|min:3|max:15',
            'question_duration' => 'integer|in:15,20,25,30,35,40',
            'categories' => 'required|array|min:1',
            'categories.*' => 'integer|exists:categories,id',
        ]);

        $game = $this->bluffService->createGame(
            Auth::id(),
            $request->total_rounds ?? 8,
            $request->input('categories'),
            $request->integer('question_duration', 30)
        );

        return response()->json([
            'message' => 'تم إنشاء لعبة الخداع بنجاح',
            'game'    => new BluffGameResource($game->load('creator', 'players.user')),
        ], 201);
    }

    public function join(Request $request): JsonResponse
    {
        $request->validate(['code' => 'required|string|size:6']);

        $game = $this->bluffService->joinGame($request->code, Auth::id());

        return response()->json([
            'message' => 'تم الانضمام للعبة بنجاح',
            'game'    => new BluffGameResource($game->load('creator', 'players.user')),
        ]);
    }

    public function lobby(string $code): JsonResponse
    {
        $game = BluffGame::where('code', $code)
            ->with('players.user', 'creator')
            ->firstOrFail();

        return response()->json([
            'game' => new BluffGameResource($game),
        ]);
    }

    public function start(string $code): JsonResponse
    {
        $game = BluffGame::where('code', $code)->firstOrFail();

        if ($game->bluff_creator_id !== Auth::id()) {
            return response()->json(['message' => 'غير مصرح لك ببدء اللعبة'], 403);
        }

        $this->bluffService->startGame($game);

        return response()->json([
            'message' => 'بدأت اللعبة!',
            'game'    => new BluffGameResource($game->fresh()->load('players.user', 'creator')),
        ]);
    }

    public function state(string $code): JsonResponse
    {
        $game = BluffGame::where('code', $code)->firstOrFail();
        $state = $this->bluffService->getGameState($game);

        return response()->json($state);
    }

    public function submitAnswer(Request $request, string $code, BluffRound $round): JsonResponse
    {
        $game = BluffGame::where('code', $code)->firstOrFail();
        $player = BluffPlayer::where('bluff_game_id', $game->id)
            ->where('user_id', Auth::id())
            ->firstOrFail();

        $request->validate(['answer_text' => 'required|string|max:500']);

        $result = $this->bluffService->submitAnswer($game, $round, $player, $request->answer_text);

        return response()->json($result);
    }

    public function submitVote(Request $request, string $code, BluffRound $round): JsonResponse
    {
        $game = BluffGame::where('code', $code)->firstOrFail();
        $player = BluffPlayer::where('bluff_game_id', $game->id)
            ->where('user_id', Auth::id())
            ->firstOrFail();

        $request->validate(['answer_id' => 'required|exists:bluff_round_answers,id']);

        $result = $this->bluffService->submitVote($game, $round, $player, $request->answer_id);

        return response()->json($result);
    }

    public function advanceRound(string $code): JsonResponse
    {
        $game = BluffGame::where('code', $code)->firstOrFail();

        if ($game->bluff_creator_id !== Auth::id()) {
            return response()->json(['message' => 'غير مصرح لك'], 403);
        }

        $hasNext = $this->bluffService->advanceRound($game);

        if (!$hasNext) {
            return response()->json([
                'message'  => 'انتهت اللعبة!',
                'finished' => true,
            ]);
        }

        return response()->json([
            'message'          => 'تم التقدم للجولة التالية',
            'current_round'    => $game->fresh()->current_round,
        ]);
    }

    public function results(string $code): JsonResponse
    {
        $game = BluffGame::where('code', $code)->firstOrFail();
        $results = $this->bluffService->getFinalResults($game);

        return response()->json($results);
    }

    public function history(string $code): JsonResponse
    {
        $game = BluffGame::where('code', $code)->firstOrFail();
        $history = $this->bluffService->getRoundHistory($game);

        return response()->json([
            'history' => $history,
        ]);
    }
}
