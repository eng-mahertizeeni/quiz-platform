<?php

namespace Tests\Unit;

use App\Models\Question;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Tests\TestCase;
use PHPUnit\Framework\Attributes\Test;

class QuestionTest extends TestCase
{
    use DatabaseMigrations;
    
    #[Test]
    public function it_calculates_success_rate()
    {
        $question = Question::factory()->create([
            'times_used' => 10,
            'times_correct' => 7,
            'times_wrong' => 3,
        ]);
        $this->assertEquals(70.0, $question->success_rate);
    }

    #[Test]
    public function it_returns_zero_success_rate_when_unused()
    {
        $question = Question::factory()->create([
            'times_used' => 0,
            'times_correct' => 0,
            'times_wrong' => 0,
        ]);
        $this->assertEquals(0, $question->success_rate);
    }

    #[Test]
    public function it_returns_answers_as_array()
    {
        $question = Question::factory()->create([
            'answer_a' => 'Answer A',
            'answer_b' => 'Answer B',
            'answer_c' => 'Answer C',
            'answer_d' => 'Answer D',
        ]);
        $answers = $question->answers;
        $this->assertEquals(['a' => 'Answer A', 'b' => 'Answer B', 'c' => 'Answer C', 'd' => 'Answer D'], $answers);
    }

    #[Test]
    public function it_returns_difficulty_label()
    {
        $q1 = Question::factory()->create(['difficulty' => 'easy']);
        $q2 = Question::factory()->create(['difficulty' => 'medium']);
        $q3 = Question::factory()->create(['difficulty' => 'hard']);

        $this->assertEquals('سهل', $q1->difficulty_label);
        $this->assertEquals('متوسط', $q2->difficulty_label);
        $this->assertEquals('صعب', $q3->difficulty_label);
    }
}
