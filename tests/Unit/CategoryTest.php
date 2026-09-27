<?php

namespace Tests\Unit;

use App\Models\Category;
use App\Models\Question;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Tests\TestCase;
use PHPUnit\Framework\Attributes\Test;

class CategoryTest extends TestCase
{
    use DatabaseMigrations;
    
    #[Test]
    public function it_auto_generates_slug_on_create()
    {
        $category = Category::create(['name' => 'Test Category']);
        $this->assertEquals('test-category', $category->slug);
    }

    #[Test]
    public function it_checks_enough_questions()
    {
        $category = Category::factory()->create();

        Question::factory()->count(2)->create([
            'category_id' => $category->id,
            'difficulty' => 'easy',
            'points' => 200,
            'status' => 'active',
        ]);
        Question::factory()->count(2)->create([
            'category_id' => $category->id,
            'difficulty' => 'medium',
            'points' => 400,
            'status' => 'active',
        ]);
        Question::factory()->count(2)->create([
            'category_id' => $category->id,
            'difficulty' => 'hard',
            'points' => 600,
            'status' => 'active',
        ]);

        $this->assertTrue($category->hasEnoughQuestions());
    }

    #[Test]
    public function it_detects_insufficient_questions()
    {
        $category = Category::factory()->create();
        $this->assertFalse($category->hasEnoughQuestions());
    }
}
