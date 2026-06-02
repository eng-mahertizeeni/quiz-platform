<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Question extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'category_id', 'created_by', 'question_text', 'question_text_ar',
        'answer_a', 'answer_b', 'answer_c', 'answer_d',
        'correct_answer', 'difficulty', 'points', 'image',
        'status', 'times_used', 'times_correct', 'times_wrong',
    ];

    protected $casts = [
        'points' => 'integer',
        'times_used' => 'integer',
        'times_correct' => 'integer',
        'times_wrong' => 'integer',
    ];

    const DIFFICULTY_POINTS = [
        'medium' => 250,
        'hard' => 500,
        'very_hard' => 750,
    ];

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function gameRounds()
    {
        return $this->hasMany(GameRound::class);
    }

    public function gameAnswers()
    {
        return $this->hasMany(GameAnswer::class);
    }

    public function statistics()
    {
        return $this->hasMany(QuestionStatistic::class);
    }

    public function getAnswersAttribute(): array
    {
        return [
            'a' => $this->answer_a,
            'b' => $this->answer_b,
            'c' => $this->answer_c,
            'd' => $this->answer_d,
        ];
    }

    public function getCorrectAnswerTextAttribute(): string
    {
        return $this->{'answer_' . $this->correct_answer};
    }

    public function getSuccessRateAttribute(): float
    {
        if ($this->times_used === 0) {
            return 0;
        }
        return round(($this->times_correct / $this->times_used) * 100, 1);
    }

    public function getImageUrlAttribute(): ?string
    {
        return $this->image ? asset('storage/' . $this->image) : null;
    }

    public function getDifficultyLabelAttribute(): string
    {
        return match ($this->difficulty) {
            'medium' => 'متوسط',
            'hard' => 'صعب',
            'very_hard' => 'صعب جداً',
            default => $this->difficulty,
        };
    }

    public function getDifficultyColorAttribute(): string
    {
        return match ($this->difficulty) {
            'medium' => '#F59E0B',
            'hard' => '#EF4444',
            'very_hard' => '#7C3AED',
            default => '#6B7280',
        };
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopeForGame($query, int $categoryId, string $difficulty, int $limit = 2)
    {
        return $query->where('category_id', $categoryId)
            ->where('difficulty', $difficulty)
            ->where('status', 'active')
            ->inRandomOrder()
            ->limit($limit);
    }
}