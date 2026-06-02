<?php

namespace Tests\Unit;

use App\Models\GameSession;
use App\Models\GameTeam;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Tests\TestCase;
use PHPUnit\Framework\Attributes\Test;

class GameSessionTest extends TestCase
{
    use DatabaseMigrations;
    #[Test]
    public function it_generates_code_on_create()
    {
        $user = User::factory()->create();
        $session = GameSession::create([
            'created_by' => $user->id,
            'status' => 'waiting',
        ]);
        $this->assertNotNull($session->code);
        $this->assertEquals(8, strlen($session->code));
    }

    #[Test]
    public function it_checks_if_active()
    {
        $active = GameSession::factory()->create(['status' => 'active']);
        $finished = GameSession::factory()->create(['status' => 'finished']);

        $this->assertTrue($active->isActive());
        $this->assertFalse($finished->isActive());
    }

    #[Test]
    public function it_checks_if_finished()
    {
        $finished = GameSession::factory()->create(['status' => 'finished']);
        $active = GameSession::factory()->create(['status' => 'active']);

        $this->assertTrue($finished->isFinished());
        $this->assertFalse($active->isFinished());
    }

    #[Test]
    public function it_calculates_progress_percentage()
    {
        $session = GameSession::factory()->create([
            'total_questions' => 36,
            'answered_questions' => 9,
        ]);
        $this->assertEquals(25.0, $session->progress_percentage);
    }

    #[Test]
    public function it_returns_zero_progress_when_no_questions()
    {
        $session = GameSession::factory()->create([
            'total_questions' => 0,
            'answered_questions' => 0,
        ]);
        $this->assertEquals(0, $session->progress_percentage);
    }
}
