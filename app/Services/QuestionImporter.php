<?php

namespace App\Services;

use App\Models\Category;
use App\Models\Question;
use Illuminate\Support\Facades\DB;
use RuntimeException;

/**
 * Imports team-game questions from database/questions/*.txt.
 *
 * File format (one question per line, fields separated by "|"):
 *
 *     # comment
 *     @category football
 *     200|السؤال|الإجابة الصحيحة|خيار خاطئ|خيار خاطئ|خيار خاطئ
 *
 * - The first field is the points: 200 (easy), 400 (medium) or 600 (hard).
 * - The first answer is always the correct one; answer positions are shuffled on import.
 * - "@category <slug>" applies to every line after it until the next "@category".
 *
 * The files are the source of truth for imported questions: a question removed
 * from its file is deactivated on the next import.
 */
class QuestionImporter
{
    private const ANSWER_KEYS = ['a', 'b', 'c', 'd'];

    public function __construct(private ?string $directory = null)
    {
        $this->directory ??= database_path('questions');
    }

    /**
     * Parse every file without writing anything.
     *
     * @return array{questions: array<int, array>, errors: array<int, string>}
     */
    public function parse(): array
    {
        $difficulties = array_flip(Question::DIFFICULTY_POINTS);
        $categories = Category::pluck('id', 'slug');
        $questions = [];
        $errors = [];
        $seen = [];

        foreach ($this->files() as $file) {
            $name = basename($file);
            $categorySlug = null;
            $lines = preg_split('/\R/u', preg_replace('/^\xEF\xBB\xBF/', '', file_get_contents($file)));

            foreach ($lines as $index => $rawLine) {
                $line = trim($rawLine);
                $where = "{$name}:" . ($index + 1);

                if ($line === '' || str_starts_with($line, '#')) {
                    continue;
                }

                if (str_starts_with($line, '@category')) {
                    $categorySlug = trim(mb_substr($line, mb_strlen('@category')));
                    if (!$categories->has($categorySlug)) {
                        $errors[] = "{$where}: الفئة \"{$categorySlug}\" غير موجودة";
                    }
                    continue;
                }

                if (str_starts_with($line, '@')) {
                    $errors[] = "{$where}: تعليمة غير معروفة \"{$line}\"";
                    continue;
                }

                $fields = array_map('trim', explode('|', $line));

                if ($categorySlug === null) {
                    $errors[] = "{$where}: يجب كتابة @category قبل الأسئلة";
                    continue;
                }
                if (count($fields) !== 6) {
                    $errors[] = "{$where}: يجب أن يحتوي السطر على 6 حقول (النقاط|السؤال|الصحيحة|خطأ|خطأ|خطأ)، وُجد " . count($fields);
                    continue;
                }

                [$points, $text] = $fields;
                $answers = array_slice($fields, 2);

                if (!isset($difficulties[(int) $points]) || (string) (int) $points !== $points) {
                    $errors[] = "{$where}: النقاط يجب أن تكون 200 أو 400 أو 600، وُجد \"{$points}\"";
                    continue;
                }
                if ($text === '' || in_array('', $answers, true)) {
                    $errors[] = "{$where}: السؤال أو إحدى الإجابات فارغة";
                    continue;
                }
                if (count(array_unique($answers)) !== 4) {
                    $errors[] = "{$where}: الإجابات الأربع يجب أن تكون مختلفة";
                    continue;
                }

                $key = $categorySlug . '|' . $text;
                if (isset($seen[$key])) {
                    $errors[] = "{$where}: السؤال مكرر (موجود أيضاً في {$seen[$key]})";
                    continue;
                }
                $seen[$key] = $where;

                if (!$categories->has($categorySlug)) {
                    continue;
                }

                $questions[] = [
                    'category_id' => $categories[$categorySlug],
                    'question_text' => $text,
                    'difficulty' => $difficulties[(int) $points],
                    'points' => (int) $points,
                    'correct' => $answers[0],
                    'answers' => $answers,
                    'source_file' => $name,
                ];
            }
        }

        return ['questions' => $questions, 'errors' => $errors];
    }

    /**
     * Validate every file and, only if all of them are valid, sync them into the database.
     *
     * @return array{created: int, updated: int, unchanged: int, deactivated: int}
     */
    public function import(): array
    {
        ['questions' => $questions, 'errors' => $errors] = $this->parse();

        if ($errors) {
            throw new RuntimeException("أخطاء في ملفات الأسئلة:\n" . implode("\n", $errors));
        }

        return DB::transaction(function () use ($questions) {
            $stats = ['created' => 0, 'updated' => 0, 'unchanged' => 0, 'deactivated' => 0];
            $keptIds = [];

            $existing = Question::withTrashed()
                ->whereNotNull('source_file')
                ->get()
                ->keyBy(fn($q) => $q->category_id . '|' . $q->question_text);

            foreach ($questions as $data) {
                $question = $existing->get($data['category_id'] . '|' . $data['question_text']);
                $attributes = [
                    'difficulty' => $data['difficulty'],
                    'points' => $data['points'],
                    'status' => 'active',
                    'source_file' => $data['source_file'],
                ];

                if (!$question) {
                    $question = Question::create([
                        'category_id' => $data['category_id'],
                        'question_text' => $data['question_text'],
                        ...$attributes,
                        ...$this->shuffledAnswers($data['answers'], $data['correct']),
                    ]);
                    $keptIds[] = $question->id;
                    $stats['created']++;
                    continue;
                }

                // Keep the current answer order unless the answers themselves changed.
                $currentAnswers = $question->answers;
                $sameAnswers = collect($currentAnswers)->sort()->values()->all() === collect($data['answers'])->sort()->values()->all()
                    && $question->correct_answer_text === $data['correct'];

                if (!$sameAnswers) {
                    $attributes += $this->shuffledAnswers($data['answers'], $data['correct']);
                }

                $question->fill($attributes);
                if ($question->trashed()) {
                    $question->restore();
                }

                if ($question->isDirty()) {
                    $question->save();
                    $stats['updated']++;
                } else {
                    $stats['unchanged']++;
                }
                $keptIds[] = $question->id;
            }

            $stats['deactivated'] = Question::whereNotNull('source_file')
                ->whereNotIn('id', $keptIds)
                ->where('status', '!=', 'inactive')
                ->update(['status' => 'inactive']);

            return $stats;
        });
    }

    /**
     * @return array<int, string>
     */
    private function files(): array
    {
        $files = glob($this->directory . '/*.txt') ?: [];
        sort($files, SORT_NATURAL);

        return $files;
    }

    private function shuffledAnswers(array $answers, string $correct): array
    {
        shuffle($answers);
        $row = [];

        foreach (self::ANSWER_KEYS as $i => $key) {
            $row['answer_' . $key] = $answers[$i];
            if ($answers[$i] === $correct) {
                $row['correct_answer'] = $key;
            }
        }

        return $row;
    }
}
