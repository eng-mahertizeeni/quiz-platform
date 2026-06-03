<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class GameTeamResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'color' => $this->color,
            'score' => (int) $this->score,
            'correct_answers' => (int) $this->correct_answers,
            'wrong_answers' => (int) $this->wrong_answers,
            'is_winner' => (bool) $this->is_winner,
            'can_use_remove_power' => $this->canUseRemovePower(),
            'can_use_steal_power' => $this->canUseStealPower(),
        ];
    }
}
