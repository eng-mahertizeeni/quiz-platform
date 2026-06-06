<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('bluff_rounds', function (Blueprint $table) {
            $table->timestamp('answering_started_at')->nullable()->after('status');
            $table->timestamp('voting_started_at')->nullable()->after('answering_started_at');
        });
    }

    public function down(): void
    {
        Schema::table('bluff_rounds', function (Blueprint $table) {
            $table->dropColumn(['answering_started_at', 'voting_started_at']);
        });
    }
};
