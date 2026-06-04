<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('questions', function (Blueprint $table) {
            $table->text('hint_1')->nullable()->after('points');
            $table->text('hint_2')->nullable()->after('hint_1');
            $table->text('hint_3')->nullable()->after('hint_2');
            $table->text('hint_4')->nullable()->after('hint_3');
            $table->text('hint_5')->nullable()->after('hint_4');
        });
    }

    public function down(): void
    {
        Schema::table('questions', function (Blueprint $table) {
            $table->dropColumn('hint_1');
            $table->dropColumn('hint_2');
            $table->dropColumn('hint_3');
            $table->dropColumn('hint_4');
            $table->dropColumn('hint_5');
        });
    }
};
