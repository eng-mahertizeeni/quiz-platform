<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Tests\TestCase;
use PHPUnit\Framework\Attributes\Test;

class ProfileTest extends TestCase
{
    use DatabaseMigrations;
    private User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
    }

    public function profile_page_is_accessible()
    {
        $response = $this->actingAs($this->user)->get(route('profile.edit'));
        $response->assertStatus(200);
    }

    public function profile_can_be_updated()
    {
        $response = $this->actingAs($this->user)->patch(route('profile.update'), [
            'name' => 'New Name',
            'username' => 'newusername',
            'email' => $this->user->email,
        ]);

        $response->assertSessionHas('success');
        $this->assertEquals('New Name', $this->user->fresh()->name);
        $this->assertEquals('newusername', $this->user->fresh()->username);
    }

    public function password_can_be_updated()
    {
        $response = $this->actingAs($this->user)->put(route('profile.password'), [
            'current_password' => 'password',
            'password' => 'new-password',
            'password_confirmation' => 'new-password',
        ]);

        $response->assertSessionHas('success');
    }

    public function account_can_be_deleted()
    {
        $response = $this->actingAs($this->user)->delete(route('profile.destroy'), [
            'password' => 'password',
        ]);

        $response->assertRedirect(route('home'));
        $this->assertGuest();
        $this->assertSoftDeleted($this->user);
    }
}
