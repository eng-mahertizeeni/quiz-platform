<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BluffQuestion extends Model
{
    protected $table = 'bluff_questions';

    protected $fillable = [
        'question_text', 'correct_answer', 'category_id', 'difficulty',
    ];

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function rounds()
    {
        return $this->hasMany(BluffRound::class, 'bluff_question_id');
    }
}
