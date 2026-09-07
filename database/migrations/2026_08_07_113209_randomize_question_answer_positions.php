<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    
    public function up(): void
    {
        DB::table('questions')->orderBy('id')->chunk(200, function ($questions) {
            foreach ($questions as $question) {
                $slots = [
                    'a' => $question->answer_a,
                    'b' => $question->answer_b,
                    'c' => $question->answer_c,
                    'd' => $question->answer_d,
                ];

                $correctText = $slots[$question->correct_answer] ?? null;
                if ($correctText === null) {
                    continue;
                }

                $values = array_values($slots);
                shuffle($values);
                $letters = ['a', 'b', 'c', 'd'];
                $newSlots = array_combine($letters, $values);

                $newCorrectLetter = array_search($correctText, $newSlots, true);
                if ($newCorrectLetter === false) {
                    continue;
                }

                DB::table('questions')->where('id', $question->id)->update([
                    'answer_a' => $newSlots['a'],
                    'answer_b' => $newSlots['b'],
                    'answer_c' => $newSlots['c'],
                    'answer_d' => $newSlots['d'],
                    'correct_answer' => $newCorrectLetter,
                ]);
            }
        });
    }

    public function down(): void
    {
        
    }
};
