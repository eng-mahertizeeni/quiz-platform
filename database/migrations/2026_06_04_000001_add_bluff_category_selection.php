<?php

use App\Models\BluffQuestion;
use App\Models\Category;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        
        $generalId = Category::where('slug', 'general')->value('id');
        if ($generalId) {
            BluffQuestion::whereNull('category_id')->update(['category_id' => $generalId]);
        }

        Schema::table('bluff_games', function (Blueprint $table) {
            $table->json('selected_categories')->nullable()->after('total_rounds');
        });

        Schema::table('bluff_rounds', function (Blueprint $table) {
            
            $table->unsignedBigInteger('bluff_question_id')->nullable()->change();

            $table->foreignId('selected_by_player_id')->nullable()->after('bluff_question_id')
                ->constrained('bluff_players')->cascadeOnDelete();
            $table->foreignId('category_id')->nullable()->after('selected_by_player_id')
                ->constrained('categories')->nullOnDelete();
        });

        if (config('database.default') !== 'sqlite') {
            DB::statement("ALTER TABLE bluff_rounds MODIFY COLUMN status ENUM('selecting','answering','voting','finished') NOT NULL DEFAULT 'selecting'");
        }
    }

    public function down(): void
    {
        if (config('database.default') !== 'sqlite') {
            DB::statement("ALTER TABLE bluff_rounds MODIFY COLUMN status ENUM('answering','voting','finished') NOT NULL DEFAULT 'answering'");
        }

        Schema::table('bluff_rounds', function (Blueprint $table) {
            $table->dropForeign(['selected_by_player_id']);
            $table->dropForeign(['category_id']);
            $table->dropColumn(['selected_by_player_id', 'category_id']);
            $table->unsignedBigInteger('bluff_question_id')->nullable(false)->change();
        });

        Schema::table('bluff_games', function (Blueprint $table) {
            $table->dropColumn('selected_categories');
        });

        $generalId = Category::where('slug', 'general')->value('id');
        if ($generalId) {
            
        }
    }
};
