<?php

declare(strict_types=1);

/*
 * Único origen permitido: el del frontend, leído de CORS_ORIGIN (api-contract.md §4).
 * Si la variable no está definida, `allowed_origins` queda vacío (cierra por defecto,
 * nunca `*`). Sin Sanctum (D4, ADR-L001).
 */
$origin = trim((string) env('CORS_ORIGIN', ''));

return [

    'paths' => ['api/*'],

    'allowed_methods' => ['GET', 'POST', 'PUT', 'DELETE', 'OPTIONS'],

    'allowed_origins' => $origin !== '' ? [$origin] : [],

    'allowed_origins_patterns' => [],

    'allowed_headers' => ['*'],

    'exposed_headers' => [],

    'max_age' => 0,

    'supports_credentials' => false,

];
