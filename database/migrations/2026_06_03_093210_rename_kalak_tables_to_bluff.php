<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (config('database.default') !== 'sqlite') {
            DB::statement('SET FOREIGN_KEY_CHECKS=0');
        }

        // Rename tables
        Schema::rename('kalak_questions', 'bluff_questions');
        Schema::rename('kalak_games', 'bluff_games');
        Schema::rename('kalak_players', 'bluff_players');
        Schema::rename('kalak_rounds', 'bluff_rounds');
        Schema::rename('kalak_round_answers', 'bluff_round_answers');
        Schema::rename('kalak_votes', 'bluff_votes');

        // Rename FK columns in bluff_players
        Schema::table('bluff_players', function (Blueprint $table) {
            $table->renameColumn('kalak_game_id', 'bluff_game_id');
        });

        // Rename FK columns in bluff_rounds
        Schema::table('bluff_rounds', function (Blueprint $table) {
            $table->renameColumn('kalak_game_id', 'bluff_game_id');
            $table->renameColumn('kalak_question_id', 'bluff_question_id');
        });

        // Rename FK columns in bluff_round_answers
        Schema::table('bluff_round_answers', function (Blueprint $table) {
            $table->renameColumn('kalak_round_id', 'bluff_round_id');
            $table->renameColumn('kalak_player_id', 'bluff_player_id');
        });

        // Rename FK columns in bluff_votes
        Schema::table('bluff_votes', function (Blueprint $table) {
            $table->renameColumn('kalak_round_id', 'bluff_round_id');
            $table->renameColumn('voter_player_id', 'bluff_voter_id');
            $table->renameColumn('voted_answer_id', 'bluff_voted_answer_id');
        });

        // Rename FK column in bluff_games
        Schema::table('bluff_games', function (Blueprint $table) {
            $table->renameColumn('created_by', 'bluff_creator_id');
        });

        if (config('database.default') !== 'sqlite') {
            DB::statement('SET FOREIGN_KEY_CHECKS=1');
        }
    }

    public function down(): void
    {
        if (config('database.default') !== 'sqlite') {
            DB::statement('SET FOREIGN_KEY_CHECKS=0');
        }

        // Revert column renames
        Schema::table('bluff_players', function (Blueprint $table) {
            $table->renameColumn('bluff_game_id', 'kalak_game_id');
        });
        Schema::table('bluff_rounds', function (Blueprint $table) {
            $table->renameColumn('bluff_game_id', 'kalak_game_id');
            $table->renameColumn('bluff_question_id', 'kalak_question_id');
        });
        Schema::table('bluff_round_answers', function (Blueprint $table) {
            $table->renameColumn('bluff_round_id', 'kalak_round_id');
            $table->renameColumn('bluff_player_id', 'kalak_player_id');
        });
        Schema::table('bluff_votes', function (Blueprint $table) {
            $table->renameColumn('bluff_round_id', 'kalak_round_id');
            $table->renameColumn('bluff_voter_id', 'voter_player_id');
            $table->renameColumn('bluff_voted_answer_id', 'voted_answer_id');
        });
        Schema::table('bluff_games', function (Blueprint $table) {
            $table->renameColumn('bluff_creator_id', 'created_by');
        });

        // Revert table renames
        Schema::rename('bluff_questions', 'kalak_questions');
        Schema::rename('bluff_games', 'kalak_games');
        Schema::rename('bluff_players', 'kalak_players');
        Schema::rename('bluff_rounds', 'kalak_rounds');
        Schema::rename('bluff_round_answers', 'kalak_round_answers');
        Schema::rename('bluff_votes', 'kalak_votes');

        if (config('database.default') !== 'sqlite') {
            DB::statement('SET FOREIGN_KEY_CHECKS=1');
        }
    }
};
