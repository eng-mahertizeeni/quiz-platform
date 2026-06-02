<?php

namespace Database\Factories;

use App\Models\Category;
use App\Models\SubmittedQuestion;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class SubmittedQuestionFactory extends Factory
{
    protected $model = SubmittedQuestion::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'category_id' => Category::factory(),
            'question_text' => fake()->sentence() . '?',
            'answer_a' => fake()->word(),
            'answer_b' => fake()->word(),
            'answer_c' => fake()->word(),
            'answer_d' => fake()->word(),
            'correct_answer' => fake()->randomElement(['a', 'b', 'c', 'd']),
            'difficulty' => fake()->randomElement(['medium', 'hard', 'very_hard']),
            'status' => 'pending',
        ];
    }
}
