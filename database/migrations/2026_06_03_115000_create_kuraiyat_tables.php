<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('kuraiyat_games', function (Blueprint $table) {
            $table->id();
            $table->string('code', 6)->unique();
            $table->string('type'); // head_to_head, who_am_i, quick_challenge
            $table->string('status')->default('waiting'); // waiting, playing, finished
            $table->foreignId('created_by')->constrained('users');
            $table->integer('current_round')->default(0);
            $table->integer('total_rounds')->default(5);
            $table->integer('total_questions')->default(0);
            $table->timestamp('started_at')->nullable();
            $table->timestamp('finished_at')->nullable();
            $table->timestamps();
        });

        Schema::create('kuraiyat_players', function (Blueprint $table) {
            $table->id();
            $table->foreignId('kuraiyat_game_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users');
            $table->integer('score')->default(0);
            $table->integer('strikes')->default(0);
            $table->timestamps();
            $table->unique(['kuraiyat_game_id', 'user_id']);
        });

        Schema::create('kuraiyat_rounds', function (Blueprint $table) {
            $table->id();
            $table->foreignId('kuraiyat_game_id')->constrained()->cascadeOnDelete();
            $table->integer('round_number');
            $table->foreignId('question_id')->constrained('questions');
            $table->string('status')->default('pending'); // pending, answered, finished, skipped
            $table->foreignId('answered_by')->nullable()->constrained('kuraiyat_players');
            $table->string('answer')->nullable();
            $table->boolean('is_correct')->nullable();
            $table->timestamps();
            $table->unique(['kuraiyat_game_id', 'round_number']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('kuraiyat_rounds');
        Schema::dropIfExists('kuraiyat_players');
        Schema::dropIfExists('kuraiyat_games');
    }
};
