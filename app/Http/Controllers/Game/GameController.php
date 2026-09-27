<?php

namespace App\Http\Controllers\Game;

use App\Http\Controllers\Controller;
use App\Http\Requests\Game\CreateGameRequest;
use App\Http\Requests\Game\SelectCategoriesRequest;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use App\Models\Category;
use App\Models\GameRound;
use App\Models\GameSession;
use App\Services\GameService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class GameController extends Controller
{    use AuthorizesRequests;

    public function __construct(private GameService $gameService) {}

    public function create()
    {
        return view('game.create');
    }

    public function store(CreateGameRequest $request)
    {
        $session = $this->gameService->createSession(Auth::id(), $request->validated());
        return redirect()->route('game.categories', $session->code);
    }

    public function selectCategories(GameSession $session)
    {
        $this->authorize('manage', $session);
        $categories = Category::active()
            ->with('parent')
            ->withCount(['questions' => fn($q) => $q->active()])
            ->orderBy('sort_order')
            ->get()
            ->map(function ($cat) {
                $cat->has_enough = $cat->hasEnoughQuestions();
                return $cat;
            });

        $parentIds = $categories->pluck('parent_id')->filter()->unique();
        $groups = $categories
            ->reject(fn($cat) => $parentIds->contains($cat->id))
            ->groupBy(fn($cat) => $cat->parent?->name ?? 'أخرى');

        return view('game.select-categories', compact('session', 'groups'));
    }

    public function attachCategories(SelectCategoriesRequest $request, GameSession $session)
    {
        $this->authorize('manage', $session);
        $this->gameService->attachCategories($session, $request->validated()['category_ids']);
        return redirect()->route('game.lobby', $session->code);
    }

    public function lobby(GameSession $session)
    {
        $this->authorize('view', $session);
        $session->load(['teams', 'categories', 'creator']);
        return view('game.lobby', compact('session'));
    }

    public function start(GameSession $session)
    {
        $this->authorize('start', $session);
        $this->gameService->startSession($session);
        return redirect()->route('game.board', $session->code);
    }

    public function board(GameSession $session)
    {
        $this->authorize('view', $session);

        if ($session->status === 'waiting') {
            return redirect()->route('game.lobby', $session->code);
        }

        if ($session->status === 'finished') {
            return redirect()->route('game.result', $session->code);
        }

        $session->load(['teams', 'categories', 'currentTeam']);
        $board = $this->gameService->getBoard($session);

        return view('game.board', compact('session', 'board'));
    }

    public function result(GameSession $session)
    {
        $session->load(['teams', 'categories', 'creator']);
        $winner = $session->teams->sortByDesc('score')->first();
        return view('game.result', compact('session', 'winner'));
    }

    public function getQuestion(GameSession $session, GameRound $round): JsonResponse
    {
        $this->authorize('view', $session);

        if ($round->game_session_id !== $session->id || $round->status !== 'pending') {
            return response()->json(['error' => 'السؤال غير متاح'], 422);
        }

        $question = $round->question;

        return response()->json([
            'round' => [
                'id' => $round->id,
                'points_value' => $round->points_value,
                'assigned_team_id' => $round->assigned_team_id,
                'is_stolen' => $round->is_stolen,
            ],
            'question' => [
                'id' => $question->id,
                'text' => $question->question_text,
                'answers' => $question->answers,
                'image' => $question->image_url,
                'difficulty' => $question->difficulty,
            ],
            'category' => $round->category->name,
        ]);
    }

    public function submitAnswer(Request $request, GameSession $session, GameRound $round): JsonResponse
    {
        $this->authorize('view', $session);

        $request->validate([
            'answer' => 'required|in:a,b,c,d',
            'team_id' => 'required|exists:game_teams,id',
            'time_taken' => 'nullable|integer|min:0|max:120',
            'power_remove_used' => 'boolean',
        ]);

        $team = $session->teams->find($request->team_id);

        if (!$team) {
            return response()->json(['error' => 'الفريق غير موجود'], 422);
        }

        $result = $this->gameService->answerQuestion(
            $session,
            $round,
            $team,
            $request->answer,
            $request->time_taken ?? 0,
            $request->boolean('power_remove_used')
        );

        $session->refresh();
        $session->load('teams', 'currentTeam');

        return response()->json([
            ...$result,
            'teams' => $session->teams->map(fn($t) => [
                'id' => $t->id,
                'name' => $t->name,
                'score' => $t->score,
            ]),
            'current_team_id' => $session->current_team_id,
        ]);
    }

    public function usePowerRemove(Request $request, GameSession $session, GameRound $round): JsonResponse
    {
        $this->authorize('view', $session);

        $request->validate(['team_id' => 'required|exists:game_teams,id']);

        $team = $session->teams->find($request->team_id);

        if (!$team || !$team->canUseRemovePower()) {
            return response()->json(['error' => 'لا يمكن استخدام هذه القوة'], 422);
        }

        $removedAnswers = $this->gameService->usePowerRemove($round, $team);

        return response()->json([
            'success' => true,
            'removed_answers' => $removedAnswers,
        ]);
    }

    public function usePowerSteal(Request $request, GameSession $session, GameRound $round): JsonResponse
    {
        $this->authorize('view', $session);

        $request->validate(['team_id' => 'required|exists:game_teams,id']);

        $team = $session->teams->find($request->team_id);

        if (!$team || !$team->canUseStealPower()) {
            return response()->json(['error' => 'لا يمكن استخدام هذه القوة'], 422);
        }

        $this->gameService->usePowerSteal($session, $round, $team);

        return response()->json(['success' => true, 'message' => 'تم سرقة السؤال بنجاح']);
    }
}