<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Domain\Exceptions\DomainException;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Autorización por rol en cada endpoint (CA-01.3, api-contract.md §4, trampa 3).
 *
 * Se ejecuta ANTES del controlador/servicio para que un 403 jamás revele si un recurso existe.
 */
final class RequireRole
{
    public function handle(Request $request, Closure $next, string ...$allowedRoles): Response
    {
        $role = (string) $request->attributes->get('auth_role', '');
        if ($role === '' || ! in_array($role, $allowedRoles, true)) {
            throw DomainException::forbiddenRole();
        }

        return $next($request);
    }
}
