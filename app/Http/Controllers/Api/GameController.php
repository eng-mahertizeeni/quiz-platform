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

    /**
     * @OA\Post(
     *      path="/api/games",
     *      description="Create a new game session.",
     *      tags={"Games"},
     *      security={{"bearer_token":{}}},
     *      @OA\RequestBody(
     *           required=true,
     *           @OA\MediaType(
     *              mediaType="multipart/form-data",
     *              @OA\Schema(
     *                  @OA\Property(property="team_one_name", type="string", description="Name of team one"),
     *                  @OA\Property(property="team_two_name", type="string", description="Name of team two (must differ)"),
     *                  @OA\Property(property="team_one_color", type="string", description="Hex color for team one"),
     *                  @OA\Property(property="team_two_color", type="string", description="Hex color for team two"),
     *                  @OA\Property(property="timer_seconds", type="integer", description="Timer per question (10-120)"),
     *                  @OA\Property(property="sound_enabled", type="boolean", description="Enable sound effects"),
     *              )
     *          )
     *      ),
     *      @OA\Response(response=201, description="Game created"),
     *      @OA\Response(response=401, description="Unauthenticated"),
     *      @OA\Response(response=422, description="Validation error")
     * )
     */
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

    /**
     * @OA\Get(
     *      path="/api/games/{session}/categories",
     *      description="Get available categories for a game session.",
     *      tags={"Games"},
     *      security={{"bearer_token":{}}},
     *      @OA\Parameter(name="session", in="path", required=true, @OA\Schema(type="string"), description="Game code or ID"),
     *      @OA\Response(response=200, description="Categories list"),
     *      @OA\Response(response=403, description="Forbidden"),
     *      @OA\Response(response=404, description="Not found")
     * )
     */
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

    /**
     * @OA\Post(
     *      path="/api/games/{session}/categories",
     *      description="Attach categories to a game session.",
     *      tags={"Games"},
     *      security={{"bearer_token":{}}},
     *      @OA\Parameter(name="session", in="path", required=true, @OA\Schema(type="string"), description="Game code or ID"),
     *      @OA\RequestBody(
     *           required=true,
     *           @OA\MediaType(
     *              mediaType="multipart/form-data",
     *              @OA\Schema(
     *                  @OA\Property(property="category_ids", type="array", @OA\Items(type="integer"), description="Array of category IDs"),
     *              )
     *          )
     *      ),
     *      @OA\Response(response=200, description="Categories attached"),
     *      @OA\Response(response=403, description="Forbidden"),
     *      @OA\Response(response=422, description="Validation error")
     * )
     */
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

    /**
     * @OA\Post(
     *      path="/api/games/{session}/start",
     *      description="Start the game session.",
     *      tags={"Games"},
     *      security={{"bearer_token":{}}},
     *      @OA\Parameter(name="session", in="path", required=true, @OA\Schema(type="string"), description="Game code or ID"),
     *      @OA\Response(response=200, description="Game started"),
     *      @OA\Response(response=403, description="Forbidden"),
     *      @OA\Response(response=422, description="Cannot start")
     * )
     */
    public function start(GameSession $session): JsonResponse
    {
        $this->authorize('start', $session);

        $this->gameService->startSession($session);

        return response()->json([
            'message' => 'بدأت اللعبة!',
            'game'    => new GameSessionResource($session->fresh()->load('categories', 'teams', 'rounds')),
        ]);
    }

    /**
     * @OA\Get(
     *      path="/api/games/{session}/board",
     *      description="Get the game board with categories and rounds.",
     *      tags={"Games"},
     *      security={{"bearer_token":{}}},
     *      @OA\Parameter(name="session", in="path", required=true, @OA\Schema(type="string"), description="Game code or ID"),
     *      @OA\Response(response=200, description="Game board data"),
     *      @OA\Response(response=403, description="Forbidden"),
     *      @OA\Response(response=404, description="Not found")
     * )
     */
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

    /**
     * @OA\Get(
     *      path="/api/games/{session}/round/{round}/question",
     *      description="Get the question for a specific round.",
     *      tags={"Games"},
     *      security={{"bearer_token":{}}},
     *      @OA\Parameter(name="session", in="path", required=true, @OA\Schema(type="string")),
     *      @OA\Parameter(name="round", in="path", required=true, @OA\Schema(type="integer")),
     *      @OA\Response(response=200, description="Question data"),
     *      @OA\Response(response=403, description="Forbidden")
     * )
     */
    public function getQuestion(GameSession $session, GameRound $round): JsonResponse
    {
        $this->authorize('view', $session);

        $round->load('question');

        return response()->json([
            'round'    => new GameRoundResource($round),
        ]);
    }

    /**
     * @OA\Post(
     *      path="/api/games/{session}/round/{round}/answer",
     *      description="Submit an answer for a round.",
     *      tags={"Games"},
     *      security={{"bearer_token":{}}},
     *      @OA\Parameter(name="session", in="path", required=true, @OA\Schema(type="string")),
     *      @OA\Parameter(name="round", in="path", required=true, @OA\Schema(type="integer")),
     *      @OA\RequestBody(
     *           required=true,
     *           @OA\MediaType(
     *              mediaType="multipart/form-data",
     *              @OA\Schema(
     *                  @OA\Property(property="team_id", type="integer", description="Team ID submitting the answer"),
     *                  @OA\Property(property="answer", type="string", enum={"a","b","c","d"}, description="Selected answer"),
     *                  @OA\Property(property="time_taken", type="integer", description="Time taken in seconds"),
     *              )
     *          )
     *      ),
     *      @OA\Response(response=200, description="Answer result"),
     *      @OA\Response(response=403, description="Forbidden"),
     *      @OA\Response(response=422, description="Validation error")
     * )
     */
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

    /**
     * @OA\Post(
     *      path="/api/games/{session}/round/{round}/power/remove",
     *      description="Use the remove-two-answers power.",
     *      tags={"Games"},
     *      security={{"bearer_token":{}}},
     *      @OA\Parameter(name="session", in="path", required=true, @OA\Schema(type="string")),
     *      @OA\Parameter(name="round", in="path", required=true, @OA\Schema(type="integer")),
     *      @OA\RequestBody(
     *           required=true,
     *           @OA\MediaType(
     *              mediaType="multipart/form-data",
     *              @OA\Schema(
     *                  @OA\Property(property="team_id", type="integer", description="Team ID using the power"),
     *              )
     *          )
     *      ),
     *      @OA\Response(response=200, description="Wrong answers removed"),
     *      @OA\Response(response=403, description="Forbidden")
     * )
     */
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

    /**
     * @OA\Post(
     *      path="/api/games/{session}/round/{round}/power/steal",
     *      description="Use the steal question power.",
     *      tags={"Games"},
     *      security={{"bearer_token":{}}},
     *      @OA\Parameter(name="session", in="path", required=true, @OA\Schema(type="string")),
     *      @OA\Parameter(name="round", in="path", required=true, @OA\Schema(type="integer")),
     *      @OA\RequestBody(
     *           required=true,
     *           @OA\MediaType(
     *              mediaType="multipart/form-data",
     *              @OA\Schema(
     *                  @OA\Property(property="team_id", type="integer", description="Team ID stealing the question"),
     *              )
     *          )
     *      ),
     *      @OA\Response(response=200, description="Question stolen"),
     *      @OA\Response(response=403, description="Forbidden")
     * )
     */
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

    /**
     * @OA\Get(
     *      path="/api/games/{session}/result",
     *      description="Get the final result of a finished game.",
     *      tags={"Games"},
     *      security={{"bearer_token":{}}},
     *      @OA\Parameter(name="session", in="path", required=true, @OA\Schema(type="string")),
     *      @OA\Response(response=200, description="Game result"),
     *      @OA\Response(response=403, description="Forbidden")
     * )
     */
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

    /**
     * @OA\Get(
     *      path="/api/games",
     *      description="List user's game sessions.",
     *      tags={"Games"},
     *      security={{"bearer_token":{}}},
     *      @OA\Response(response=200, description="List of games"),
     *      @OA\Response(response=401, description="Unauthenticated")
     * )
     */
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

    /**
     * @OA\Get(
     *      path="/api/games/{session}",
     *      description="Get a single game session details.",
     *      tags={"Games"},
     *      security={{"bearer_token":{}}},
     *      @OA\Parameter(name="session", in="path", required=true, @OA\Schema(type="string")),
     *      @OA\Response(response=200, description="Game details"),
     *      @OA\Response(response=404, description="Not found")
     * )
     */
    public function show(GameSession $session): JsonResponse
    {
        $this->authorize('view', $session);

        $session->load('teams', 'categories', 'rounds.question');

        return response()->json([
            'game' => new GameSessionResource($session),
        ]);
    }
}
