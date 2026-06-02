<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable, SoftDeletes;

    protected $fillable = [
        'name', 'username', 'email', 'password', 'role',
        'avatar', 'total_score', 'games_played', 'games_won', 'is_active',
    ];

    protected $hidden = ['password', 'remember_token'];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
        'is_active' => 'boolean',
    ];

    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    public function isUser(): bool
    {
        return $this->role === 'user';
    }

    public function gameSessions()
    {
        return $this->hasMany(GameSession::class, 'created_by');
    }

    public function submittedQuestions()
    {
        return $this->hasMany(SubmittedQuestion::class);
    }

    public function scores()
    {
        return $this->hasMany(Score::class);
    }

    public function questions()
    {
        return $this->hasMany(Question::class, 'created_by');
    }

    public function questionAnswers()
    {
        return $this->hasMany(UserQuestionAnswer::class);
    }

    public function getWinRateAttribute(): float
    {
        if ($this->games_played === 0) {
            return 0;
        }
        return round(($this->games_won / $this->games_played) * 100, 1);
    }

    public function getAvatarUrlAttribute(): string
    {
        if ($this->avatar) {
            return asset('storage/' . $this->avatar);
        }
        return 'https://ui-avatars.com/api/?name=' . urlencode($this->name) . '&background=1e293b&color=f59e0b&bold=true';
    }
}