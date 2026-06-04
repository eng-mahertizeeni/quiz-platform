<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class KuraiyatRound extends Model
{
    use HasFactory;

    protected $table = 'kuraiyat_rounds';

    protected $fillable = [
        'kuraiyat_game_id', 'round_number', 'question_id', 'status', 'clue_level',
        'answered_by', 'answer', 'is_correct',
        'answered_by_p1', 'answer_p1', 'is_correct_p1',
        'answered_by_p2', 'answer_p2', 'is_correct_p2',
    ];

    protected $casts = [
        'is_correct' => 'boolean',
    ];

    public function game()
    {
        return $this->belongsTo(KuraiyatGame::class, 'kuraiyat_game_id');
    }

    public function question()
    {
        return $this->belongsTo(Question::class);
    }

    public function answeredBy()
    {
        return $this->belongsTo(KuraiyatPlayer::class, 'answered_by');
    }
}
