<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class GameRoundResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'round_number' => $this->round_number,
            'status' => $this->status,
            'points_value' => (int) $this->points_value,
            'is_stolen' => (bool) $this->is_stolen,
            'question' => new QuestionResource($this->whenLoaded('question')),
            'category' => new CategoryResource($this->whenLoaded('category')),
            'assigned_team' => new GameTeamResource($this->whenLoaded('assignedTeam')),
            'answered_by_team' => new GameTeamResource($this->whenLoaded('answeredByTeam')),
        ];
    }
}
