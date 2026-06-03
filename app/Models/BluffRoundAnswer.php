<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BluffRoundAnswer extends Model
{
    protected $table = 'bluff_round_answers';

    protected $fillable = [
        'bluff_round_id', 'bluff_player_id', 'answer_text', 'is_real_fake',
    ];

    protected function casts(): array
    {
        return [
            'is_real_fake' => 'boolean',
        ];
    }

    public function round()
    {
        return $this->belongsTo(BluffRound::class, 'bluff_round_id');
    }

    public function player()
    {
        return $this->belongsTo(BluffPlayer::class, 'bluff_player_id');
    }

    public function votes()
    {
        return $this->hasMany(BluffVote::class, 'bluff_voted_answer_id');
    }
}
