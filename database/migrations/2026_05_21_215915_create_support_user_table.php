<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('submitted_questions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('category_id')->constrained()->cascadeOnDelete();
            $table->text('question_text');
            $table->string('answer_a');
            $table->string('answer_b');
            $table->string('answer_c');
            $table->string('answer_d');
            $table->enum('correct_answer', ['a', 'b', 'c', 'd']);
            $table->enum('difficulty', ['medium', 'hard', 'very_hard']);
            $table->string('image')->nullable();
            $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending');
            $table->text('rejection_reason')->nullable();
            $table->foreignId('reviewed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('reviewed_at')->nullable();
            $table->foreignId('converted_question_id')->nullable()->constrained('questions')->nullOnDelete();
            $table->timestamps();

            $table->index(['user_id', 'status']);
            $table->index(['status', 'created_at']);
        });

        Schema::create('question_statistics', function (Blueprint $table) {
            $table->id();
            $table->foreignId('question_id')->constrained()->cascadeOnDelete();
            $table->date('date');
            $table->integer('times_shown')->default(0);
            $table->integer('times_correct')->default(0);
            $table->integer('times_wrong')->default(0);
            $table->decimal('avg_time_taken', 5, 2)->nullable();
            $table->timestamps();

            $table->unique(['question_id', 'date']);
        });

        Schema::create('scores', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('game_session_id')->constrained()->cascadeOnDelete();
            $table->foreignId('team_id')->constrained('game_teams')->cascadeOnDelete();
            $table->integer('score')->default(0);
            $table->boolean('is_winner')->default(false);
            $table->timestamps();

            $table->index(['user_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('scores');
        Schema::dropIfExists('question_statistics');
        Schema::dropIfExists('submitted_questions');
    }
};