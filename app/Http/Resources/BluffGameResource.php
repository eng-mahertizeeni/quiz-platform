<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class BluffGameResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'code' => $this->code,
            'status' => $this->status,
            'current_round' => $this->current_round,
            'total_rounds' => $this->total_rounds,
            'creator' => new UserResource($this->whenLoaded('creator')),
            'players' => $this->whenLoaded('players', function () {
                return $this->players->map(fn($p) => [
                    'id' => $p->id,
                    'user_id' => $p->user_id,
                    'name' => $p->user->name,
                    'avatar' => $p->user->avatar_url,
                    'total_score' => (int) $p->total_score,
                ]);
            }),
            'started_at' => $this->started_at,
            'finished_at' => $this->finished_at,
            'created_at' => $this->created_at,
        ];
    }
}
