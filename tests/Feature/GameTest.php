<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\GameSession;
use App\Models\GameTeam;
use App\Models\Question;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Tests\TestCase;
use PHPUnit\Framework\Attributes\Test;

class GameTest extends TestCase
{
    use DatabaseMigrations;
    private User $user;
    private Category $category;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();

        $this->category = Category::factory()->create();
        foreach (['medium', 'hard', 'very_hard'] as $diff) {
            Question::factory()->count(2)->create([
                'category_id' => $this->category->id,
                'difficulty' => $diff,
                'points' => $diff === 'medium' ? 250 : ($diff === 'hard' ? 500 : 750),
                'status' => 'active',
            ]);
        }
    }

    public function create_game_page_is_accessible()
    {
        $response = $this->actingAs($this->user)->get(route('game.create'));
        $response->assertStatus(200);
    }

    public function guests_cannot_access_create_game()
    {
        $response = $this->get(route('game.create'));
        $response->assertRedirect(route('login', absolute: false));
    }

    public function game_can_be_created()
    {
        $response = $this->actingAs($this->user)->post(route('game.store'), [
            'team_one_name' => 'Team A',
            'team_two_name' => 'Team B',
            'timer_seconds' => 30,
        ]);

        $session = GameSession::where('created_by', $this->user->id)->first();
        $this->assertNotNull($session);
        $response->assertRedirect(route('game.categories', $session->code));
    }

    public function categories_page_shows_games()
    {
        $session = $this->createGameSession();

        $response = $this->actingAs($this->user)->get(route('game.categories', $session->code));
        $response->assertStatus(200);
    }

    public function categories_can_be_attached_to_game()
    {
        $session = $this->createGameSession();
        $categories = Category::factory()->count(6)->create();

        foreach ($categories as $cat) {
            foreach (['medium', 'hard', 'very_hard'] as $diff) {
                Question::factory()->count(2)->create([
                    'category_id' => $cat->id,
                    'difficulty' => $diff,
                    'points' => $diff === 'medium' ? 250 : ($diff === 'hard' ? 500 : 750),
                    'status' => 'active',
                ]);
            }
        }

        $response = $this->actingAs($this->user)
            ->post(route('game.categories.attach', $session->code), [
                'category_ids' => $categories->pluck('id')->toArray(),
            ]);

        $response->assertRedirect(route('game.lobby', $session->code));
        $this->assertCount(6, $session->fresh()->categories);
    }

    public function lobby_page_is_accessible()
    {
        $session = $this->createGameSession();

        $response = $this->actingAs($this->user)->get(route('game.lobby', $session->code));
        $response->assertStatus(200);
    }

    public function game_can_be_started()
    {
        $session = $this->createGameSession();
        $categories = Category::factory()->count(6)->create();

        foreach ($categories as $cat) {
            foreach (['medium', 'hard', 'very_hard'] as $diff) {
                Question::factory()->count(2)->create([
                    'category_id' => $cat->id,
                    'difficulty' => $diff,
                    'points' => $diff === 'medium' ? 250 : ($diff === 'hard' ? 500 : 750),
                    'status' => 'active',
                ]);
            }
        }

        $this->actingAs($this->user)
            ->post(route('game.categories.attach', $session->code), [
                'category_ids' => $categories->pluck('id')->toArray(),
            ]);

        $response = $this->actingAs($this->user)->post(route('game.start', $session->code));
        $response->assertRedirect(route('game.board', $session->code));
        $this->assertEquals('active', $session->fresh()->status);
    }

    public function board_page_redirects_to_lobby_if_waiting()
    {
        $session = $this->createGameSession();

        $response = $this->actingAs($this->user)->get(route('game.board', $session->code));
        $response->assertRedirect(route('game.lobby', $session->code));
    }

    public function leaderboard_page_is_accessible()
    {
        $response = $this->get(route('leaderboard'));
        $response->assertStatus(200);
    }

    public function statistics_page_is_accessible()
    {
        $response = $this->get(route('statistics'));
        $response->assertStatus(200);
    }

    private function createGameSession(): GameSession
    {
        $session = GameSession::create([
            'created_by' => $this->user->id,
            'status' => 'waiting',
            'timer_seconds' => 30,
            'total_questions' => 36,
        ]);

        GameTeam::create(['game_session_id' => $session->id, 'name' => 'Team A', 'color' => '#3B82F6']);
        GameTeam::create(['game_session_id' => $session->id, 'name' => 'Team B', 'color' => '#EF4444']);

        return $session;
    }
}
