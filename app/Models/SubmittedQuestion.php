<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SubmittedQuestion extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id', 'category_id', 'question_text',
        'answer_a', 'answer_b', 'answer_c', 'answer_d',
        'correct_answer', 'difficulty', 'image',
        'status', 'rejection_reason', 'reviewed_by',
        'reviewed_at', 'converted_question_id',
    ];

    protected $casts = [
        'reviewed_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function reviewer()
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    public function convertedQuestion()
    {
        return $this->belongsTo(Question::class, 'converted_question_id');
    }

    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    public function isApproved(): bool
    {
        return $this->status === 'approved';
    }

    public function isRejected(): bool
    {
        return $this->status === 'rejected';
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

    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            'pending' => 'قيد المراجعة',
            'approved' => 'مقبول',
            'rejected' => 'مرفوض',
            default => $this->status,
        };
    }

    public function getStatusColorAttribute(): string
    {
        return match ($this->status) {
            'pending' => 'warning',
            'approved' => 'success',
            'rejected' => 'danger',
            default => 'secondary',
        };
    }
}