<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    
    public function up(): void
    {
        $files = glob(__DIR__ . '/../seeders/questions/*.php');

        $categoryIds = DB::table('categories')->pluck('id', 'slug');

        foreach ($files as $file) {
            $questions = require $file;
            if (!is_array($questions)) continue;

            foreach ($questions as $q) {
                if (!isset($q['question'], $q['a'], $q['b'], $q['c'], $q['d'], $q['category'])) continue;

                $rows = DB::table('questions')
                    ->where('question_text', $q['question'])
                    ->get();

                foreach ($rows as $row) {
                    $update = [];

                    $targetCatId = $categoryIds[$q['category']] ?? null;
                    if ($targetCatId && $targetCatId !== $row->category_id) {
                        $update['category_id'] = $targetCatId;
                    }

                    $seedTexts = [$q['a'], $q['b'], $q['c'], $q['d']];
                    foreach (['answer_a', 'answer_b', 'answer_c', 'answer_d'] as $col) {
                        $current = $row->{$col};
                        if (in_array($current, $seedTexts, true)) continue; 

                        foreach ($seedTexts as $clean) {
                            if ($clean !== '' && str_starts_with($current, $clean)) {
                                $update[$col] = $clean;
                                break;
                            }
                        }
                    }

                    if ($update) {
                        DB::table('questions')->where('id', $row->id)->update($update);
                    }
                }
            }
        }
    }

    public function down(): void
    {
        
    }
};
