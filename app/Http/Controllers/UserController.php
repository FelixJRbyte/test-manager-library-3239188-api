<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Application\Services\CreateLibrarianService;
use App\Http\Requests\CreateUserRequest;
use Illuminate\Http\JsonResponse;

/**
 * Controlador de alta de bibliotecarios (HU-02, api-contract.md §3.2, RNF-3).
 *
 * La respuesta incluye únicamente { id, username, role } — jamás password ni password_hash.
 */
final readonly class UserController
{
    public function __construct(
        private CreateLibrarianService $createLibrarian,
    ) {
    }

    public function store(CreateUserRequest $request): JsonResponse
    {
        $user = $this->createLibrarian->execute(
            username: (string) $request->validated('username'),
            plainPassword: (string) $request->validated('password'),
            role: (string) $request->validated('role'),
        );

        return new JsonResponse([
            'id' => $user->id,
            'username' => $user->username,
            'role' => $user->role,
        ], 201);
    }
}
