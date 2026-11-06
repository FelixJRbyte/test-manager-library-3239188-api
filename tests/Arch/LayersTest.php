<?php

declare(strict_types=1);

// T0 · constitution.md Artículos I, II y III (traduccion §8, ADR-L004).

arch('Art. I: el dominio no depende de nada externo ni de otras capas')
    ->expect('App\Domain')
    ->not->toUse([
        'Illuminate',
        'Eloquent',
        'Firebase',
        'Symfony',
        'Carbon',
        'App\Application',
        'App\Infrastructure',
        'App\Http',
        'App\Providers',
    ]);

arch('Art. II: la aplicacion solo conoce el dominio y sus propios puertos')
    ->expect('App\Application')
    ->not->toUse([
        'App\Infrastructure',
        'App\Http',
        'App\Providers',
        'Illuminate\Database',
        'Illuminate\Http',
        'Eloquent',
        'Firebase',
    ]);

arch('Art. III: Infrastructure no se usa en Domain, Application ni Http')
    ->expect('App\Infrastructure')
    ->not->toBeUsedIn([
        'App\Domain',
        'App\Application',
        'App\Http',
    ]);

it('Art. III: ninguna clase fuera de PortBindingsServiceProvider referencia App\\Infrastructure', function (): void {
    $appRoot = dirname(__DIR__, 2).DIRECTORY_SEPARATOR.'app';
    $allowed = realpath($appRoot.DIRECTORY_SEPARATOR.'Providers'.DIRECTORY_SEPARATOR.'PortBindingsServiceProvider.php');
    $infraDir = realpath($appRoot.DIRECTORY_SEPARATOR.'Infrastructure');

    $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($appRoot));
    $violations = [];

    foreach ($iterator as $file) {
        if (! $file->isFile() || $file->getExtension() !== 'php') {
            continue;
        }

        $real = $file->getRealPath();
        if ($real === $allowed) {
            continue;
        }
        if ($infraDir !== false && str_starts_with($real, $infraDir.DIRECTORY_SEPARATOR)) {
            continue;
        }

        $contents = (string) file_get_contents($real);
        if (preg_match('/\bApp\\\\Infrastructure\\\\/', $contents) === 1) {
            $violations[] = substr($real, strlen($appRoot) + 1);
        }
    }

    expect($violations)->toBe([]);
});
