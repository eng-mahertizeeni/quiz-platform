<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GameTeam extends Model
{
    use HasFactory;

    protected $fillable = [
        'game_session_id', 'name', 'color', 'score',
        'correct_answers', 'wrong_answers', 'is_winner',
        'power_remove_used', 'power_steal_used',
    ];

    protected $casts = [
        'is_winner' => 'boolean',
        'power_remove_used' => 'boolean',
        'power_steal_used' => 'boolean',
    ];

    public function gameSession()
    {
        return $this->belongsTo(GameSession::class);
    }

    public function rounds()
    {
        return $this->hasMany(GameRound::class, 'assigned_team_id');
    }

    public function answers()
    {
        return $this->hasMany(GameAnswer::class, 'team_id');
    }

    public function powerUsages()
    {
        return $this->hasMany(PowerUsage::class, 'team_id');
    }

    public function canUseRemovePower(): bool
    {
        return !$this->power_remove_used;
    }

    public function canUseStealPower(): bool
    {
        return !$this->power_steal_used;
    }
}