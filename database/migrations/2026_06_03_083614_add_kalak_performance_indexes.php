<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('kalak_games', function (Blueprint $table) {
            $table->index(['status', 'created_at'], 'kg_status_created_idx');
        });

        Schema::table('kalak_players', function (Blueprint $table) {
            $table->index(['user_id', 'kalak_game_id'], 'kp_user_game_idx');
        });

        Schema::table('kalak_rounds', function (Blueprint $table) {
            $table->index(['kalak_game_id', 'round_number', 'status'], 'kr_game_round_status_idx');
        });

        Schema::table('kalak_round_answers', function (Blueprint $table) {
            $table->index(['kalak_round_id', 'kalak_player_id', 'is_real_fake'], 'kra_round_player_fake_idx');
        });

        Schema::table('kalak_votes', function (Blueprint $table) {
            $table->index(['kalak_round_id', 'voter_player_id', 'voted_answer_id'], 'kv_round_voter_answer_idx');
        });
    }

    public function down(): void
    {
        Schema::table('kalak_games', function (Blueprint $table) {
            $table->dropIndex('kg_status_created_idx');
        });

        Schema::table('kalak_players', function (Blueprint $table) {
            $table->dropIndex('kp_user_game_idx');
        });

        Schema::table('kalak_rounds', function (Blueprint $table) {
            $table->dropIndex('kr_game_round_status_idx');
        });

        Schema::table('kalak_round_answers', function (Blueprint $table) {
            $table->dropIndex('kra_round_player_fake_idx');
        });

        Schema::table('kalak_votes', function (Blueprint $table) {
            $table->dropIndex('kv_round_voter_answer_idx');
        });
    }
};
