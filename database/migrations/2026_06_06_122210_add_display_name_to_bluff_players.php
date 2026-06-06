<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('bluff_players', function (Blueprint $table) {
            $table->string('display_name', 50)->nullable()->after('user_id');
        });
    }

    public function down(): void
    {
        Schema::table('bluff_players', function (Blueprint $table) {
            $table->dropColumn('display_name');
        });
    }
};
