<?php

namespace Database\Factories;

use App\Models\Category;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class CategoryFactory extends Factory
{
    protected $model = Category::class;

    public function definition(): array
    {
        $name = fake()->unique()->word();
        return [
            'name' => $name,
            'name_ar' => $name,
            'slug' => Str::slug($name),
            'description' => fake()->sentence(),
            'icon' => 'tag',
            'color' => fake()->hexColor(),
            'is_featured' => false,
            'is_active' => true,
            'sort_order' => 0,
            'times_played' => 0,
        ];
    }
}
