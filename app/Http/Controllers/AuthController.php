<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Application\Services\LoginService;
use App\Http\Requests\LoginRequest;
use Illuminate\Http\JsonResponse;

/**
 * Controlador de inicio de sesión (HU-01, api-contract.md §3.1, trampa 7).
 *
 * Jamás registra en logs el cuerpo de la petición ni la contraseña (RNF-3, Art. IX).
 */
final readonly class AuthController
{
    public function __construct(
        private LoginService $loginService,
    ) {
    }

    public function login(LoginRequest $request): JsonResponse
    {
        $issued = $this->loginService->login(
            username: (string) $request->validated('username'),
            plainPassword: (string) $request->validated('password'),
        );

        return new JsonResponse([
            'access_token' => $issued->accessToken,
            'token_type' => $issued->tokenType,
            'expires_in' => $issued->expiresIn,
            'role' => $issued->role,
        ], 200);
    }
}
