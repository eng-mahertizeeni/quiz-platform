<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class KuraiyatPlayer extends Model
{
    use HasFactory;

    protected $table = 'kuraiyat_players';

    protected $fillable = [
        'kuraiyat_game_id', 'user_id', 'score', 'strikes',
    ];

    public function game()
    {
        return $this->belongsTo(KuraiyatGame::class, 'kuraiyat_game_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
