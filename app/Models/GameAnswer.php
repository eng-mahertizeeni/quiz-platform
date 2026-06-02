<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GameAnswer extends Model
{
    protected $fillable = [
        'game_session_id', 'game_round_id', 'team_id', 'question_id',
        'selected_answer', 'is_correct', 'points_earned',
        'time_taken', 'power_remove_used', 'power_steal_used',
    ];

    protected $casts = [
        'is_correct' => 'boolean',
        'power_remove_used' => 'boolean',
        'power_steal_used' => 'boolean',
    ];

    public function gameSession()
    {
        return $this->belongsTo(GameSession::class);
    }

    public function round()
    {
        return $this->belongsTo(GameRound::class, 'game_round_id');
    }

    public function team()
    {
        return $this->belongsTo(GameTeam::class);
    }

    public function question()
    {
        return $this->belongsTo(Question::class);
    }
}