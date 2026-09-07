<?php

namespace Tests\Feature\Admin;

use App\Models\Category;
use App\Models\Question;
use App\Models\SubmittedQuestion;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Tests\TestCase;
use PHPUnit\Framework\Attributes\Test;

class AdminTest extends TestCase
{
    use DatabaseMigrations;
    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->admin = User::factory()->create(['role' => 'admin']);
    }

    public function admin_dashboard_is_accessible()
    {
        $response = $this->actingAs($this->admin)->get(route('admin.dashboard'));
        $response->assertStatus(200);
    }

    public function non_admin_cannot_access_admin()
    {
        $user = User::factory()->create(['role' => 'user']);

        $response = $this->actingAs($user)->get(route('admin.dashboard'));
        $response->assertStatus(403);
    }

    public function categories_index_is_accessible()
    {
        $response = $this->actingAs($this->admin)->get(route('admin.categories.index'));
        $response->assertStatus(200);
    }

    public function category_can_be_created()
    {
        $response = $this->actingAs($this->admin)->post(route('admin.categories.store'), [
            'name' => 'New Category',
            'name_ar' => 'فئة جديدة',
            'icon' => 'tag',
            'color' => '#FF0000',
            'is_active' => '1',
        ]);

        $response->assertRedirect(route('admin.categories.index'));
        $this->assertDatabaseHas('categories', ['name' => 'New Category']);
    }

    public function category_can_be_updated()
    {
        $category = Category::factory()->create();

        $response = $this->actingAs($this->admin)->put(route('admin.categories.update', $category), [
            'name' => 'Updated Name',
            'icon' => 'star',
            'color' => '#00FF00',
        ]);

        $response->assertRedirect(route('admin.categories.index'));
        $this->assertDatabaseHas('categories', ['name' => 'Updated Name']);
    }

    public function category_can_be_deleted()
    {
        $category = Category::factory()->create();

        $response = $this->actingAs($this->admin)->delete(route('admin.categories.destroy', $category));
        $response->assertSessionHas('success');
        $this->assertSoftDeleted($category);
    }

    public function questions_index_is_accessible()
    {
        $response = $this->actingAs($this->admin)->get(route('admin.questions.index'));
        $response->assertStatus(200);
    }

    public function question_can_be_created()
    {
        $category = Category::factory()->create();

        $response = $this->actingAs($this->admin)->post(route('admin.questions.store'), [
            'category_id' => $category->id,
            'question_text' => 'Test question?',
            'answer_a' => 'Answer A',
            'answer_b' => 'Answer B',
            'answer_c' => 'Answer C',
            'answer_d' => 'Answer D',
            'correct_answer' => 'a',
            'difficulty' => 'medium',
        ]);

        $response->assertRedirect(route('admin.questions.index'));
        $this->assertDatabaseHas('questions', ['question_text' => 'Test question?']);
    }

    public function submissions_page_is_accessible()
    {
        $response = $this->actingAs($this->admin)->get(route('admin.questions.submissions'));
        $response->assertStatus(200);
    }

    public function submission_can_be_approved()
    {
        $category = Category::factory()->create();
        $submission = SubmittedQuestion::factory()->create([
            'category_id' => $category->id,
            'status' => 'pending',
            'difficulty' => 'medium',
        ]);

        $response = $this->actingAs($this->admin)->post(route('admin.questions.approve', $submission));
        $response->assertRedirect(route('admin.questions.submissions'));

        $this->assertEquals('approved', $submission->fresh()->status);
        $this->assertNotNull($submission->fresh()->converted_question_id);
    }

    public function submission_can_be_rejected()
    {
        $submission = SubmittedQuestion::factory()->create(['status' => 'pending']);

        $response = $this->actingAs($this->admin)->post(route('admin.questions.reject', $submission), [
            'reason' => 'Not relevant',
        ]);

        $response->assertRedirect(route('admin.questions.submissions'));
        $this->assertEquals('rejected', $submission->fresh()->status);
        $this->assertEquals('Not relevant', $submission->fresh()->rejection_reason);
    }

    public function users_index_is_accessible()
    {
        $response = $this->actingAs($this->admin)->get(route('admin.users.index'));
        $response->assertStatus(200);
    }

    public function user_show_page_is_accessible()
    {
        $user = User::factory()->create();

        $response = $this->actingAs($this->admin)->get(route('admin.users.show', $user));
        $response->assertStatus(200);
    }

    public function user_can_be_toggled()
    {
        $user = User::factory()->create(['is_active' => true]);

        $response = $this->actingAs($this->admin)->post(route('admin.users.toggle-status', $user));

        $response->assertSessionHas('success');
        $this->assertFalse($user->fresh()->is_active);
    }

    public function admin_cannot_be_toggled()
    {
        $response = $this->actingAs($this->admin)->post(route('admin.users.toggle-status', $this->admin));
        $response->assertSessionHas('error');
    }

    public function user_can_be_deleted()
    {
        $user = User::factory()->create();

        $response = $this->actingAs($this->admin)->delete(route('admin.users.destroy', $user));
        $response->assertSessionHas('success');
        $this->assertSoftDeleted($user);
    }
}
