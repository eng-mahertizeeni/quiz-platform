<?php

use Database\Seeders\CategorySeeder;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    
    public function up(): void
    {
        (new CategorySeeder())->run();
    }

    public function down(): void
    {
        
    }
};
