<?php

declare(strict_types=1);

/*
 * Sobrescribe la configuración base de Laravel 11.
 *
 * LoadConfiguration fusiona `connections` en segundo nivel: el bloque `pgsql`
 * de aquí reemplaza entero al del framework, por eso incluye las diez claves.
 * Ver D8, D14 y D25 en docs/laravel-react/decisiones.md.
 */
return [

    'default' => env('DB_CONNECTION', 'pgsql'),

    'connections' => [

        'pgsql' => [
            'driver' => 'pgsql',
            'url' => env('DB_URL'),
            'host' => env('DB_HOST', '127.0.0.1'),
            'port' => env('DB_PORT', '5432'),
            'database' => env('DB_DATABASE', 'library'),
            'username' => env('DB_USERNAME', 'library'),
            'password' => env('DB_PASSWORD', ''),
            'charset' => env('DB_CHARSET', 'utf8'),
            'prefix' => '',
            'prefix_indexes' => true,
            // `public` va segundo porque ahí vive la tabla `migrations`, que Laravel
            // crea antes de la primera migración (cuando `library` aún no existe, D25).
            'search_path' => env('DB_SEARCH_PATH', 'library,public'),
            'sslmode' => 'prefer',
        ],

    ],

    'migrations' => [
        // Calificado a `public` para que no se mueva a `library` tras la primera migración (D25).
        'table' => 'public.migrations',
        'update_date_on_publish' => true,
    ],

];
