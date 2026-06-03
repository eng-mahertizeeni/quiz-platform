<?php

namespace App;

use OpenApi\Attributes as OA;

#[OA\Info(
    title: 'Quiz Platform API',
    version: '1.0.0',
    description: 'API documentation for Quiz Platform mobile app'
)]
#[OA\SecurityScheme(
    securityScheme: 'bearer_token',
    type: 'http',
    scheme: 'bearer',
    description: 'Enter your bearer token'
)]
#[OA\Parameter(
    parameter: 'Accept-Language',
    name: 'Accept-Language',
    in: 'header',
    required: false,
    schema: new OA\Schema(type: 'string', default: 'ar'),
    description: 'Language code (ar, en)'
)]
class ApiAnnotations
{
}
