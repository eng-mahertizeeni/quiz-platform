<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GameRound extends Model
{
    protected $fillable = [
        'game_session_id', 'question_id', 'category_id',
        'assigned_team_id', 'answered_by_team_id',
        'status', 'is_stolen', 'points_value', 'round_number',
    ];

    protected $casts = [
        'is_stolen' => 'boolean',
        'points_value' => 'integer',
        'round_number' => 'integer',
    ];

    public function gameSession()
    {
        return $this->belongsTo(GameSession::class);
    }

    public function question()
    {
        return $this->belongsTo(Question::class);
    }

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function assignedTeam()
    {
        return $this->belongsTo(GameTeam::class, 'assigned_team_id');
    }

    public function answeredByTeam()
    {
        return $this->belongsTo(GameTeam::class, 'answered_by_team_id');
    }

    public function answer()
    {
        return $this->hasOne(GameAnswer::class);
    }

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeAnswered($query)
    {
        return $query->where('status', 'answered');
    }
}