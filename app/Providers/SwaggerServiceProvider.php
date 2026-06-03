<?php

namespace App\Providers;

use App\Swagger\GeneratorFactory;
use Illuminate\Support\ServiceProvider;

class SwaggerServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(
            \L5Swagger\GeneratorFactory::class,
            GeneratorFactory::class
        );
    }
}
