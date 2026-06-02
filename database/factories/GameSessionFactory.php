<?php

namespace Database\Factories;

use App\Models\GameSession;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class GameSessionFactory extends Factory
{
    protected $model = GameSession::class;

    public function definition(): array
    {
        return [
            'created_by' => User::factory(),
            'status' => 'waiting',
            'total_questions' => 36,
            'answered_questions' => 0,
            'timer_seconds' => 30,
            'sound_enabled' => true,
        ];
    }
}
