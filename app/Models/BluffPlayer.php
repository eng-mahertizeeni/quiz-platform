<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BluffPlayer extends Model
{
    protected $table = 'bluff_players';

    protected $fillable = [
        'bluff_game_id', 'user_id', 'total_score', 'display_name',
    ];

    public function game()
    {
        return $this->belongsTo(BluffGame::class, 'bluff_game_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function answers()
    {
        return $this->hasMany(BluffRoundAnswer::class, 'bluff_player_id');
    }

    public function getDisplayNameAttribute($value)
    {
        return $value ?? $this->user->name;
    }
}
