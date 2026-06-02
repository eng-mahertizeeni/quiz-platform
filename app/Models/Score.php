<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Score extends Model
{
    protected $fillable = ['user_id', 'game_session_id', 'team_id', 'score', 'is_winner'];

    protected $casts = [
        'is_winner' => 'boolean',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function gameSession()
    {
        return $this->belongsTo(GameSession::class);
    }

    public function team()
    {
        return $this->belongsTo(GameTeam::class);
    }
}