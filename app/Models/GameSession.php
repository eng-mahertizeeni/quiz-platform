<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class GameSession extends Model
{
    use HasFactory;

    protected $fillable = [
        'code', 'created_by', 'status', 'current_team_id',
        'current_round', 'total_questions', 'answered_questions',
        'timer_seconds', 'sound_enabled', 'started_at', 'finished_at',
    ];

    protected $casts = [
        'started_at' => 'datetime',
        'finished_at' => 'datetime',
        'sound_enabled' => 'boolean',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($session) {
            $session->code = strtoupper(Str::random(8));
        });
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function teams()
    {
        return $this->hasMany(GameTeam::class);
    }

    public function currentTeam()
    {
        return $this->belongsTo(GameTeam::class, 'current_team_id');
    }

    public function categories()
    {
        return $this->belongsToMany(Category::class, 'game_session_categories')
            ->withPivot('sort_order')
            ->orderBy('game_session_categories.sort_order');
    }

    public function rounds()
    {
        return $this->hasMany(GameRound::class);
    }

    public function answers()
    {
        return $this->hasMany(GameAnswer::class);
    }

    public function scores()
    {
        return $this->hasMany(Score::class);
    }

    public function getTeamOneAttribute(): ?GameTeam
    {
        return $this->teams->first();
    }

    public function getTeamTwoAttribute(): ?GameTeam
    {
        return $this->teams->skip(1)->first();
    }

    public function getWinnerAttribute(): ?GameTeam
    {
        if ($this->relationLoaded('teams')) {
            return $this->teams->sortByDesc('score')->first();
        }
        return $this->teams()->orderByDesc('score')->first();
    }

    public function getProgressPercentageAttribute(): float
    {
        if ($this->total_questions === 0) {
            return 0;
        }
        return round(($this->answered_questions / $this->total_questions) * 100, 1);
    }

    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    public function isFinished(): bool
    {
        return $this->status === 'finished';
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopeFinished($query)
    {
        return $query->where('status', 'finished');
    }
}