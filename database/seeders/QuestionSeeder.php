<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Question;
use Illuminate\Database\Seeder;

class QuestionSeeder extends Seeder
{
    public function run(): void
    {
        $categories = Category::all()->keyBy('slug');

        $questions = array_merge(
            require __DIR__ . '/questions/football.php',
            require __DIR__ . '/questions/movies.php',
            require __DIR__ . '/questions/technology.php',
            require __DIR__ . '/questions/geography.php',
            require __DIR__ . '/questions/history.php',
            require __DIR__ . '/questions/science.php',
            require __DIR__ . '/questions/anime.php',
            require __DIR__ . '/questions/gaming.php',
            require __DIR__ . '/questions/general.php',
            require __DIR__ . '/questions/cars.php',
            require __DIR__ . '/questions/space.php',
            require __DIR__ . '/questions/sports.php',
            require __DIR__ . '/questions/football-career.php',
            require __DIR__ . '/questions/syrian-drama.php',
            require __DIR__ . '/questions/health.php',
            require __DIR__ . '/questions/historical-figures.php',
            require __DIR__ . '/questions/quotes.php',
            require __DIR__ . '/questions/capitals.php',
        );

        foreach ($questions as $q) {
            $category = $categories->get($q['category']);
            if (!$category) continue;

            Question::create([
                'category_id' => $category->id,
                'question_text' => $q['question'],
                'answer_a' => $q['a'],
                'answer_b' => $q['b'],
                'answer_c' => $q['c'],
                'answer_d' => $q['d'],
                'correct_answer' => $q['correct'],
                'difficulty' => $q['difficulty'],
                'points' => match ($q['difficulty']) {
                    'medium' => 250,
                    'hard' => 500,
                    'very_hard' => 750,
                },
                'status' => 'active',
            ]);
        }
    }
}
