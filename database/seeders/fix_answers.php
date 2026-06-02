<?php
/**
 * Fix imbalanced answers in the database directly.
 * Run: php artisan db:seed --class=QuestionSeeder  (first if needed)
 * Then: php database/seeders/fix_answers.php
 */

require __DIR__ . '/../../vendor/autoload.php';
$app = require __DIR__ . '/../../bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\Question;

$fillers = [
    ['من أكثر المفاهيم شهرة في هذا المجال', 'وهو موضوع يثير اهتمام الباحثين', 'وتتعدد الآراء حول هذه المسألة'],
    ['وقد ساهم في تطور هذا المجال بشكل ملحوظ', 'وله تطبيقات عديدة في الحياة العملية', 'وهو ما زال قيد الدراسة والتحليل'],
    ['ويعتبر من المواضيع الأساسية في هذا السياق', 'وقد حظي باهتمام كبير من المتخصصين', 'وهناك عدة جوانب يجب أخذها بعين الاعتبار'],
    ['ويرتبط بعدة مفاهيم أخرى مهمة', 'وله دور محوري في فهم الظواهر المرتبطة', 'ويتطلب فهماً عميقاً للسياق العام'],
    ['وقد أشارت إليه العديد من المصادر الموثوقة', 'وهو محل نقاش بين المدارس الفكرية المختلفة', 'وتختلف التفسيرات حسب المنهج المتبع'],
];

$imbalanced = 0;
$hardImbalanced = 0;
$totalImbalanced = 0;

// Fix very_hard
$questions = Question::where('difficulty', 'very_hard')->where('status', 'active')->get();
foreach ($questions as $q) {
    $answers = ['a' => $q->answer_a, 'b' => $q->answer_b, 'c' => $q->answer_c, 'd' => $q->answer_d];
    $lengths = array_map('mb_strlen', $answers);
    $maxLen = max($lengths);
    $minLen = min($lengths);
    
    if ($maxLen > $minLen * 1.6 && $maxLen > 35) {
        $targetLen = (int)($maxLen * 0.75);
        foreach (['a', 'b', 'c', 'd'] as $letter) {
            $len = mb_strlen($answers[$letter]);
            if ($len < $targetLen) {
                $pick = $fillers[array_rand($fillers)];
                $extra = $pick[array_rand($pick)];
                $answers[$letter] .= '، ' . $extra;
            }
        }
        $q->update(['answer_a' => $answers['a'], 'answer_b' => $answers['b'], 'answer_c' => $answers['c'], 'answer_d' => $answers['d']]);
        $imbalanced++;
    }
}

// Fix hard
$questions = Question::where('difficulty', 'hard')->where('status', 'active')->get();
foreach ($questions as $q) {
    $answers = ['a' => $q->answer_a, 'b' => $q->answer_b, 'c' => $q->answer_c, 'd' => $q->answer_d];
    $lengths = array_map('mb_strlen', $answers);
    $maxLen = max($lengths);
    $minLen = min($lengths);
    
    if ($maxLen > $minLen * 1.6 && $maxLen > 35) {
        $targetLen = (int)($maxLen * 0.7);
        foreach (['a', 'b', 'c', 'd'] as $letter) {
            $len = mb_strlen($answers[$letter]);
            if ($len < $targetLen) {
                $pick = $fillers[array_rand($fillers)];
                $extra = $pick[array_rand($pick)];
                $answers[$letter] .= '، ' . $extra;
            }
        }
        $q->update(['answer_a' => $answers['a'], 'answer_b' => $answers['b'], 'answer_c' => $answers['c'], 'answer_d' => $answers['d']]);
        $hardImbalanced++;
    }
}

echo "Fixed very_hard: {$imbalanced}, hard: {$hardImbalanced}\n";

// Now export back to seed files
$questions = Question::where('status', 'active')->with('category')->get()->groupBy(fn($q) => $q->category->slug);

foreach ($questions as $slug => $items) {
    $content = "<?php\nreturn [\n";
    foreach ($items as $q) {
        $content .= "    [\n";
        $content .= "        'category' => " . var_export($q->category->slug, true) . ",\n";
        $content .= "        'difficulty' => " . var_export($q->difficulty, true) . ",\n";
        $content .= "        'question' => " . var_export($q->question_text, true) . ",\n";
        $content .= "        'a' => " . var_export($q->answer_a, true) . ",\n";
        $content .= "        'b' => " . var_export($q->answer_b, true) . ",\n";
        $content .= "        'c' => " . var_export($q->answer_c, true) . ",\n";
        $content .= "        'd' => " . var_export($q->answer_d, true) . ",\n";
        $content .= "        'correct' => " . var_export($q->correct_answer, true) . ",\n";
        $content .= "    ],\n";
    }
    $content .= "];\n";
    
    file_put_contents(__DIR__ . "/questions/{$slug}.php", $content);
    echo "Exported {$slug}: {$items->count()} questions\n";
}

echo "Done!\n";
