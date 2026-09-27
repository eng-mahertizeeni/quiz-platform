<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Question;
use App\Services\QuestionImporter;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use PHPUnit\Framework\Attributes\Test;
use RuntimeException;
use Tests\TestCase;

class QuestionImporterTest extends TestCase
{
    use DatabaseMigrations;

    private string $dir;

    protected function setUp(): void
    {
        parent::setUp();

        $this->dir = sys_get_temp_dir() . '/questions-' . uniqid();
        mkdir($this->dir);
        Category::factory()->create(['slug' => 'test-category']);
    }

    protected function tearDown(): void
    {
        array_map('unlink', glob($this->dir . '/*'));
        rmdir($this->dir);

        parent::tearDown();
    }

    #[Test]
    public function it_imports_questions_with_difficulty_from_points()
    {
        $this->writeFile('batch1.txt', <<<TXT
            # comment
            @category test-category
            200|سؤال سهل|صح|خطأ1|خطأ2|خطأ3
            400|سؤال متوسط|صح|خطأ1|خطأ2|خطأ3
            600|سؤال صعب|صح|خطأ1|خطأ2|خطأ3
            TXT);

        $stats = $this->importer()->import();

        $this->assertEquals(3, $stats['created']);
        $this->assertEquals(
            ['easy' => 200, 'medium' => 400, 'hard' => 600],
            Question::orderBy('points')->pluck('points', 'difficulty')->all()
        );
        foreach (Question::all() as $question) {
            $this->assertEquals('صح', $question->correct_answer_text);
            $this->assertEquals('active', $question->status);
            $this->assertEquals('batch1.txt', $question->source_file);
        }
    }

    #[Test]
    public function reimporting_is_idempotent_and_deactivates_removed_questions()
    {
        $this->writeFile('batch1.txt', "@category test-category\n200|س1|صح|أ|ب|ج\n200|س2|صح|أ|ب|ج\n");
        $this->importer()->import();
        $order = Question::where('question_text', 'س1')->first()->answers;

        $this->writeFile('batch1.txt', "@category test-category\n200|س1|صح|أ|ب|ج\n");
        $stats = $this->importer()->import();

        $this->assertEquals(['created' => 0, 'updated' => 0, 'unchanged' => 1, 'deactivated' => 1], $stats);
        $this->assertEquals($order, Question::where('question_text', 'س1')->first()->answers);
        $this->assertEquals('inactive', Question::where('question_text', 'س2')->first()->status);
    }

    #[Test]
    public function it_leaves_questions_not_from_files_alone()
    {
        $manual = Question::factory()->create(['status' => 'active']);
        $this->writeFile('batch1.txt', "@category test-category\n200|س1|صح|أ|ب|ج\n");

        $this->importer()->import();

        $this->assertEquals('active', $manual->fresh()->status);
    }

    #[Test]
    public function it_rejects_invalid_files_without_writing_anything()
    {
        $this->writeFile('batch1.txt', <<<TXT
            200|بدون فئة|صح|أ|ب|ج
            @category test-category
            200|سؤال صحيح|صح|أ|ب|ج
            250|نقاط خاطئة|صح|أ|ب|ج
            400|حقول ناقصة|صح|أ
            600|إجابات مكررة|صح|صح|ب|ج
            200|سؤال صحيح|صح|أ|ب|ج
            @category unknown-slug
            TXT);

        $errors = $this->importer()->parse()['errors'];

        $this->assertCount(6, $errors);
        $this->assertStringStartsWith('batch1.txt:1:', $errors[0]);

        $this->expectException(RuntimeException::class);
        try {
            $this->importer()->import();
        } finally {
            $this->assertEquals(0, Question::count());
        }
    }

    private function importer(): QuestionImporter
    {
        return new QuestionImporter($this->dir);
    }

    private function writeFile(string $name, string $contents): void
    {
        file_put_contents($this->dir . '/' . $name, $contents);
    }
}
