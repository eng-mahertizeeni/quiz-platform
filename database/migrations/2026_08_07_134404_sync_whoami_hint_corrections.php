<?php

use Database\Seeders\WhoAmISeeder;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    
    public function up(): void
    {
        foreach (WhoAmISeeder::questions() as $q) {
            
            $matches = DB::table('questions')->where('hint_5', $q['hint_5'])->pluck('id');
            if ($matches->count() !== 1) continue;

            DB::table('questions')->where('id', $matches->first())->update([
                'hint_1' => $q['hint_1'],
                'hint_2' => $q['hint_2'],
                'hint_3' => $q['hint_3'],
                'hint_4' => $q['hint_4'],
            ]);
        }
    }

    public function down(): void
    {
        
    }
};
