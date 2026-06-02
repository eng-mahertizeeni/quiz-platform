<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('game_sessions', function (Blueprint $table) {
            $table->id();
            $table->string('code', 8)->unique();
            $table->foreignId('created_by')->constrained('users')->cascadeOnDelete();
            $table->enum('status', ['waiting', 'active', 'paused', 'finished'])->default('waiting');
            $table->unsignedBigInteger('current_team_id')->nullable(); 
            $table->integer('current_round')->default(0);
            $table->integer('total_questions')->default(36);
            $table->integer('answered_questions')->default(0);
            $table->integer('timer_seconds')->default(30);
            $table->boolean('sound_enabled')->default(true);
            $table->timestamp('started_at')->nullable();
            $table->timestamp('finished_at')->nullable();
            $table->timestamps();

            $table->index(['code', 'status']);
        });

        Schema::create('game_teams', function (Blueprint $table) {
            $table->id();
            $table->foreignId('game_session_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('color', 7)->default('#3B82F6');
            $table->integer('score')->default(0);
            $table->integer('correct_answers')->default(0);
            $table->integer('wrong_answers')->default(0);
            $table->boolean('is_winner')->default(false);
            $table->boolean('power_remove_used')->default(false);
            $table->boolean('power_steal_used')->default(false);
            $table->timestamps();

            $table->index('game_session_id');
        });

        Schema::table('game_sessions', function (Blueprint $table) {
            $table->foreign('current_team_id')->references('id')->on('game_teams')->nullOnDelete();
        });

        Schema::create('game_session_categories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('game_session_id')->constrained()->cascadeOnDelete();
            $table->foreignId('category_id')->constrained()->cascadeOnDelete();
            $table->integer('sort_order')->default(0);
            $table->timestamps();

            $table->unique(['game_session_id', 'category_id']);
        });

        Schema::create('game_rounds', function (Blueprint $table) {
            $table->id();
            $table->foreignId('game_session_id')->constrained()->cascadeOnDelete();
            $table->foreignId('question_id')->constrained()->cascadeOnDelete();
            $table->foreignId('category_id')->constrained()->cascadeOnDelete();
            $table->foreignId('assigned_team_id')->constrained('game_teams')->cascadeOnDelete();
            $table->foreignId('answered_by_team_id')->nullable()->constrained('game_teams')->nullOnDelete();
            $table->enum('status', ['pending', 'active', 'answered', 'skipped', 'stolen'])->default('pending');
            $table->boolean('is_stolen')->default(false);
            $table->integer('points_value');
            $table->integer('round_number');
            $table->timestamps();

            $table->index(['game_session_id', 'status']);
        });

        Schema::create('game_answers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('game_session_id')->constrained()->cascadeOnDelete();
            $table->foreignId('game_round_id')->constrained()->cascadeOnDelete();
            $table->foreignId('team_id')->constrained('game_teams')->cascadeOnDelete();
            $table->foreignId('question_id')->constrained()->cascadeOnDelete();
            $table->enum('selected_answer', ['a', 'b', 'c', 'd'])->nullable();
            $table->boolean('is_correct')->default(false);
            $table->integer('points_earned')->default(0);
            $table->integer('time_taken')->nullable();
            $table->boolean('power_remove_used')->default(false);
            $table->boolean('power_steal_used')->default(false);
            $table->timestamps();
        });

        Schema::create('power_usage', function (Blueprint $table) {
            $table->id();
            $table->foreignId('game_session_id')->constrained()->cascadeOnDelete();
            $table->foreignId('team_id')->constrained('game_teams')->cascadeOnDelete();
            $table->foreignId('game_round_id')->constrained()->cascadeOnDelete();
            $table->enum('power_type', ['remove_two', 'steal_question']);
            $table->timestamps();

            $table->unique(['game_session_id', 'team_id', 'power_type']);
        });
    }

    public function down(): void
    {
        Schema::table('game_sessions', function (Blueprint $table) {
            $table->dropForeign(['current_team_id']);
        });

        Schema::dropIfExists('power_usage');
        Schema::dropIfExists('game_answers');
        Schema::dropIfExists('game_rounds');
        Schema::dropIfExists('game_session_categories');
        Schema::dropIfExists('game_teams');
        Schema::dropIfExists('game_sessions');
    }
};