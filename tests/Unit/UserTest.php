<?php

namespace Tests\Unit;

use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class UserTest extends TestCase
{
    use DatabaseMigrations;
    
    public function it_checks_if_user_is_admin()
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $user = User::factory()->create(['role' => 'user']);

        $this->assertTrue($admin->isAdmin());
        $this->assertFalse($user->isAdmin());
    }

    public function it_checks_if_user_is_user()
    {
        $user = User::factory()->create(['role' => 'user']);
        $admin = User::factory()->create(['role' => 'admin']);

        $this->assertTrue($user->isUser());
        $this->assertFalse($admin->isUser());
    }

    public function it_calculates_win_rate()
    {
        $user = User::factory()->create(['games_played' => 10, 'games_won' => 7]);
        $this->assertEquals(70.0, $user->win_rate);

        $user2 = User::factory()->create(['games_played' => 0, 'games_won' => 0]);
        $this->assertEquals(0, $user2->win_rate);
    }

    public function it_generates_avatar_url()
    {
        $user = User::factory()->create(['name' => 'Test User', 'avatar' => null]);
        $this->assertStringContainsString('ui-avatars.com', $user->avatar_url);

        $user2 = User::factory()->create(['avatar' => 'avatars/test.jpg']);
        $this->assertStringContainsString('storage/avatars/test.jpg', $user2->avatar_url);
    }
}
