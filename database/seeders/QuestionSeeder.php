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
            require __DIR__ . '/questions/literature-poetry.php',
            require __DIR__ . '/questions/nature-animals.php',
            require __DIR__ . '/questions/islamic-knowledge.php',
            require __DIR__ . '/questions/food-cuisine.php',
            require __DIR__ . '/questions/greek-mythology.php',
            require __DIR__ . '/questions/philosophy.php',
            require __DIR__ . '/questions/artificial-intelligence.php',
            require __DIR__ . '/questions/psychology.php',
            require __DIR__ . '/questions/natural-phenomena.php',
            require __DIR__ . '/questions/world-languages.php',
            require __DIR__ . '/questions/harry-potter.php',
            require __DIR__ . '/questions/marvel.php',
            require __DIR__ . '/questions/netflix-series.php',
            require __DIR__ . '/questions/korean-culture.php',
            require __DIR__ . '/questions/esports.php',
            require __DIR__ . '/questions/energy-environment.php',
            require __DIR__ . '/questions/puzzles-logic.php',
            require __DIR__ . '/questions/exploration.php',
            require __DIR__ . '/questions/digital-culture.php',
            require __DIR__ . '/questions/cartoon-characters.php',
            require __DIR__ . '/questions/rulers-presidents.php',
            require __DIR__ . '/questions/human-body.php',
            require __DIR__ . '/questions/inventions.php',
            require __DIR__ . '/questions/art-music.php',
            require __DIR__ . '/questions/combat-sports.php',
            require __DIR__ . '/questions/legends-myths.php',
            require __DIR__ . '/questions/famous-scientists.php',
            require __DIR__ . '/questions/predators.php',
            require __DIR__ . '/questions/basketball.php',
            require __DIR__ . '/questions/tennis.php',
            require __DIR__ . '/questions/formula-1.php',
            require __DIR__ . '/questions/wwe.php',
            require __DIR__ . '/questions/disney-characters.php',
            require __DIR__ . '/questions/olympics.php',
            require __DIR__ . '/questions/the-walking-dead.php',
            require __DIR__ . '/questions/sherlock.php',
            require __DIR__ . '/questions/peaky-blinders.php',
            require __DIR__ . '/questions/the-office.php',
            require __DIR__ . '/questions/friends.php',
            require __DIR__ . '/questions/the-boys.php',
            require __DIR__ . '/questions/dark.php',
            require __DIR__ . '/questions/money-heist-korea.php',
            require __DIR__ . '/questions/game-of-thrones.php',
            require __DIR__ . '/questions/economics-finance.php',
            require __DIR__ . '/questions/general-medicine.php',
            require __DIR__ . '/questions/medications.php',
            require __DIR__ . '/questions/smart-apps.php',
            require __DIR__ . '/questions/world-news.php',
            require __DIR__ . '/questions/special-dates.php',
            require __DIR__ . '/questions/football-stadiums.php',
            require __DIR__ . '/questions/world-cup.php',
            require __DIR__ . '/questions/champions-league.php',
            require __DIR__ . '/questions/premier-league.php',
            require __DIR__ . '/questions/real-madrid.php',
            require __DIR__ . '/questions/barcelona.php',
            require __DIR__ . '/questions/historical-leaders.php',
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
