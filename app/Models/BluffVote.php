<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BluffVote extends Model
{
    protected $table = 'bluff_votes';

    protected $fillable = [
        'bluff_round_id', 'bluff_voter_id', 'bluff_voted_answer_id',
    ];

    public function round()
    {
        return $this->belongsTo(BluffRound::class, 'bluff_round_id');
    }

    public function voter()
    {
        return $this->belongsTo(BluffPlayer::class, 'bluff_voter_id');
    }

    public function answer()
    {
        return $this->belongsTo(BluffRoundAnswer::class, 'bluff_voted_answer_id');
    }
}
