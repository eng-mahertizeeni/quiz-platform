<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class QuestionResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'category_id' => $this->category_id,
            'category' => new CategoryResource($this->whenLoaded('category')),
            'question_text' => $this->question_text,
            'answer_a' => $this->answer_a,
            'answer_b' => $this->answer_b,
            'answer_c' => $this->answer_c,
            'answer_d' => $this->answer_d,
            'correct_answer' => $this->when($request->routeIs('api.*'), $this->correct_answer),
            'difficulty' => $this->difficulty,
            'difficulty_label' => $this->difficulty_label,
            'difficulty_color' => $this->difficulty_color,
            'points' => (int) $this->points,
            'image' => $this->image_url,
            'status' => $this->status,
            'times_used' => (int) $this->times_used,
            'times_correct' => (int) $this->times_correct,
            'times_wrong' => (int) $this->times_wrong,
            'success_rate' => (float) $this->success_rate,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
