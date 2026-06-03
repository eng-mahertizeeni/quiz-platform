<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('kalak_questions', function (Blueprint $table) {
            $table->id();
            $table->text('question_text');
            $table->string('correct_answer');
            $table->foreignId('category_id')->nullable()->constrained()->nullOnDelete();
            $table->string('difficulty')->nullable();
            $table->timestamps();
        });

        Schema::create('kalak_games', function (Blueprint $table) {
            $table->id();
            $table->string('code', 6)->unique();
            $table->foreignId('created_by')->constrained('users')->cascadeOnDelete();
            $table->enum('status', ['waiting', 'playing', 'finished'])->default('waiting');
            $table->integer('current_round')->default(0);
            $table->integer('total_rounds')->default(8);
            $table->timestamp('started_at')->nullable();
            $table->timestamp('finished_at')->nullable();
            $table->timestamps();

            $table->index(['code', 'status']);
        });

        Schema::create('kalak_players', function (Blueprint $table) {
            $table->id();
            $table->foreignId('kalak_game_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->integer('total_score')->default(0);
            $table->timestamps();

            $table->unique(['kalak_game_id', 'user_id']);
        });

        Schema::create('kalak_rounds', function (Blueprint $table) {
            $table->id();
            $table->foreignId('kalak_game_id')->constrained()->cascadeOnDelete();
            $table->foreignId('kalak_question_id')->constrained('kalak_questions')->cascadeOnDelete();
            $table->integer('round_number');
            $table->enum('status', ['answering', 'voting', 'finished'])->default('answering');
            $table->timestamps();

            $table->index(['kalak_game_id', 'round_number']);
        });

        Schema::create('kalak_round_answers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('kalak_round_id')->constrained()->cascadeOnDelete();
            $table->foreignId('kalak_player_id')->nullable()->constrained('kalak_players')->cascadeOnDelete();
            $table->string('answer_text');
            $table->boolean('is_real_fake')->default(false);
            $table->timestamps();

            $table->unique(['kalak_round_id', 'kalak_player_id']);
        });

        Schema::create('kalak_votes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('kalak_round_id')->constrained()->cascadeOnDelete();
            $table->foreignId('voter_player_id')->constrained('kalak_players')->cascadeOnDelete();
            $table->foreignId('voted_answer_id')->constrained('kalak_round_answers')->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['kalak_round_id', 'voter_player_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('kalak_votes');
        Schema::dropIfExists('kalak_round_answers');
        Schema::dropIfExists('kalak_rounds');
        Schema::dropIfExists('kalak_players');
        Schema::dropIfExists('kalak_games');
        Schema::dropIfExists('kalak_questions');
    }
};
