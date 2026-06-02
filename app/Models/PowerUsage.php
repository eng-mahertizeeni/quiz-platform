<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PowerUsage extends Model
{
    protected $fillable = ['game_session_id', 'team_id', 'game_round_id', 'power_type'];

    public function team()
    {
        return $this->belongsTo(GameTeam::class);
    }

    public function round()
    {
        return $this->belongsTo(GameRound::class, 'game_round_id');
    }
}