<?php

namespace App\Http\Controllers;

use App\Models\KuraiyatGame;
use App\Models\KuraiyatPlayer;
use App\Models\KuraiyatRound;
use App\Services\KuraiyatService;
use Illuminate\Http\Request;

class KuraiyatController extends Controller
{
    public function __construct(
        protected KuraiyatService $kuraiyatService
    ) {}

    public function index()
    {
        $pastGames = KuraiyatGame::where('status', 'finished')
            ->where('type', 'who_am_i')
            ->whereIn('id', KuraiyatPlayer::where('user_id', auth()->id())->pluck('kuraiyat_game_id'))
            ->with('players.user', 'creator')
            ->latest()
            ->take(10)
            ->get();

        return view('kuraiyat.index', compact('pastGames'));
    }

    public function store(Request $request)
    {
        try {
            $game = $this->kuraiyatService->createGame(auth()->id());
            $this->kuraiyatService->startGame($game);
            return redirect()->route('kuraiyat.play', $game->code);
        } catch (\RuntimeException $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    public function play($code)
    {
        $game = KuraiyatGame::where('code', $code)
            ->with('players.user', 'rounds.question')
            ->firstOrFail();

        if ($game->status === 'finished') {
            return redirect()->route('kuraiyat.results', $code);
        }

        $authPlayer = $game->players->firstWhere('user_id', auth()->id());
        $state = $this->kuraiyatService->getGameState($game);

        return view('kuraiyat.who-am-i', compact('game', 'authPlayer', 'state'));
    }

    public function state($code)
    {
        $game = KuraiyatGame::where('code', $code)->firstOrFail();
        return response()->json($this->kuraiyatService->getGameState($game));
    }

    public function results($code)
    {
        $game = KuraiyatGame::where('code', $code)
            ->with('players.user')
            ->firstOrFail();

        $result = $this->kuraiyatService->getResults($game);
        return view('kuraiyat.results', $result);
    }

    public function submitAnswer(Request $request)
    {
        $validated = $request->validate([
            'game_code' => 'required|string|size:6',
            'round_id' => 'required|integer',
            'answer' => 'required|string|max:300',
        ]);

        $game = KuraiyatGame::where('code', $validated['game_code'])->firstOrFail();
        $player = KuraiyatPlayer::where('kuraiyat_game_id', $game->id)
            ->where('user_id', auth()->id())
            ->firstOrFail();

        $result = $this->kuraiyatService->submitAnswer($game, $player, $validated['round_id'], $validated['answer']);
        return response()->json($result);
    }

    public function getClue($code)
    {
        $game = KuraiyatGame::where('code', $code)->firstOrFail();
        $result = $this->kuraiyatService->getClue($game);
        return response()->json($result);
    }

    public function skipRound($code)
    {
        $game = KuraiyatGame::where('code', $code)->firstOrFail();
        $result = $this->kuraiyatService->skip($game);
        return response()->json($result);
    }
}
