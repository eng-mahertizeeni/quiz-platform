<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    
    public function up(): void
    {
        Schema::table('bluff_games', function (Blueprint $table) {
            $table->unsignedTinyInteger('question_duration')->default(30)->after('total_rounds');
        });
    }

    public function down(): void
    {
        Schema::table('bluff_games', function (Blueprint $table) {
            $table->dropColumn('question_duration');
        });
    }
};
