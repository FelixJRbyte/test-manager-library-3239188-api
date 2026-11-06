<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Application\Ports\TokenIssuer;
use App\Domain\Exceptions\DomainException;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Autenticación Bearer JWT para todos los endpoints protegidos (CA-01.3, api-contract.md §1 y §4, ADR-L001).
 *
 * Deja `auth_user_id` y `auth_role` en los atributos de la petición sin cargar datos personales.
 */
final readonly class JwtAuthenticate
{
    public function __construct(
        private TokenIssuer $tokens,
    ) {
    }

    public function handle(Request $request, Closure $next): Response
    {
        $header = (string) $request->header('Authorization', '');
        if (! str_starts_with($header, 'Bearer ')) {
            throw DomainException::invalidCredentials('Sesión no válida o vencida. Inicie sesión de nuevo.');
        }

        $rawToken = trim(substr($header, 7));
        if ($rawToken === '') {
            throw DomainException::invalidCredentials('Sesión no válida o vencida. Inicie sesión de nuevo.');
        }

        $claims = $this->tokens->verify($rawToken);

        $request->attributes->set('auth_user_id', $claims->userId);
        $request->attributes->set('auth_role', $claims->role);

        return $next($request);
    }
}
