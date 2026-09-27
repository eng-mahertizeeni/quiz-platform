<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        $tree = [
            ['name' => 'كرة القدم', 'slug' => 'football', 'icon' => 'futbol', 'color' => '#16A34A', 'children' => [
                ['name' => 'ريال مدريد', 'slug' => 'real-madrid', 'is_featured' => true, 'icon' => 'crown', 'color' => '#A78BFA'],
                ['name' => 'برشلونة', 'slug' => 'barcelona', 'is_featured' => true, 'icon' => 'shield-alt', 'color' => '#A50044'],
                ['name' => 'الدوري الإنجليزي', 'slug' => 'premier-league', 'icon' => 'flag', 'color' => '#7C3AED'],
                ['name' => 'الدوريات الأوروبية الأخرى', 'slug' => 'european-leagues', 'icon' => 'globe-europe', 'color' => '#2563EB'],
                ['name' => 'دوري أبطال أوروبا', 'slug' => 'champions-league', 'is_featured' => true, 'icon' => 'star', 'color' => '#1E3A8A'],
                ['name' => 'كأس العالم', 'slug' => 'world-cup', 'is_featured' => true, 'icon' => 'trophy', 'color' => '#CA8A04'],
                ['name' => 'البطولات القارية', 'slug' => 'continental-cups', 'icon' => 'globe', 'color' => '#0891B2'],
                ['name' => 'الكرة العربية', 'slug' => 'arab-football', 'icon' => 'moon', 'color' => '#059669'],
                ['name' => 'أساطير كرة القدم', 'slug' => 'football-legends', 'icon' => 'user-astronaut', 'color' => '#B45309'],
                ['name' => 'ميسي ورونالدو', 'slug' => 'messi-ronaldo', 'icon' => 'user-friends', 'color' => '#DC2626'],
                ['name' => 'الكرة الذهبية والجوائز', 'slug' => 'ballon-dor', 'icon' => 'award', 'color' => '#EAB308'],
                ['name' => 'المدربون', 'slug' => 'coaches', 'icon' => 'clipboard', 'color' => '#475569'],
                ['name' => 'الملاعب والأندية', 'slug' => 'stadiums-clubs', 'icon' => 'building', 'color' => '#0D9488'],
                ['name' => 'انتقالات ومسيرة اللاعبين', 'slug' => 'player-careers', 'icon' => 'exchange-alt', 'color' => '#9333EA'],
                ['name' => 'كرة التسعينيات', 'slug' => 'nineties-football', 'icon' => 'history', 'color' => '#BE123C'],
                ['name' => 'نهائيات تاريخية', 'slug' => 'historic-finals', 'icon' => 'medal', 'color' => '#D97706'],
                ['name' => 'الديربيات والكلاسيكوهات', 'slug' => 'derbies', 'icon' => 'fire', 'color' => '#EA580C'],
                ['name' => 'أرقام القمصان', 'slug' => 'shirt-numbers', 'icon' => 'tshirt', 'color' => '#4F46E5'],
                ['name' => 'مواهب ضاعت', 'slug' => 'lost-talents', 'icon' => 'user-slash', 'color' => '#6B7280'],
                ['name' => 'انتقالات منسية', 'slug' => 'forgotten-transfers', 'icon' => 'question-circle', 'color' => '#0EA5E9'],
            ]],
        ];

        foreach ($tree as $parentOrder => $parentData) {
            $children = $parentData['children'];
            unset($parentData['children']);

            $parent = $this->upsert($parentData + ['parent_id' => null, 'sort_order' => $parentOrder]);

            foreach ($children as $childOrder => $childData) {
                $this->upsert($childData + ['parent_id' => $parent->id, 'sort_order' => $childOrder]);
            }
        }
    }

    private function upsert(array $data): Category
    {
        $category = Category::withTrashed()->updateOrCreate(
            ['slug' => $data['slug']],
            $data + ['is_active' => true, 'is_featured' => false]
        );

        if ($category->trashed()) {
            $category->restore();
        }

        return $category;
    }
}
