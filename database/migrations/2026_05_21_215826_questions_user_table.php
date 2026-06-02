<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('questions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('category_id')->constrained()->cascadeOnDelete();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->text('question_text');
            $table->text('question_text_ar')->nullable();
            $table->string('answer_a');
            $table->string('answer_b');
            $table->string('answer_c');
            $table->string('answer_d');
            $table->enum('correct_answer', ['a', 'b', 'c', 'd']);
            $table->enum('difficulty', ['medium', 'hard', 'very_hard']);
            $table->integer('points')->default(250);
            $table->string('image')->nullable();
            $table->enum('status', ['active', 'inactive', 'pending'])->default('active');
            $table->integer('times_used')->default(0);
            $table->integer('times_correct')->default(0);
            $table->integer('times_wrong')->default(0);
            $table->timestamps();
            $table->softDeletes();

            $table->index(['category_id', 'difficulty', 'status']);
            $table->index(['points', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('questions');
    }
};