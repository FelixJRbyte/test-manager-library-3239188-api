<?php

declare(strict_types=1);

namespace App\Http\Errors;

use App\Domain\Exceptions\DomainException;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Throwable;

/**
 * Traduce cualquier excepción en rutas `api/*` al cuerpo único de error (RNF-4, api-contract.md §1 y §2).
 *
 * Jamás expone trazas, sentencias SQL, rutas del servidor ni contraseñas (RNF-3, RNF-4, D13).
 */
final class ApiErrorRenderer
{
    public function __invoke(Throwable $e, Request $request): ?JsonResponse
    {
        if (! $request->is('api/*')) {
            return null;
        }

        if ($e instanceof DomainException) {
            return self::response($e->errorCode, $e->getMessage(), $e->httpStatus, $e->details);
        }

        if ($e instanceof ValidationException) {
            $details = [];
            foreach ($e->errors() as $field => $messages) {
                foreach ($messages as $issue) {
                    $details[] = [
                        'field' => (string) $field,
                        'issue' => (string) $issue,
                    ];
                }
            }

            return self::response('VALIDATION_ERROR', 'Los datos enviados no son válidos.', 422, $details);
        }

        if ($e instanceof AuthenticationException) {
            return self::response(
                'INVALID_CREDENTIALS',
                'Sesión no válida o vencida. Inicie sesión de nuevo.',
                401,
            );
        }

        if ($e instanceof AuthorizationException || $e instanceof AccessDeniedHttpException) {
            return self::response(
                'FORBIDDEN_ROLE',
                'No tiene permisos para realizar esta operación.',
                403,
            );
        }

        if (
            $e instanceof ModelNotFoundException
            || $e instanceof NotFoundHttpException
            || $e instanceof MethodNotAllowedHttpException
        ) {
            return self::response('NOT_FOUND', 'El recurso solicitado no existe.', 404);
        }

        if ($e instanceof HttpExceptionInterface) {
            $status = $e->getStatusCode();
            if ($status >= 400 && $status < 500) {
                return self::response('VALIDATION_ERROR', 'La petición no es válida.', 422);
            }
        }

        return self::response(
            'INTERNAL_ERROR',
            'Ocurrió un error interno. Intente nuevamente.',
            500,
        );
    }

    /**
     * @param  list<array{field: string, issue: string}>  $details
     */
    private static function response(
        string $code,
        string $message,
        int $status,
        array $details = [],
    ): JsonResponse {
        return new JsonResponse(
            [
                'code' => $code,
                'message' => $message,
                'details' => $details,
            ],
            $status,
            [],
            JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES,
        );
    }
}
