<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class GameSessionResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'code' => $this->code,
            'status' => $this->status,
            'current_round' => $this->current_round,
            'total_questions' => $this->total_questions,
            'answered_questions' => $this->answered_questions,
            'timer_seconds' => $this->timer_seconds,
            'sound_enabled' => (bool) $this->sound_enabled,
            'progress_percentage' => (float) $this->progress_percentage,
            'creator' => new UserResource($this->whenLoaded('creator')),
            'teams' => GameTeamResource::collection($this->whenLoaded('teams')),
            'categories' => CategoryResource::collection($this->whenLoaded('categories')),
            'started_at' => $this->started_at,
            'finished_at' => $this->finished_at,
            'created_at' => $this->created_at,
        ];
    }
}
