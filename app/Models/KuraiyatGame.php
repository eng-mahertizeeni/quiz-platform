<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class KuraiyatGame extends Model
{
    use HasFactory;

    protected $table = 'kuraiyat_games';

    protected $fillable = [
        'code', 'type', 'status', 'created_by',
        'current_round', 'total_rounds', 'total_questions',
        'started_at', 'finished_at',
    ];

    protected $casts = [
        'started_at' => 'datetime',
        'finished_at' => 'datetime',
    ];

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
            $code = strtoupper(Str::random(6));
        } while (static::where('code', $code)->exists());
        return $code;
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function players()
    {
        return $this->hasMany(KuraiyatPlayer::class, 'kuraiyat_game_id');
    }

    public function rounds()
    {
        return $this->hasMany(KuraiyatRound::class, 'kuraiyat_game_id');
    }
}
