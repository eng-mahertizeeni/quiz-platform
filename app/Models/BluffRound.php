<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BluffRound extends Model
{
    protected $table = 'bluff_rounds';

    protected $fillable = [
        'bluff_game_id', 'bluff_question_id', 'selected_by_player_id', 'category_id', 'round_number', 'status',
    ];

    public function game()
    {
        return $this->belongsTo(BluffGame::class, 'bluff_game_id');
    }

    public function question()
    {
        return $this->belongsTo(BluffQuestion::class, 'bluff_question_id');
    }

    public function selector()
    {
        return $this->belongsTo(BluffPlayer::class, 'selected_by_player_id');
    }

    public function category()
    {
        return $this->belongsTo(Category::class, 'category_id');
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
