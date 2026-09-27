<?php

namespace Database\Seeders;

use App\Services\QuestionImporter;
use Illuminate\Database\Seeder;

class GameQuestionSeeder extends Seeder
{
    public function run(QuestionImporter $importer): void
    {
        $stats = $importer->import();

        $this->command?->info(sprintf(
            'Questions: %d created, %d updated, %d unchanged, %d deactivated',
            $stats['created'], $stats['updated'], $stats['unchanged'], $stats['deactivated']
        ));
    }
}
