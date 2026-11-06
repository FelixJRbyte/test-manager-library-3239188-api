<?php

declare(strict_types=1);

namespace App\Domain\Exceptions;

/**
 * Excepción única de dominio y aplicación (architecture.md §5, api-contract.md §2).
 *
 * No importa nada fuera de la biblioteca estándar de PHP (constitution.md Art. I).
 * Solo el adaptador HTTP la traduce al cuerpo JSON único { code, message, details }.
 */
final class DomainException extends \DomainException
{
    /**
     * @param  list<array{field: string, issue: string}>  $details
     */
    public function __construct(
        public readonly string $errorCode,
        string $message,
        public readonly int $httpStatus,
        public readonly array $details = [],
    ) {
        parent::__construct($message);
    }

    public static function invalidCredentials(
        string $message = 'Usuario o contraseña incorrectos.',
    ): self {
        return new self('INVALID_CREDENTIALS', $message, 401);
    }

    public static function forbiddenRole(
        string $message = 'No tiene permisos para realizar esta operación.',
    ): self {
        return new self('FORBIDDEN_ROLE', $message, 403);
    }

    public static function notFound(
        string $message = 'El recurso solicitado no existe.',
    ): self {
        return new self('NOT_FOUND', $message, 404);
    }

    public static function usernameTaken(string $username): self
    {
        return new self(
            'USERNAME_TAKEN',
            "El nombre de usuario «{$username}» ya está en uso.",
            409,
            [['field' => 'username', 'issue' => 'ya está en uso']],
        );
    }

    public static function isbnTaken(string $isbn): self
    {
        return new self(
            'ISBN_TAKEN',
            "Ya existe un libro activo con el ISBN «{$isbn}».",
            409,
            [['field' => 'isbn', 'issue' => 'ya está registrado en un libro activo']],
        );
    }

    public static function documentTaken(string $documentNumber): self
    {
        return new self(
            'DOCUMENT_TAKEN',
            "Ya existe un socio con el documento «{$documentNumber}».",
            409,
            [['field' => 'document_number', 'issue' => 'ya está registrado']],
        );
    }

    public static function bookHasOpenLoans(string $title): self
    {
        return new self(
            'BOOK_HAS_OPEN_LOANS',
            "No se puede dar de baja «{$title}» porque tiene préstamos abiertos.",
            409,
            [['field' => 'id', 'issue' => 'tiene préstamos sin devolver']],
        );
    }

    public static function bookNotAvailable(string $title, int $itemIndex = 0): self
    {
        return new self(
            'BOOK_NOT_AVAILABLE',
            "No quedan ejemplares disponibles de «{$title}».",
            409,
            [['field' => "items[{$itemIndex}].book_id", 'issue' => 'sin ejemplares']],
        );
    }

    public static function memberHasOverdue(): self
    {
        return new self(
            'MEMBER_HAS_OVERDUE',
            'El socio tiene préstamos vencidos sin devolver y no puede solicitar nuevos libros.',
            409,
            [['field' => 'member_id', 'issue' => 'tiene préstamos vencidos']],
        );
    }

    public static function itemAlreadyReturned(): self
    {
        return new self(
            'ITEM_ALREADY_RETURNED',
            'Esta línea de préstamo ya fue devuelta.',
            409,
            [['field' => 'item_id', 'issue' => 'ya devuelta']],
        );
    }

    public static function memberLimitExceeded(int $currentActiveItems, int $requestedItems): self
    {
        return new self(
            'MEMBER_LIMIT_EXCEEDED',
            "El socio ya tiene {$currentActiveItems} libro(s) sin devolver; con {$requestedItems} más superaría el máximo de 3.",
            422,
            [['field' => 'items', 'issue' => "actualmente tiene {$currentActiveItems} sin devolver (máximo 3)"]],
        );
    }

    public static function duplicateBookInLoan(int $itemIndex): self
    {
        return new self(
            'DUPLICATE_BOOK_IN_LOAN',
            'No se puede repetir el mismo libro dentro de un mismo préstamo.',
            422,
            [['field' => "items[{$itemIndex}].book_id", 'issue' => 'libro repetido en el préstamo']],
        );
    }

    /**
     * @param  list<array{field: string, issue: string}>  $details
     */
    public static function validationError(string $message, array $details = []): self
    {
        return new self('VALIDATION_ERROR', $message, 422, $details);
    }

    public static function invalidDateRange(
        string $message = 'La fecha final no puede ser anterior a la fecha inicial.',
    ): self {
        return new self(
            'INVALID_DATE_RANGE',
            $message,
            422,
            [['field' => 'to', 'issue' => 'debe ser igual o posterior a from']],
        );
    }
}
