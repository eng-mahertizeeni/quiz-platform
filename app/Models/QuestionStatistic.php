<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class QuestionStatistic extends Model
{
    protected $fillable = [
        'question_id', 'date', 'times_shown',
        'times_correct', 'times_wrong', 'avg_time_taken',
    ];

    protected $casts = [
        'date' => 'date',
    ];

    public function question()
    {
        return $this->belongsTo(Question::class);
    }
}