<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Break the sessions/teams FK cycle (MySQL).
        DB::table('game_sessions')->update(['current_team_id' => null]);
        DB::table('game_sessions')->delete();
        DB::table('question_statistics')->delete();
        DB::table('submitted_questions')->delete();
        DB::table('questions')->delete();

        DB::table('bluff_games')->delete();
        DB::table('bluff_questions')->delete();

        Schema::dropIfExists('kuraiyat_rounds');
        Schema::dropIfExists('kuraiyat_players');
        Schema::dropIfExists('kuraiyat_games');
        Schema::dropIfExists('user_question_answers');

        $hints = array_filter(
            ['hint_1', 'hint_2', 'hint_3', 'hint_4', 'hint_5'],
            fn($column) => Schema::hasColumn('questions', $column)
        );
        if ($hints) {
            Schema::table('questions', function (Blueprint $table) use ($hints) {
                $table->dropColumn($hints);
            });
        }

        Schema::table('questions', function (Blueprint $table) {
            $table->enum('difficulty', ['easy', 'medium', 'hard'])->change();
            $table->integer('points')->default(200)->change();
            $table->string('source_file')->nullable()->after('status');
        });

        Schema::table('submitted_questions', function (Blueprint $table) {
            $table->enum('difficulty', ['easy', 'medium', 'hard'])->change();
        });
    }

    public function down(): void
    {
        DB::table('game_sessions')->update(['current_team_id' => null]);
        DB::table('game_sessions')->delete();
        DB::table('submitted_questions')->delete();
        DB::table('questions')->delete();

        Schema::table('submitted_questions', function (Blueprint $table) {
            $table->enum('difficulty', ['medium', 'hard', 'very_hard'])->change();
        });

        Schema::table('questions', function (Blueprint $table) {
            $table->dropColumn('source_file');
            $table->enum('difficulty', ['medium', 'hard', 'very_hard'])->change();
            $table->integer('points')->default(250)->change();
        });
    }
};
