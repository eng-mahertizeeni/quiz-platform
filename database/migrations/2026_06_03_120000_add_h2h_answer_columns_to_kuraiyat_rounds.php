<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('kuraiyat_rounds', function (Blueprint $table) {
            $table->foreignId('answered_by_p1')->nullable()->constrained('kuraiyat_players');
            $table->string('answer_p1')->nullable();
            $table->boolean('is_correct_p1')->nullable();
            $table->foreignId('answered_by_p2')->nullable()->constrained('kuraiyat_players');
            $table->string('answer_p2')->nullable();
            $table->boolean('is_correct_p2')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('kuraiyat_rounds', function (Blueprint $table) {
            $table->dropConstrainedForeignId('answered_by_p1');
            $table->dropColumn('answer_p1');
            $table->dropColumn('is_correct_p1');
            $table->dropConstrainedForeignId('answered_by_p2');
            $table->dropColumn('answer_p2');
            $table->dropColumn('is_correct_p2');
        });
    }
};
