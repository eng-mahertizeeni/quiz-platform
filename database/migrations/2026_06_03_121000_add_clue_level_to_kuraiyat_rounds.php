<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('kuraiyat_rounds', function (Blueprint $table) {
            $table->integer('clue_level')->default(0)->after('status');
        });
    }

    public function down(): void
    {
        Schema::table('kuraiyat_rounds', function (Blueprint $table) {
            $table->dropColumn('clue_level');
        });
    }
};
