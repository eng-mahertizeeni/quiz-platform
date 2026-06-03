<?php

namespace App\Swagger;

use L5Swagger\ConfigFactory;
use L5Swagger\GeneratorFactory as BaseFactory;
use L5Swagger\SecurityDefinitions;

class GeneratorFactory extends BaseFactory
{
    public function make(string $documentation): Generator
    {
        $config = app(ConfigFactory::class)->documentationConfig($documentation);

        $paths = $config['paths'];
        $scanOptions = $config['scanOptions'] ?? [];
        $constants = $config['constants'] ?? [];
        $yamlCopyRequired = $config['generate_yaml_copy'] ?? false;

        $secSchemesConfig = $config['securityDefinitions']['securitySchemes'] ?? [];
        $secConfig = $config['securityDefinitions']['security'] ?? [];

        $security = new SecurityDefinitions($secSchemesConfig, $secConfig);

        return new Generator(
            $paths,
            $constants,
            $yamlCopyRequired,
            $security,
            $scanOptions
        );
    }
}
