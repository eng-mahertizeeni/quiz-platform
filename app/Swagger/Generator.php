<?php

namespace App\Swagger;

use L5Swagger\Generator as L5SwaggerGenerator;
use OpenApi\Analysers\AttributeAnnotationFactory;
use OpenApi\Analysers\DocBlockAnnotationFactory;
use OpenApi\Analysers\ReflectionAnalyser;

class Generator extends L5SwaggerGenerator
{
    protected function setAnalyser(\OpenApi\Generator $openApiGenerator): void
    {
        $openApiGenerator->setAnalyser(new ReflectionAnalyser([
            new AttributeAnnotationFactory(),
            new DocBlockAnnotationFactory(),
        ]));
    }
}
