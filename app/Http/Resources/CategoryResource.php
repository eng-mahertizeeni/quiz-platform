<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CategoryResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'parent_id' => $this->parent_id,
            'name' => $this->name,
            'name_ar' => $this->name_ar,
            'slug' => $this->slug,
            'description' => $this->description,
            'icon' => $this->icon,
            'thumbnail' => $this->thumbnail_url,
            'color' => $this->color,
            'is_featured' => (bool) $this->is_featured,
            'is_active' => (bool) $this->is_active,
            'sort_order' => (int) $this->sort_order,
            'times_played' => (int) $this->times_played,
            'questions_count' => (int) ($this->questions_count ?? $this->questions()->count()),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
