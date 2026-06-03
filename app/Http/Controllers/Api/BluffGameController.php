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

    /**
     * @OA\Get(
     *      path="/api/bluff",
     *      description="List user's bluff games (active and past).",
     *      tags={"Bluff"},
     *      security={{"bearer_token":{}}},
     *      @OA\Response(response=200, description="List of bluff games"),
     *      @OA\Response(response=401, description="Unauthenticated")
     * )
     */
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

    /**
     * @OA\Post(
     *      path="/api/bluff",
     *      description="Create a new bluff game.",
     *      tags={"Bluff"},
     *      security={{"bearer_token":{}}},
     *      @OA\RequestBody(
     *           required=true,
     *           @OA\MediaType(
     *              mediaType="multipart/form-data",
     *              @OA\Schema(
     *                  @OA\Property(property="total_rounds", type="integer", description="Number of rounds (3-15)"),
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
        $request->validate([
            'total_rounds' => 'integer|min:3|max:15',
        ]);

        $game = $this->bluffService->createGame(Auth::id(), $request->total_rounds ?? 8);

        return response()->json([
            'message' => 'تم إنشاء لعبة الخداع بنجاح',
            'game'    => new BluffGameResource($game->load('creator', 'players.user')),
        ], 201);
    }

    /**
     * @OA\Post(
     *      path="/api/bluff/join",
     *      description="Join an existing bluff game by code.",
     *      tags={"Bluff"},
     *      security={{"bearer_token":{}}},
     *      @OA\RequestBody(
     *           required=true,
     *           @OA\MediaType(
     *              mediaType="multipart/form-data",
     *              @OA\Schema(
     *                  @OA\Property(property="code", type="string", description="Game code to join"),
     *              )
     *          )
     *      ),
     *      @OA\Response(response=200, description="Joined game"),
     *      @OA\Response(response=401, description="Unauthenticated"),
     *      @OA\Response(response=404, description="Game not found")
     * )
     */
    public function join(Request $request): JsonResponse
    {
        $request->validate(['code' => 'required|string|size:6']);

        $game = $this->bluffService->joinGame($request->code, Auth::id());

        return response()->json([
            'message' => 'تم الانضمام للعبة بنجاح',
            'game'    => new BluffGameResource($game->load('creator', 'players.user')),
        ]);
    }

    /**
     * @OA\Get(
     *      path="/api/bluff/{code}/lobby",
     *      description="Get lobby data for a bluff game.",
     *      tags={"Bluff"},
     *      security={{"bearer_token":{}}},
     *      @OA\Parameter(name="code", in="path", required=true, @OA\Schema(type="string")),
     *      @OA\Response(response=200, description="Lobby data"),
     *      @OA\Response(response=401, description="Unauthenticated"),
     *      @OA\Response(response=404, description="Game not found")
     * )
     */
    public function lobby(string $code): JsonResponse
    {
        $game = BluffGame::where('code', $code)
            ->with('players.user', 'creator')
            ->firstOrFail();

        return response()->json([
            'game' => new BluffGameResource($game),
        ]);
    }

    /**
     * @OA\Post(
     *      path="/api/bluff/{code}/start",
     *      description="Start a bluff game (creator only).",
     *      tags={"Bluff"},
     *      security={{"bearer_token":{}}},
     *      @OA\Parameter(name="code", in="path", required=true, @OA\Schema(type="string")),
     *      @OA\Response(response=200, description="Game started"),
     *      @OA\Response(response=401, description="Unauthenticated"),
     *      @OA\Response(response=403, description="Forbidden"),
     *      @OA\Response(response=404, description="Game not found")
     * )
     */
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

    /**
     * @OA\Get(
     *      path="/api/bluff/{code}/state",
     *      description="Get the current game state for playing.",
     *      tags={"Bluff"},
     *      security={{"bearer_token":{}}},
     *      @OA\Parameter(name="code", in="path", required=true, @OA\Schema(type="string")),
     *      @OA\Response(response=200, description="Game state"),
     *      @OA\Response(response=401, description="Unauthenticated"),
     *      @OA\Response(response=404, description="Game not found")
     * )
     */
    public function state(string $code): JsonResponse
    {
        $game = BluffGame::where('code', $code)->firstOrFail();
        $state = $this->bluffService->getGameState($game);

        return response()->json($state);
    }

    /**
     * @OA\Post(
     *      path="/api/bluff/{code}/round/{round}/answer",
     *      description="Submit a bluff answer for the current round.",
     *      tags={"Bluff"},
     *      security={{"bearer_token":{}}},
     *      @OA\Parameter(name="code", in="path", required=true, @OA\Schema(type="string")),
     *      @OA\Parameter(name="round", in="path", required=true, @OA\Schema(type="integer")),
     *      @OA\RequestBody(
     *           required=true,
     *           @OA\MediaType(
     *              mediaType="multipart/form-data",
     *              @OA\Schema(
     *                  @OA\Property(property="answer_text", type="string", description="Your fake answer text"),
     *              )
     *          )
     *      ),
     *      @OA\Response(response=200, description="Answer submitted"),
     *      @OA\Response(response=401, description="Unauthenticated"),
     *      @OA\Response(response=422, description="Validation error")
     * )
     */
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

    /**
     * @OA\Post(
     *      path="/api/bluff/{code}/round/{round}/vote",
     *      description="Vote for a bluff answer.",
     *      tags={"Bluff"},
     *      security={{"bearer_token":{}}},
     *      @OA\Parameter(name="code", in="path", required=true, @OA\Schema(type="string")),
     *      @OA\Parameter(name="round", in="path", required=true, @OA\Schema(type="integer")),
     *      @OA\RequestBody(
     *           required=true,
     *           @OA\MediaType(
     *              mediaType="multipart/form-data",
     *              @OA\Schema(
     *                  @OA\Property(property="answer_id", type="integer", description="Answer ID to vote for"),
     *              )
     *          )
     *      ),
     *      @OA\Response(response=200, description="Vote submitted"),
     *      @OA\Response(response=401, description="Unauthenticated"),
     *      @OA\Response(response=422, description="Validation error")
     * )
     */
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

    /**
     * @OA\Post(
     *      path="/api/bluff/{code}/advance",
     *      description="Advance to the next round (creator only).",
     *      tags={"Bluff"},
     *      security={{"bearer_token":{}}},
     *      @OA\Parameter(name="code", in="path", required=true, @OA\Schema(type="string")),
     *      @OA\Response(response=200, description="Next round started"),
     *      @OA\Response(response=401, description="Unauthenticated"),
     *      @OA\Response(response=403, description="Forbidden"),
     *      @OA\Response(response=404, description="Game not found")
     * )
     */
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

    /**
     * @OA\Get(
     *      path="/api/bluff/{code}/results",
     *      description="Get final results of a finished bluff game.",
     *      tags={"Bluff"},
     *      security={{"bearer_token":{}}},
     *      @OA\Parameter(name="code", in="path", required=true, @OA\Schema(type="string")),
     *      @OA\Response(response=200, description="Final results"),
     *      @OA\Response(response=401, description="Unauthenticated"),
     *      @OA\Response(response=404, description="Game not found")
     * )
     */
    public function results(string $code): JsonResponse
    {
        $game = BluffGame::where('code', $code)->firstOrFail();
        $results = $this->bluffService->getFinalResults($game);

        return response()->json($results);
    }

    /**
     * @OA\Get(
     *      path="/api/bluff/{code}/history",
     *      description="Get round-by-round history of a finished bluff game.",
     *      tags={"Bluff"},
     *      security={{"bearer_token":{}}},
     *      @OA\Parameter(name="code", in="path", required=true, @OA\Schema(type="string")),
     *      @OA\Response(response=200, description="Round history"),
     *      @OA\Response(response=401, description="Unauthenticated"),
     *      @OA\Response(response=404, description="Game not found")
     * )
     */
    public function history(string $code): JsonResponse
    {
        $game = BluffGame::where('code', $code)->firstOrFail();
        $history = $this->bluffService->getRoundHistory($game);

        return response()->json([
            'history' => $history,
        ]);
    }
}
