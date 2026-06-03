<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BluffRound extends Model
{
    protected $table = 'bluff_rounds';

    protected $fillable = [
        'bluff_game_id', 'bluff_question_id', 'round_number', 'status',
    ];

    public function game()
    {
        return $this->belongsTo(BluffGame::class, 'bluff_game_id');
    }

    public function question()
    {
        return $this->belongsTo(BluffQuestion::class, 'bluff_question_id');
    }

    public function answers()
    {
        return $this->hasMany(BluffRoundAnswer::class, 'bluff_round_id');
    }

    public function votes()
    {
        return $this->hasMany(BluffVote::class, 'bluff_round_id');
    }
}
