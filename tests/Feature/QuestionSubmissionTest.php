<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Tests\TestCase;
use PHPUnit\Framework\Attributes\Test;

class QuestionSubmissionTest extends TestCase
{
    use DatabaseMigrations;
    private User $user;
    private Category $category;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
        $this->category = Category::factory()->create();
    }

    #[Test]
    public function submit_question_page_is_accessible()
    {
        $response = $this->actingAs($this->user)->get(route('questions.submit'));
        $response->assertStatus(200);
    }

    #[Test]
    public function guests_cannot_submit_questions()
    {
        $response = $this->get(route('questions.submit'));
        $response->assertRedirect(route('login', absolute: false));
    }

    #[Test]
    public function user_can_submit_question()
    {
        $response = $this->actingAs($this->user)->post(route('questions.store'), [
            'category_id' => $this->category->id,
            'question_text' => 'What is the capital of France?',
            'answer_a' => 'Paris',
            'answer_b' => 'London',
            'answer_c' => 'Berlin',
            'answer_d' => 'Madrid',
            'correct_answer' => 'a',
            'difficulty' => 'medium',
        ]);

        $response->assertRedirect(route('questions.my-submissions'));
        $this->assertDatabaseHas('submitted_questions', [
            'question_text' => 'What is the capital of France?',
            'user_id' => $this->user->id,
        ]);
    }

    #[Test]
    public function my_submissions_page_is_accessible()
    {
        $response = $this->actingAs($this->user)->get(route('questions.my-submissions'));
        $response->assertStatus(200);
    }
}
