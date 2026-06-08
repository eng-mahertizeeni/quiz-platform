<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            ['name' => 'كرة القدم', 'slug' => 'football', 'icon' => 'futbol', 'color' => '#16A34A', 'is_featured' => true, 'sort_order' => 1],
            ['name' => 'السينما والأفلام', 'slug' => 'movies', 'icon' => 'film', 'color' => '#DC2626', 'is_featured' => true, 'sort_order' => 2],
            ['name' => 'التكنولوجيا', 'slug' => 'technology', 'icon' => 'microchip', 'color' => '#2563EB', 'is_featured' => true, 'sort_order' => 3],
            ['name' => 'الجغرافيا', 'slug' => 'geography', 'icon' => 'globe-asia', 'color' => '#0891B2', 'is_featured' => true, 'sort_order' => 4],
            ['name' => 'التاريخ', 'slug' => 'history', 'icon' => 'scroll', 'color' => '#92400E', 'is_featured' => true, 'sort_order' => 5],
            ['name' => 'العلوم', 'slug' => 'science', 'icon' => 'flask', 'color' => '#7C3AED', 'is_featured' => true, 'sort_order' => 6],
            ['name' => 'الأنيمي', 'slug' => 'anime', 'icon' => 'dragon', 'color' => '#DB2777', 'is_featured' => true, 'sort_order' => 7],
            ['name' => 'ألعاب الفيديو', 'slug' => 'gaming', 'icon' => 'gamepad', 'color' => '#059669', 'is_featured' => true, 'sort_order' => 8],
            ['name' => 'معلومات عامة', 'slug' => 'general', 'icon' => 'lightbulb', 'color' => '#D97706', 'is_featured' => true, 'sort_order' => 9],
            ['name' => 'السيارات', 'slug' => 'cars', 'icon' => 'car', 'color' => '#475569', 'is_featured' => false, 'sort_order' => 10],
            ['name' => 'الفضاء', 'slug' => 'space', 'icon' => 'rocket', 'color' => '#1E1B4B', 'is_featured' => true, 'sort_order' => 11],
            ['name' => 'الرياضة', 'slug' => 'sports', 'icon' => 'running', 'color' => '#B45309', 'is_featured' => false, 'sort_order' => 12],
            ['name' => 'مسيرة كرة القدم الاحترافية', 'slug' => 'football-career', 'icon' => 'shoe-prints', 'color' => '#0D9488', 'is_featured' => true, 'sort_order' => 13],
            ['name' => 'مسلسلات سورية', 'slug' => 'syrian-drama', 'icon' => 'tv', 'color' => '#BE123C', 'is_featured' => true, 'sort_order' => 14],
            ['name' => 'الطب والصحة', 'slug' => 'health', 'icon' => 'heartbeat', 'color' => '#DC2626', 'is_featured' => true, 'sort_order' => 15],
            ['name' => 'شخصيات تاريخية', 'slug' => 'historical-figures', 'icon' => 'crown', 'color' => '#7C3AED', 'is_featured' => true, 'sort_order' => 16],
            ['name' => 'أقوال مشهورة', 'slug' => 'quotes', 'icon' => 'quote-right', 'color' => '#F59E0B', 'is_featured' => true, 'sort_order' => 17],
            ['name' => 'عواصم وأعلام ودول', 'slug' => 'capitals', 'icon' => 'flag', 'color' => '#6366F1', 'is_featured' => true, 'sort_order' => 18],
            ['name' => 'أدب وشعر', 'slug' => 'literature-poetry', 'icon' => 'book', 'color' => '#8B5CF6', 'is_featured' => true, 'sort_order' => 19],
            ['name' => 'طبيعة وحيوان', 'slug' => 'nature-animals', 'icon' => 'paw', 'color' => '#10B981', 'is_featured' => true, 'sort_order' => 20],
            ['name' => 'إسلاميات', 'slug' => 'islamic-knowledge', 'icon' => 'mosque', 'color' => '#059669', 'is_featured' => true, 'sort_order' => 21],
            ['name' => 'مطبخ وأكلات', 'slug' => 'food-cuisine', 'icon' => 'utensils', 'color' => '#F59E0B', 'is_featured' => true, 'sort_order' => 22],
            ['name' => 'الميثولوجيا الإغريقية', 'slug' => 'greek-mythology', 'icon' => 'bolt', 'color' => '#C084FC', 'is_featured' => true, 'sort_order' => 0],
            ['name' => 'فلسفة وفكر', 'slug' => 'philosophy', 'icon' => 'brain', 'color' => '#8B5CF6', 'is_featured' => true, 'sort_order' => 0],
            ['name' => 'ذكاء اصطناعي', 'slug' => 'artificial-intelligence', 'icon' => 'robot', 'color' => '#06B6D4', 'is_featured' => true, 'sort_order' => 0],
            ['name' => 'علم نفس وسلوك', 'slug' => 'psychology', 'icon' => 'heart', 'color' => '#EC4899', 'is_featured' => true, 'sort_order' => 0],
            ['name' => 'ظواهر طبيعية', 'slug' => 'natural-phenomena', 'icon' => 'cloud-sun', 'color' => '#F97316', 'is_featured' => true, 'sort_order' => 0],
            ['name' => 'لغات العالم', 'slug' => 'world-languages', 'icon' => 'language', 'color' => '#14B8A6', 'is_featured' => true, 'sort_order' => 0],
            ['name' => 'هاري بوتر', 'slug' => 'harry-potter', 'icon' => 'hat-wizard', 'color' => '#7C3AED', 'is_featured' => true, 'sort_order' => 0],
            ['name' => 'عالم مارفل', 'slug' => 'marvel', 'icon' => 'mask', 'color' => '#DC2626', 'is_featured' => true, 'sort_order' => 0],
            ['name' => 'مسلسلات نتفليكس', 'slug' => 'netflix-series', 'icon' => 'play-circle', 'color' => '#E50914', 'is_featured' => true, 'sort_order' => 0],
            ['name' => 'ثقافة كورية', 'slug' => 'korean-culture', 'icon' => 'music', 'color' => '#FF6B6B', 'is_featured' => true, 'sort_order' => 0],
            ['name' => 'رياضة إلكترونية', 'slug' => 'esports', 'icon' => 'desktop', 'color' => '#6366F1', 'is_featured' => true, 'sort_order' => 0],
            ['name' => 'طاقة وبيئة', 'slug' => 'energy-environment', 'icon' => 'leaf', 'color' => '#22C55E', 'is_featured' => true, 'sort_order' => 0],
            ['name' => 'ألغاز وتفكير منطقي', 'slug' => 'puzzles-logic', 'icon' => 'puzzle-piece', 'color' => '#F59E0B', 'is_featured' => true, 'sort_order' => 0],
            ['name' => 'استكشاف ومغامرة', 'slug' => 'exploration', 'icon' => 'compass', 'color' => '#92400E', 'is_featured' => true, 'sort_order' => 0],
            ['name' => 'ثقافة رقمية', 'slug' => 'digital-culture', 'icon' => 'hashtag', 'color' => '#3B82F6', 'is_featured' => true, 'sort_order' => 0],
            ['name' => 'جوائز وألقاب عالمية', 'slug' => 'جوائز-وألقاب-عالمية', 'icon' => 'trophy', 'color' => '#F59E0B', 'is_featured' => true, 'sort_order' => 0],
            ['name' => 'معالم وآثار خالدة', 'slug' => 'معالم-وآثار-خالدة', 'icon' => 'landmark', 'color' => '#92400E', 'is_featured' => true, 'sort_order' => 0],
            ['name' => 'اختراعات غيرت مسار البشرية', 'slug' => 'اختراعات-غيرت-مسار-البشرية', 'icon' => 'lightbulb', 'color' => '#2563EB', 'is_featured' => true, 'sort_order' => 0],
            ['name' => 'عجائب الدنيا والطبيعة', 'slug' => 'عجائب-الدنيا-والطبيعة', 'icon' => 'globe', 'color' => '#10B981', 'is_featured' => true, 'sort_order' => 0],
            ['name' => 'موسيقى وفنون', 'slug' => 'موسيقى-وفنون', 'icon' => 'music', 'color' => '#EC4899', 'is_featured' => true, 'sort_order' => 0],
            ['name' => 'رياضات مائية وجبلية', 'slug' => 'رياضات-مائية-وجبلية', 'icon' => 'water', 'color' => '#06B6D4', 'is_featured' => true, 'sort_order' => 0],
            ['name' => 'الحضارة الإسلامية', 'slug' => 'الحضارة-الإسلامية', 'icon' => 'mosque', 'color' => '#059669', 'is_featured' => true, 'sort_order' => 0],
            ['name' => 'أعياد ومناسبات', 'slug' => 'أعياد-ومناسبات', 'icon' => 'calendar-alt', 'color' => '#DC2626', 'is_featured' => true, 'sort_order' => 0],
            ['name' => 'أساطير وحكايات شعبية', 'slug' => 'أساطير-وحكايات-شعبية', 'icon' => 'dragon', 'color' => '#7C3AED', 'is_featured' => true, 'sort_order' => 0],
        ];

        foreach ($categories as $cat) {
            Category::firstOrCreate(
                ['slug' => $cat['slug']],
                array_merge($cat, ['is_active' => true])
            );
        }
    }
}