<?php

namespace App\Http\Controllers;

use App\Models\BluffGame;
use App\Models\BluffPlayer;
use App\Models\BluffQuestion;
use App\Models\BluffRound;
use App\Models\Category;
use App\Services\BluffService;
use Illuminate\Http\Request;

class BluffController extends Controller
{
    public function __construct(
        protected BluffService $bluffService
    ) {}

    public function index()
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

        $categoryIds = BluffQuestion::whereNotNull('category_id')
            ->distinct()
            ->pluck('category_id');

        $categories = Category::whereIn('id', $categoryIds)
            ->orderBy('name')
            ->get()
            ->map(function ($cat) {
                $cat->questions_count = BluffQuestion::where('category_id', $cat->id)->count();
                return $cat;
            });

        return view('bluff.index', compact('activeGames', 'pastGames', 'categories'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'total_rounds' => 'integer|min:3|max:15',
            'question_duration' => 'integer|in:15,20,25,30,35,40',
            'categories' => 'required|array|min:1',
            'categories.*' => 'integer|exists:categories,id',
        ]);

        try {
            $game = $this->bluffService->createGame(
                auth()->id(),
                $request->integer('total_rounds', 8),
                $request->input('categories'),
                $request->integer('question_duration', 30)
            );

            return redirect()->route('bluff.lobby', $game->code);
        } catch (\InvalidArgumentException $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    public function join(Request $request)
    {
        $request->validate([
            'code' => 'required|string|size:6',
            'display_name' => 'nullable|string|max:50',
        ]);

        try {
            $game = $this->bluffService->joinGame(
                strtoupper($request->code),
                auth()->id(),
                $request->input('display_name')
            );
            return redirect()->route('bluff.lobby', $game->code);
        } catch (\Throwable $e) {
            return back()->with('error', 'كود اللعبة غير صحيح أو اللعبة بدأت بالفعل');
        }
    }

    public function lobby($code)
    {
        $game = BluffGame::where('code', $code)
            ->with('players.user')
            ->firstOrFail();

        if ($game->status === 'playing') {
            return redirect()->route('bluff.play', $code);
        }

        if ($game->status === 'finished') {
            return redirect()->route('bluff.results', $code);
        }

        $isCreator = $game->bluff_creator_id === auth()->id();

        $categories = Category::whereIn('id', $game->selected_categories ?? [])->get();

        return view('bluff.lobby', compact('game', 'isCreator', 'categories'));
    }

    public function start($code)
    {
        $game = BluffGame::where('code', $code)->firstOrFail();

        if ($game->bluff_creator_id !== auth()->id()) {
            return back()->with('error', 'فقط منشئ اللعبة يمكنه بدء اللعبة');
        }

        if ($game->players()->count() < 2) {
            return back()->with('error', 'يجب أن يكون على الأقل لاعبين لبدء اللعبة');
        }

        try {
            $this->bluffService->startGame($game);
            return redirect()->route('bluff.play', $game->code);
        } catch (\RuntimeException $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    public function play($code)
    {
        $game = BluffGame::where('code', $code)
            ->with('players.user')
            ->firstOrFail();

        $player = $game->players->firstWhere('user_id', auth()->id());
        $isSpectator = !$player;

        if ($isSpectator && $game->status === 'waiting') {
            return redirect()->route('bluff.index')->with('error', 'أنت لست جزءاً من هذه اللعبة');
        }

        if ($game->status === 'waiting') {
            return redirect()->route('bluff.lobby', $code);
        }

        if ($game->status === 'finished') {
            return redirect()->route('bluff.results', $code);
        }

        return view('bluff.play', compact('game', 'isSpectator'));
    }

    public function getState($code)
    {
        try {
            $game = BluffGame::where('code', $code)->firstOrFail();
            return response()->json($this->bluffService->getGameState($game))
                ->header('Cache-Control', 'no-store, no-cache, must-revalidate');
        } catch (\Throwable $e) {
            \Log::error('Bluff getState error: ' . $e->getMessage(), ['code' => $code, 'trace' => $e->getTraceAsString()]);
            return response()->json(['error' => 'Internal error', 'message' => $e->getMessage()], 500);
        }
    }

    public function selectCategory(Request $request, $code, BluffRound $round)
    {
        $game = BluffGame::where('code', $code)->firstOrFail();
        $player = BluffPlayer::where('bluff_game_id', $game->id)
            ->where('user_id', auth()->id())
            ->first();

        if (!$player) {
            return response()->json(['status' => 'error', 'message' => 'أنت لست جزءاً من هذه اللعبة'], 403);
        }

        $request->validate(['category_id' => 'required|integer|exists:categories,id']);

        if ($round->bluff_game_id !== $game->id) {
            return response()->json(['status' => 'error', 'message' => 'جولة غير صالحة'], 400);
        }

        if ($round->round_number !== $game->current_round) {
            return response()->json(['status' => 'error', 'message' => 'هذه الجولة غير نشطة حالياً'], 400);
        }

        $result = $this->bluffService->selectCategory($game, $round, $player, $request->category_id);

        return response()->json($result);
    }

    public function submitAnswer(Request $request, $code, BluffRound $round)
    {
        $game = BluffGame::where('code', $code)->firstOrFail();
        $player = BluffPlayer::where('bluff_game_id', $game->id)
            ->where('user_id', auth()->id())
            ->first();

        if (!$player) {
            return response()->json(['status' => 'error', 'message' => 'أنت لست جزءاً من هذه اللعبة'], 403);
        }

        $request->validate(['answer' => 'required|string|max:255']);

        if ($round->bluff_game_id !== $game->id) {
            return response()->json(['status' => 'error', 'message' => 'جولة غير صالحة'], 400);
        }

        if ($round->round_number !== $game->current_round) {
            return response()->json(['status' => 'error', 'message' => 'هذه الجولة غير نشطة حالياً'], 400);
        }

        if ($round->status !== 'answering') {
            return response()->json(['status' => 'error', 'message' => 'هذه الجولة في حالة خطأ، يرجى التحديث'], 400);
        }

        $result = $this->bluffService->submitAnswer($game, $round, $player, $request->answer);

        return response()->json($result);
    }

    public function submitVote(Request $request, $code, BluffRound $round)
    {
        $game = BluffGame::where('code', $code)->firstOrFail();
        $player = BluffPlayer::where('bluff_game_id', $game->id)
            ->where('user_id', auth()->id())
            ->first();

        if (!$player) {
            return response()->json(['status' => 'error', 'message' => 'أنت لست جزءاً من هذه اللعبة'], 403);
        }

        $request->validate(['answer_id' => 'required|integer|exists:bluff_round_answers,id']);

        if ($round->bluff_game_id !== $game->id) {
            return response()->json(['status' => 'error', 'message' => 'جولة غير صالحة'], 400);
        }

        $result = $this->bluffService->submitVote($game, $round, $player, $request->answer_id);

        return response()->json($result);
    }

    public function advanceRound(Request $request, $code)
    {
        $game = BluffGame::where('code', $code)->firstOrFail();

        $currentRound = $game->rounds()->where('round_number', $game->current_round)->first();

        if (!$currentRound || $currentRound->status !== 'finished') {
            return response()->json(['status' => 'error', 'message' => 'الجولة الحالية لم تنته بعد'], 400);
        }

        $isCreator = $game->bluff_creator_id === auth()->id();
        $finishedLongAgo = $currentRound->updated_at && $currentRound->updated_at->diffInSeconds(now()) >= 15;

        if (!$isCreator && !$finishedLongAgo) {
            return response()->json(['status' => 'error', 'message' => 'فقط منشئ اللعبة يمكنه التقدم حالياً. انتظر 15 ثانية.'], 403);
        }

        $hasNext = $this->bluffService->advanceRound($game);

        return response()->json([
            'status' => 'ok',
            'has_next' => $hasNext,
            'redirect' => !$hasNext ? route('bluff.results', $game->code) : null,
        ]);
    }

    public function results($code)
    {
        $game = BluffGame::where('code', $code)
            ->with('players.user')
            ->firstOrFail();

        $results = $this->bluffService->getFinalResults($game);

        return view('bluff.results', $results);
    }

    public function getHistory($code)
    {
        $game = BluffGame::where('code', $code)->firstOrFail();
        $history = $this->bluffService->getRoundHistory($game);
        return response()->json(['rounds' => $history]);
    }

    public function lobbyData($code)
    {
        $game = BluffGame::where('code', $code)
            ->with('players.user')
            ->firstOrFail();

        return response()->json([
            'status' => $game->status,
            'players' => $game->players->map(fn($p) => [
                'id' => $p->id,
                'name' => $p->display_name,
                'avatar' => $p->user->avatar_url,
            ]),
            'is_creator' => $game->bluff_creator_id === auth()->id(),
        ]);
    }

    public function updateDisplayName(Request $request, $code)
    {
        $request->validate(['display_name' => 'required|string|max:50']);

        $game = BluffGame::where('code', $code)->firstOrFail();

        $player = BluffPlayer::where('bluff_game_id', $game->id)
            ->where('user_id', auth()->id())
            ->first();

        if (!$player) {
            return response()->json(['status' => 'error', 'message' => 'أنت لست جزءاً من هذه اللعبة'], 403);
        }

        $player->update(['display_name' => trim($request->input('display_name'))]);

        return response()->json(['status' => 'ok', 'name' => $player->display_name]);
    }
}
