<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\CategoryResource;
use App\Http\Resources\GameRoundResource;
use App\Http\Resources\GameSessionResource;
use App\Models\GameRound;
use App\Models\GameSession;
use App\Models\GameTeam;
use App\Services\GameService;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class GameController extends Controller
{
    use AuthorizesRequests;

    public function __construct(private GameService $gameService) {}

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'team_one_name'  => 'required|string|max:50',
            'team_two_name'  => 'required|string|max:50|different:team_one_name',
            'team_one_color' => 'nullable|string|regex:/^#[0-9A-Fa-f]{6}$/',
            'team_two_color' => 'nullable|string|regex:/^#[0-9A-Fa-f]{6}$/',
            'timer_seconds'  => 'nullable|integer|min:10|max:120',
            'sound_enabled'  => 'nullable|boolean',
        ]);

        $session = $this->gameService->createSession(Auth::id(), $validated);

        return response()->json([
            'message' => 'تم إنشاء اللعبة بنجاح',
            'game'    => new GameSessionResource($session->load('teams', 'creator')),
        ], 201);
    }

    public function categories(GameSession $session): JsonResponse
    {
        $this->authorize('manage', $session);

        $categories = \App\Models\Category::active()
            ->withCount(['questions' => fn($q) => $q->active()])
            ->orderBy('sort_order')
            ->get()
->map(function ($cat) {
                $cat->has_enough = $cat->hasEnoughQuestions();
                return $cat;
            });

        return response()->json([
            'categories' => CategoryResource::collection($categories),
        ]);
    }

    public function attachCategories(Request $request, GameSession $session): JsonResponse
    {
        $this->authorize('manage', $session);

        $validated = $request->validate([
            'category_ids'   => 'required|array|min:1',
            'category_ids.*' => 'exists:categories,id',
        ]);

        $this->gameService->attachCategories($session, $validated['category_ids']);

        return response()->json([
            'message' => 'تم اختيار الفئات بنجاح',
            'game'    => new GameSessionResource($session->fresh()->load('categories', 'teams', 'rounds')),
        ]);
    }

    public function start(GameSession $session): JsonResponse
    {
        $this->authorize('start', $session);

        $this->gameService->startSession($session);

        return response()->json([
            'message' => 'بدأت اللعبة!',
            'game'    => new GameSessionResource($session->fresh()->load('categories', 'teams', 'rounds')),
        ]);
    }

    public function board(GameSession $session): JsonResponse
    {
        $this->authorize('view', $session);

        $board = $this->gameService->getBoard($session);

        return response()->json([
            'board'          => $board,
            'current_team'   => $session->currentTeam ? [
                'id'    => $session->currentTeam->id,
                'name'  => $session->currentTeam->name,
                'color' => $session->currentTeam->color,
            ] : null,
            'game'           => new GameSessionResource($session),
        ]);
    }

    public function getQuestion(GameSession $session, GameRound $round): JsonResponse
    {
        $this->authorize('view', $session);

        $round->load('question');

        return response()->json([
            'round'    => new GameRoundResource($round),
        ]);
    }

    public function submitAnswer(Request $request, GameSession $session, GameRound $round): JsonResponse
    {
        $this->authorize('view', $session);

        $validated = $request->validate([
            'team_id'    => 'required|exists:game_teams,id',
            'answer'     => 'required|in:a,b,c,d',
            'time_taken' => 'nullable|integer|min:0',
        ]);

        $team = GameTeam::findOrFail($validated['team_id']);

        $result = $this->gameService->answerQuestion(
            $session,
            $round,
            $team,
            $validated['answer'],
            $validated['time_taken'] ?? 0,
        );

        return response()->json($result);
    }

    public function usePowerRemove(Request $request, GameSession $session, GameRound $round): JsonResponse
    {
        $this->authorize('view', $session);

        $request->validate(['team_id' => 'required|exists:game_teams,id']);
        $team = GameTeam::findOrFail($request->team_id);

        $removedAnswers = $this->gameService->usePowerRemove($round, $team);

        return response()->json([
            'message'        => 'تم استخدام خاصية حذف إجابتين',
            'removed_answers' => $removedAnswers,
        ]);
    }

    public function usePowerSteal(Request $request, GameSession $session, GameRound $round): JsonResponse
    {
        $this->authorize('view', $session);

        $request->validate(['team_id' => 'required|exists:game_teams,id']);
        $team = GameTeam::findOrFail($request->team_id);

        $round = $this->gameService->usePowerSteal($session, $round, $team);

        return response()->json([
            'message' => 'تمت سرقة السؤال',
            'round'   => new GameRoundResource($round->load('question')),
        ]);
    }

    public function result(GameSession $session): JsonResponse
    {
        $this->authorize('view', $session);

        $session->load('teams', 'categories');

        return response()->json([
            'game'   => new GameSessionResource($session),
            'winner' => $session->winner ? [
                'name'  => $session->winner->name,
                'color' => $session->winner->color,
                'score' => $session->winner->score,
            ] : null,
        ]);
    }

    public function index(Request $request): JsonResponse
    {
        $user = Auth::user();
        $query = GameSession::where('created_by', $user->id)
            ->with('teams');

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $games = $query->latest()->paginate($request->per_page ?? 20);

        return response()->json([
            'games'      => GameSessionResource::collection($games),
            'pagination' => [
                'current_page' => $games->currentPage(),
                'last_page'    => $games->lastPage(),
                'per_page'     => $games->perPage(),
                'total'        => $games->total(),
            ],
        ]);
    }

    public function show(GameSession $session): JsonResponse
    {
        $this->authorize('view', $session);

        $session->load('teams', 'categories', 'rounds.question');

        return response()->json([
            'game' => new GameSessionResource($session),
        ]);
    }
}
