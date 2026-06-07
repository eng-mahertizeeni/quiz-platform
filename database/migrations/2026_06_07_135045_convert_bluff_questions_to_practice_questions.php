<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $admin = DB::table('users')->orderBy('id')->first();
        $adminId = $admin?->id ?? 1;

        $categoryIds = range(67, 81);

        foreach ($categoryIds as $catId) {
            $bluffQuestions = DB::table('bluff_questions')
                ->where('category_id', $catId)
                ->orderBy('id')
                ->get();

            $allAnswers = $bluffQuestions->pluck('correct_answer')->toArray();

            foreach ($bluffQuestions as $bq) {
                $wrongs = $this->generateWrongAnswers($bq->correct_answer, $allAnswers);

                $options = collect([$bq->correct_answer, ...$wrongs]);
                $options = $options->shuffle();

                $correctLetter = match ($options->search(fn($v) => $v === $bq->correct_answer)) {
                    0 => 'a', 1 => 'b', 2 => 'c', 3 => 'd', default => 'a',
                };

                $now = now();

                DB::table('questions')->insert([
                    'category_id' => $catId,
                    'created_by' => $adminId,
                    'question_text' => $bq->question_text,
                    'question_text_ar' => $bq->question_text,
                    'answer_a' => $options[0] ?? '',
                    'answer_b' => $options[1] ?? '',
                    'answer_c' => $options[2] ?? '',
                    'answer_d' => $options[3] ?? '',
                    'correct_answer' => $correctLetter,
                    'difficulty' => 'medium',
                    'points' => 10,
                    'status' => 'active',
                    'times_used' => 0,
                    'times_correct' => 0,
                    'times_wrong' => 0,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
            }
        }
    }

    private function generateWrongAnswers(string $correct, array $pool): array
    {
        // Remove correct answer from pool
        $candidates = array_values(array_filter($pool, fn($a) => $a !== $correct));
        $candidates = array_values(array_unique($candidates));

        $wrongs = [];

        // Strategy 1: For numeric answers, use nearby numbers
        $cleanNumeric = $this->extractNumeric($correct);
        if ($cleanNumeric !== null) {
            $variations = [];
            $num = $cleanNumeric;
            $possibleOffsets = [1, 2, 3, -1, -2, -3, 5, -5, 10, -10];
            foreach ($possibleOffsets as $offset) {
                $candidate = str_replace((string)$num, (string)($num + $offset), $correct);
                if ($candidate !== $correct && !in_array($candidate, $wrongs)) {
                    $variations[] = $candidate;
                }
                if (count($variations) >= 10) break;
            }
            shuffle($variations);
            foreach ($variations as $v) {
                if (count($wrongs) >= 3) break;
                $wrongs[] = $v;
            }
        }

        // Strategy 2: Use other answers from the same category pool
        if (count($wrongs) < 3 && count($candidates) > 0) {
            shuffle($candidates);
            foreach ($candidates as $c) {
                if (count($wrongs) >= 3) break;
                if (!in_array($c, $wrongs)) {
                    $wrongs[] = $c;
                }
            }
        }

        // Strategy 3: Modify the correct answer (change suffix/prefix)
        if (count($wrongs) < 3) {
            $modifications = [
                $correct . ' ' . 'القديم',
                $correct . ' ' . 'الجديد',
                'غير ' . $correct,
                'نفس ' . $correct,
                'مثل ' . $correct,
                'دون ' . $correct,
                $correct . ' الصغيرة',
                $correct . ' الكبيرة',
                $correct . ' الأولى',
                $correct . ' الثانية',
            ];
            shuffle($modifications);
            foreach ($modifications as $m) {
                if (count($wrongs) >= 3) break;
                if ($m !== $correct && !in_array($m, $wrongs)) {
                    $wrongs[] = $m;
                }
            }
        }

        // Strategy 4: Last resort - generic wrong answers in Arabic
        if (count($wrongs) < 3) {
            $fallbacks = [
                'لا شيء مما ذكر',
                'كل ما ذكر',
                'واحد',
                'اثنان',
                'ثلاثة',
                'صفر',
                'واحد وعشرون',
                'مائة',
                'ألف',
            ];
            shuffle($fallbacks);
            foreach ($fallbacks as $f) {
                if (count($wrongs) >= 3) break;
                if ($f !== $correct && !in_array($f, $wrongs)) {
                    $wrongs[] = $f;
                }
            }
        }

        // Pad to 3 if needed
        while (count($wrongs) < 3) {
            $wrongs[] = 'إجابة ' . (count($wrongs) + 1);
        }

        return array_slice($wrongs, 0, 3);
    }

    private function extractNumeric(string $str): ?float
    {
        // Remove Arabic text, keep just numbers and decimals
        $cleaned = preg_replace('/[^0-9.]/', '', $str);
        if ($cleaned !== '' && is_numeric($cleaned)) {
            return (float) $cleaned;
        }
        return null;
    }

    public function down(): void
    {
        DB::table('questions')
            ->whereBetween('category_id', [67, 81])
            ->delete();
    }
};
