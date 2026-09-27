<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Category extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name', 'name_ar', 'slug', 'description', 'icon',
        'thumbnail', 'color', 'is_featured', 'is_active',
        'sort_order', 'times_played',
    ];

    protected $casts = [
        'is_featured' => 'boolean',
        'is_active' => 'boolean',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($category) {
            if (empty($category->slug)) {
                $category->slug = Str::slug($category->name);
            }
        });
    }

    public function questions()
    {
        return $this->hasMany(Question::class);
    }

    public function activeQuestions()
    {
        return $this->hasMany(Question::class)->where('status', 'active');
    }

    public function questionsByDifficulty(string $difficulty)
    {
        return $this->activeQuestions()->where('difficulty', $difficulty);
    }

    public function gameSessions()
    {
        return $this->belongsToMany(GameSession::class, 'game_session_categories');
    }

    public function hasEnoughQuestions(): bool
    {
        foreach (array_keys(Question::DIFFICULTY_POINTS) as $difficulty) {
            if ($this->questionsByDifficulty($difficulty)->count() < 2) {
                return false;
            }
        }

        return true;
    }

    public function getThumbnailUrlAttribute(): string
    {
        if ($this->thumbnail) {
            return asset('storage/' . $this->thumbnail);
        }
        return asset('images/categories/default.png');
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeFeatured($query)
    {
        return $query->where('is_featured', true);
    }
}