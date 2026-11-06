<?php

declare(strict_types=1);

namespace App\Providers;

use App\Application\Ports\CategoryRepository;
use App\Application\Ports\Clock;
use App\Application\Ports\PasswordHasher;
use App\Application\Ports\TokenIssuer;
use App\Application\Ports\UserRepository;
use App\Infrastructure\Persistence\EloquentCategoryRepository;
use App\Infrastructure\Persistence\EloquentUserRepository;
use App\Infrastructure\Security\BcryptPasswordHasher;
use App\Infrastructure\Security\JwtTokenIssuer;
use App\Infrastructure\Support\SystemClock;
use Illuminate\Contracts\Container\Container;
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
        $this->app->singleton(Clock::class, SystemClock::class);
        $this->app->singleton(PasswordHasher::class, BcryptPasswordHasher::class);
        $this->app->singleton(
            TokenIssuer::class,
            static fn (Container $app): TokenIssuer => new JwtTokenIssuer(
                clock: $app->make(Clock::class),
                secret: (string) env('JWT_SECRET', ''),
                ttlSeconds: (int) env('JWT_TTL_SECONDS', 28800),
            ),
        );
        $this->app->singleton(UserRepository::class, EloquentUserRepository::class);
        $this->app->singleton(CategoryRepository::class, EloquentCategoryRepository::class);
    }
}
