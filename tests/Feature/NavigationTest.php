<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\GameSession;
use App\Models\GameTeam;
use App\Models\Question;
use App\Models\SubmittedQuestion;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Tests\TestCase;

class NavigationTest extends TestCase
{
    use DatabaseMigrations;

    private User $admin;
    private User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->create(['role' => 'admin', 'is_active' => true]);
        $this->user = User::factory()->create(['role' => 'user', 'is_active' => true]);
    }

    public function test_public_routes(): void
    {
        $this->get(route('home'))->assertOk();
        $this->get(route('leaderboard'))->assertOk();
        $this->get(route('statistics'))->assertOk();
        $this->get(route('login'))->assertOk();
        $this->get(route('register'))->assertOk();
    }

    public function test_public_routes_after_seeding(): void
    {
        $this->seed();

        $this->get(route('home'))->assertOk();
        $this->get(route('leaderboard'))->assertOk();
        $this->get(route('statistics'))->assertOk();
    }

    public function test_authenticated_user_routes(): void
    {
        $this->actingAs($this->user);
        $this->seed();

        $this->get(route('dashboard'))->assertOk();
        $this->get(route('profile.edit'))->assertOk();
        $this->get(route('questions.submit'))->assertOk();
        $this->get(route('questions.my-submissions'))->assertOk();
        $this->get(route('game.create'))->assertOk();
    }

    public function test_authenticated_user_routes_no_seed(): void
    {
        $this->actingAs($this->user);

        $this->get(route('dashboard'))->assertOk();
        $this->get(route('questions.my-submissions'))->assertOk();
    }

    public function test_admin_routes(): void
    {
        $this->actingAs($this->admin);
        $this->seed();

        $this->get(route('admin.dashboard'))->assertOk();
        $this->get(route('admin.categories.index'))->assertOk();
        $this->get(route('admin.categories.create'))->assertOk();
        $this->get(route('admin.questions.index'))->assertOk();
        $this->get(route('admin.questions.create'))->assertOk();
        $this->get(route('admin.questions.submissions'))->assertOk();
        $this->get(route('admin.users.index'))->assertOk();

        $user = User::where('role', 'user')->first();
        if ($user) {
            $this->get(route('admin.users.show', $user))->assertOk();
        }

        $category = Category::first();
        if ($category) {
            $this->get(route('admin.categories.edit', $category))->assertOk();
        }

        $question = Question::first();
        if ($question) {
            $this->get(route('admin.questions.edit', $question))->assertOk();
        }

        $submission = SubmittedQuestion::first();
        if ($submission) {
            $this->get(route('admin.questions.review', $submission))->assertOk();
        }
    }

    public function test_admin_routes_without_seed(): void
    {
        $this->actingAs($this->admin);

        $this->get(route('admin.dashboard'))->assertOk();
        $this->get(route('admin.categories.index'))->assertOk();
        $this->get(route('admin.categories.create'))->assertOk();
        $this->get(route('admin.questions.index'))->assertOk();
        $this->get(route('admin.questions.create'))->assertOk();
        $this->get(route('admin.questions.submissions'))->assertOk();
        $this->get(route('admin.users.index'))->assertOk();
    }

    public function test_admin_middleware_blocks_non_admin(): void
    {
        $this->actingAs($this->user);

        $adminRoutes = [
            'admin.dashboard', 'admin.categories.index', 'admin.categories.create',
            'admin.questions.index', 'admin.questions.create',
            'admin.questions.submissions', 'admin.users.index',
        ];

        foreach ($adminRoutes as $route) {
            $this->get(route($route))->assertForbidden();
        }
    }

    public function test_auth_middleware_blocks_guests(): void
    {
        $guestRoutes = [
            'dashboard', 'profile.edit', 'questions.submit',
            'questions.my-submissions', 'game.create',
        ];

        foreach ($guestRoutes as $route) {
            $this->get(route($route))->assertRedirect(route('login'));
        }
    }

    public function test_game_creation_flow(): void
    {
        $this->actingAs($this->user);
        $this->seed();

        $response = $this->post(route('game.store'), [
            'team_one_name' => 'الفريق الأزرق',
            'team_two_name' => 'الفريق الأحمر',
            'timer_seconds' => 30,
        ]);

        $response->assertRedirect();
        $code = GameSession::first()->code;

        $this->get(route('game.categories', $code))->assertOk();

        $categories = Category::active()->get();
        $validIds = $categories->filter(fn($c) => $c->hasEnoughQuestions())->pluck('id')->take(6)->toArray();

        if (count($validIds) === 6) {
            $this->post(route('game.categories.attach', $code), [
                'category_ids' => $validIds,
            ])->assertRedirect(route('game.lobby', $code));

            $this->get(route('game.lobby', $code))->assertOk();
        }
    }

    public function test_blocked_user_redirected(): void
    {
        $blocked = User::factory()->create(['is_active' => false, 'role' => 'user']);
        $this->actingAs($blocked);

        $this->get(route('dashboard'))->assertRedirect(route('login'));
    }

    public function test_admin_can_see_user_detail(): void
    {
        $this->actingAs($this->admin);
        $response = $this->get(route('admin.users.show', $this->user));
        $response->assertOk();
    }

    public function test_game_route_policy_blocks_non_creator(): void
    {
        $this->seed();
        $this->actingAs($this->user);

        $session = GameSession::factory()->create(['created_by' => $this->admin->id]);
        GameTeam::create(['game_session_id' => $session->id, 'name' => 'Team A', 'color' => '#3B82F6']);
        GameTeam::create(['game_session_id' => $session->id, 'name' => 'Team B', 'color' => '#EF4444']);

        $this->get(route('game.lobby', $session->code))->assertForbidden();
    }
}
