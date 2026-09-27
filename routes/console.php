<?php

use App\Services\QuestionImporter;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('questions:import {--check : Only validate the files, without writing to the database}', function (QuestionImporter $importer) {
    if ($this->option('check')) {
        ['questions' => $questions, 'errors' => $errors] = $importer->parse();
        foreach ($errors as $error) {
            $this->error($error);
        }
        $this->info(count($questions) . ' valid questions, ' . count($errors) . ' errors');

        return $errors ? 1 : 0;
    }

    try {
        $stats = $importer->import();
    } catch (RuntimeException $e) {
        $this->error($e->getMessage());

        return 1;
    }

    $this->info(sprintf(
        'Questions: %d created, %d updated, %d unchanged, %d deactivated',
        $stats['created'], $stats['updated'], $stats['unchanged'], $stats['deactivated']
    ));
})->purpose('Import team-game questions from database/questions/*.txt');
