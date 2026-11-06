<?php

declare(strict_types=1);

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

/**
 * Único punto de composición del sistema (constitution.md Art. III).
 *
 * Solo aquí se enlaza cada puerto de Application\Ports con su adaptador
 * de Infrastructure. Fuera de esta clase no se instancia infraestructura;
 * lo verifica la prueba de arquitectura.
 */
final class PortBindingsServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        // Los enlaces puerto -> adaptador se añaden conforme aparecen los casos de uso (T1..T9).
    }
}
