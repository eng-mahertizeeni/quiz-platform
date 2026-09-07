<?php

namespace Tests\Feature;

use App\Models\BluffGame;
use App\Models\BluffPlayer;
use App\Models\BluffQuestion;
use App\Models\BluffRound;
use App\Models\BluffRoundAnswer;
use App\Models\BluffVote;
use App\Models\Category;
use App\Models\User;
use App\Services\BluffService;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Tests\TestCase;
use PHPUnit\Framework\Attributes\Test;

class BluffGameTest extends TestCase
{
    use DatabaseMigrations;

    private User $creator;
    private User $player2;
    private User $player3;
    private BluffService $service;
    private array $categoryIds;

    protected function setUp(): void
    {
        parent::setUp();
        $this->creator = User::factory()->create();
        $this->player2 = User::factory()->create();
        $this->player3 = User::factory()->create();
        $this->service = app(BluffService::class);

        $category = Category::factory()->create();
        $this->categoryIds = [$category->id];

        $questions = [
            ['question_text' => 'ما هي أكبر قارة؟', 'correct_answer' => 'آسيا'],
            ['question_text' => 'ما هي عاصمة فرنسا؟', 'correct_answer' => 'باريس'],
            ['question_text' => 'ما هو لون السماء؟', 'correct_answer' => 'أزرق'],
            ['question_text' => 'كم شهر في السنة؟', 'correct_answer' => '12'],
            ['question_text' => 'من اخترع المصباح؟', 'correct_answer' => 'إديسون'],
            ['question_text' => 'ما هو أكبر محيط؟', 'correct_answer' => 'الهادئ'],
            ['question_text' => 'ما هي عاصمة مصر؟', 'correct_answer' => 'القاهرة'],
            ['question_text' => 'ما هو أسرع حيوان؟', 'correct_answer' => 'الفهد'],
        ];
        foreach ($questions as $q) {
            BluffQuestion::create(array_merge($q, ['category_id' => $category->id]));
        }
    }

    public function guest_cannot_access_bluff()
    {
        $this->get(route('bluff.index'))->assertRedirect(route('login', absolute: false));
    }

    public function user_can_create_game()
    {
        $response = $this->actingAs($this->creator)->post(route('bluff.store'), [
            'total_rounds' => 5,
            'categories' => $this->categoryIds,
        ]);

        $game = BluffGame::where('bluff_creator_id', $this->creator->id)->first();
        $this->assertNotNull($game);
        $this->assertEquals(5, $game->total_rounds);
        $this->assertEquals('waiting', $game->status);
        $response->assertRedirect(route('bluff.lobby', $game->code));
    }

    public function user_can_join_game()
    {
        $game = $this->service->createGame($this->creator->id, 3, $this->categoryIds);

        $response = $this->actingAs($this->player2)
            ->post(route('bluff.join'), ['code' => $game->code]);

        $response->assertRedirect(route('bluff.lobby', $game->code));
        $this->assertDatabaseHas('bluff_players', [
            'bluff_game_id' => $game->id,
            'user_id' => $this->player2->id,
        ]);
    }

    public function cannot_join_with_wrong_code()
    {
        $this->service->createGame($this->creator->id, 3, $this->categoryIds);

        $response = $this->actingAs($this->player2)
            ->post(route('bluff.join'), ['code' => 'XXXXXX']);

        $response->assertSessionHas('error');
    }

    public function creator_can_start_game()
    {
        $game = $this->service->createGame($this->creator->id, 3, $this->categoryIds);
        $this->service->joinGame($game->code, $this->player2->id);

        $response = $this->actingAs($this->creator)
            ->post(route('bluff.start', $game->code));

        $response->assertRedirect(route('bluff.play', $game->code));
        $this->assertEquals('playing', $game->fresh()->status);
        $this->assertEquals(1, $game->fresh()->current_round);
    }

    public function non_creator_cannot_start_game()
    {
        $game = $this->service->createGame($this->creator->id, 3, $this->categoryIds);
        $this->service->joinGame($game->code, $this->player2->id);

        $response = $this->actingAs($this->player2)
            ->post(route('bluff.start', $game->code));

        $response->assertSessionHas('error');
    }

    public function game_needs_min_2_players()
    {
        $game = $this->service->createGame($this->creator->id, 3, $this->categoryIds);

        $response = $this->actingAs($this->creator)
            ->post(route('bluff.start', $game->code));

        $response->assertSessionHas('error');
    }

    public function full_game_flow_works()
    {
        $game = $this->service->createGame($this->creator->id, 3, $this->categoryIds);
        $this->service->joinGame($game->code, $this->player2->id);
        $this->service->joinGame($game->code, $this->player3->id);
        $this->service->startGame($game);
        $game = $game->fresh();

        $this->assertEquals('playing', $game->status);
        $this->assertEquals(1, $game->current_round);

        $creatorPlayer = BluffPlayer::where('bluff_game_id', $game->id)
            ->where('user_id', $this->creator->id)->first();
        $player2Player = BluffPlayer::where('bluff_game_id', $game->id)
            ->where('user_id', $this->player2->id)->first();
        $player3Player = BluffPlayer::where('bluff_game_id', $game->id)
            ->where('user_id', $this->player3->id)->first();

        $round = BluffRound::where('bluff_game_id', $game->id)
            ->where('round_number', 1)->first();
        $this->assertEquals('answering', $round->status);

        $result = $this->service->submitAnswer($game, $round, $creatorPlayer, 'أوروبا');
        $this->assertEquals('ok', $result['status']);

        $result = $this->service->submitAnswer($game, $round, $player2Player, 'أمريكا');
        $this->assertEquals('ok', $result['status']);

        $result = $this->service->submitAnswer($game, $round, $player3Player, 'أفريقيا');
        $this->assertEquals('ok', $result['status']);

        $round = $round->fresh();
        $this->assertEquals('voting', $round->status);

        $correctEntry = BluffRoundAnswer::where('bluff_round_id', $round->id)
            ->where('is_real_fake', true)->first();
        $this->assertNotNull($correctEntry);
        $this->assertNull($correctEntry->bluff_player_id);

        $fakeAnswers = BluffRoundAnswer::where('bluff_round_id', $round->id)
            ->where('is_real_fake', false)->get();

        $result = $this->service->submitVote($game, $round, $creatorPlayer, $correctEntry->id);
        $this->assertEquals('ok', $result['status']);

        $result = $this->service->submitVote($game, $round, $player2Player, $fakeAnswers[0]->id);
        $this->assertEquals('ok', $result['status']);

        $result = $this->service->submitVote($game, $round, $player3Player, $fakeAnswers[0]->id);
        $this->assertEquals('ok', $result['status']);

        $round = $round->fresh();
        $this->assertEquals('finished', $round->status);

        $totalScore = $creatorPlayer->fresh()->total_score
            + $player2Player->fresh()->total_score
            + $player3Player->fresh()->total_score;
        $this->assertEquals(4, $totalScore);

        $hasNext = $this->service->advanceRound($game);
        $this->assertTrue($hasNext);
        $this->assertEquals(2, $game->fresh()->current_round);
    }

    public function game_ends_after_all_rounds()
    {
        $game = $this->service->createGame($this->creator->id, 1, $this->categoryIds);
        $this->service->joinGame($game->code, $this->player2->id);
        $this->service->startGame($game);
        $game = $game->fresh();

        $round = BluffRound::where('bluff_game_id', $game->id)->first();
        $players = BluffPlayer::where('bluff_game_id', $game->id)->get();

        foreach ($players as $p) {
            $this->service->submitAnswer($game, $round, $p, 'إجابة');
        }

        $correctEntry = BluffRoundAnswer::where('bluff_round_id', $round->id)
            ->where('is_real_fake', true)->first();

        foreach ($players as $p) {
            $this->service->submitVote($game, $round, $p, $correctEntry->id);
        }

        $hasNext = $this->service->advanceRound($game);
        $this->assertFalse($hasNext);
        $this->assertEquals('finished', $game->fresh()->status);
    }

    public function cannot_vote_for_own_answer()
    {
        $game = $this->service->createGame($this->creator->id, 1, $this->categoryIds);
        $this->service->joinGame($game->code, $this->player2->id);
        $this->service->startGame($game);
        $game = $game->fresh();

        $round = BluffRound::where('bluff_game_id', $game->id)->first();
        $players = BluffPlayer::where('bluff_game_id', $game->id)->get();

        foreach ($players as $p) {
            $this->service->submitAnswer($game, $round, $p, 'إجابة' . $p->id);
        }

        $creatorPlayer = $players->firstWhere('user_id', $this->creator->id);
        $myAnswer = BluffRoundAnswer::where('bluff_round_id', $round->id)
            ->where('bluff_player_id', $creatorPlayer->id)
            ->where('is_real_fake', false)
            ->first();

        $result = $this->service->submitVote($game, $round, $creatorPlayer, $myAnswer->id);
        $this->assertEquals('error', $result['status']);
        $this->assertEquals('لا يمكنك التصويت على إجابتك الخاصة', $result['message']);
    }

    public function cannot_submit_empty_answer()
    {
        $game = $this->service->createGame($this->creator->id, 1, $this->categoryIds);
        $this->service->joinGame($game->code, $this->player2->id);
        $this->service->startGame($game);
        $game = $game->fresh();

        $round = BluffRound::where('bluff_game_id', $game->id)->first();
        $creatorPlayer = BluffPlayer::where('bluff_game_id', $game->id)
            ->where('user_id', $this->creator->id)->first();

        $result = $this->service->submitAnswer($game, $round, $creatorPlayer, '');
        $this->assertEquals('error', $result['status']);

        $result = $this->service->submitAnswer($game, $round, $creatorPlayer, ' ');
        $this->assertEquals('error', $result['status']);

        $result = $this->service->submitAnswer($game, $round, $creatorPlayer, 'ا');
        $this->assertEquals('error', $result['status']);
    }

    public function cannot_submit_correct_answer()
    {
        $game = $this->service->createGame($this->creator->id, 1, $this->categoryIds);
        $this->service->joinGame($game->code, $this->player2->id);
        $this->service->startGame($game);
        $game = $game->fresh();

        $round = BluffRound::where('bluff_game_id', $game->id)->first();
        $round->load('question');
        $creatorPlayer = BluffPlayer::where('bluff_game_id', $game->id)
            ->where('user_id', $this->creator->id)->first();

        $result = $this->service->submitAnswer($game, $round, $creatorPlayer, $round->question->correct_answer);
        $this->assertEquals('correct_answer_rejected', $result['status']);
    }

    public function getGameState_returns_correct_round_data()
    {
        $game = $this->service->createGame($this->creator->id, 1, $this->categoryIds);
        $this->service->joinGame($game->code, $this->player2->id);
        $this->service->startGame($game);
        $game = $game->fresh();

        $state = $this->service->getGameState($game);
        $this->assertEquals('playing', $state['status']);
        $this->assertEquals(1, $state['current_round']);
        $this->assertCount(2, $state['players']);
        $this->assertNotNull($state['current_round_data']);
        $this->assertEquals(1, $state['current_round_data']['round_number']);
        $this->assertEquals('answering', $state['current_round_data']['status']);
    }

    public function final_results_are_ordered_by_score()
    {
        $game = $this->service->createGame($this->creator->id, 1, $this->categoryIds);
        $this->service->joinGame($game->code, $this->player2->id);
        $this->service->startGame($game);
        $game = $game->fresh();

        $round = BluffRound::where('bluff_game_id', $game->id)->first();
        $players = BluffPlayer::where('bluff_game_id', $game->id)->get();

        foreach ($players as $p) {
            $this->service->submitAnswer($game, $round, $p, 'إجابة' . $p->id);
        }

        $correctEntry = BluffRoundAnswer::where('bluff_round_id', $round->id)
            ->where('is_real_fake', true)->first();

        foreach ($players as $p) {
            $this->service->submitVote($game, $round, $p, $correctEntry->id);
        }

        $this->service->advanceRound($game);

        $results = $this->service->getFinalResults($game);
        $this->assertCount(2, $results['results']);
        $this->assertNotNull($results['winner']);
        $this->assertEquals(2, $results['results'][0]['total_score']);
        $this->assertEquals(2, $results['results'][1]['total_score']);
    }
}
