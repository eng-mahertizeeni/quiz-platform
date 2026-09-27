<?php

namespace Database\Factories;

use App\Models\Category;
use App\Models\Question;
use Illuminate\Database\Eloquent\Factories\Factory;

class QuestionFactory extends Factory
{
    protected $model = Question::class;

    public function definition(): array
    {
        return [
            'category_id' => Category::factory(),
            'question_text' => fake()->sentence() . '?',
            'answer_a' => fake()->word(),
            'answer_b' => fake()->word(),
            'answer_c' => fake()->word(),
            'answer_d' => fake()->word(),
            'correct_answer' => fake()->randomElement(['a', 'b', 'c', 'd']),
            'difficulty' => fake()->randomElement(['easy', 'medium', 'hard']),
            'points' => fake()->randomElement([200, 400, 600]),
            'status' => 'active',
            'times_used' => 0,
            'times_correct' => 0,
            'times_wrong' => 0,
        ];
    }
}
