<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BluffGame extends Model
{
    use HasFactory;

    protected $table = 'bluff_games';

    protected $fillable = [
        'bluff_creator_id', 'total_rounds', 'selected_categories', 'status', 'current_round', 'started_at', 'finished_at',
    ];

    protected function casts(): array
    {
        return [
            'selected_categories' => 'array',
            'started_at' => 'datetime',
            'finished_at' => 'datetime',
        ];
    }

    protected static function boot()
    {
        parent::boot();
        static::creating(function ($game) {
            $game->code = static::generateCode();
        });
    }

    private static function generateCode(): string
    {
        do {
            $code = strtoupper(substr(bin2hex(random_bytes(3)), 0, 6));
        } while (static::where('code', $code)->exists());
        return $code;
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'bluff_creator_id');
    }

    public function players()
    {
        return $this->hasMany(BluffPlayer::class, 'bluff_game_id');
    }

    public function rounds()
    {
        return $this->hasMany(BluffRound::class, 'bluff_game_id');
    }
}
