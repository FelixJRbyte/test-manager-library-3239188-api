<?php

declare(strict_types=1);

use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;

uses(RefreshDatabase::class);

// T1 (paso 1) · data-model.md §3, §4, §5, §9, §10; constitution.md Arts. V, VI, VII, X; D8, D9, D14, D25.

it('crea exactamente las seis tablas del esquema library sin ningún DEFAULT ni columnas de estado o auditoría', function (): void {
    $rows = DB::select(<<<'SQL'
        SELECT table_name, ordinal_position, column_name, data_type, is_nullable, column_default
        FROM information_schema.columns
        WHERE table_schema = 'library'
        ORDER BY 1, 2
    SQL);

    $tables = array_values(array_unique(array_map(static fn (object $r): string => $r->table_name, $rows)));
    expect($tables)->toBe(['book', 'category', 'loan', 'loan_item', 'member', 'user']);

    foreach ($rows as $row) {
        expect($row->column_default)->toBeNull("La columna {$row->table_name}.{$row->column_name} no debe tener DEFAULT");
        expect($row->column_name)->not->toBeIn(['created_at', 'updated_at', 'status', 'days_late']);
    }
});

it('declara las restricciones CHECK en el motor (ck_book_available_range, ck_book_total_positive, ck_user_role)', function (): void {
    $checks = DB::select(<<<'SQL'
        SELECT c.conname
        FROM pg_constraint c
        JOIN pg_namespace n ON n.oid = c.connamespace
        WHERE n.nspname = 'library' AND c.contype = 'c'
        ORDER BY c.conname
    SQL);

    $names = array_map(static fn (object $r): string => $r->conname, $checks);

    expect($names)->toContain(
        'ck_book_available_range',
        'ck_book_total_positive',
        'ck_user_role',
    );
});

it('declara los índices únicos, parciales y de acceso de data-model.md §4', function (): void {
    $indexes = DB::select(<<<'SQL'
        SELECT tablename, indexname, indexdef
        FROM pg_indexes
        WHERE schemaname = 'library'
        ORDER BY 1, 2
    SQL);

    $byName = [];
    foreach ($indexes as $idx) {
        $byName[$idx->indexname] = $idx->indexdef;
    }

    expect($byName)->toHaveKeys([
        'ux_category_name',
        'ux_member_document_number',
        'ux_user_username',
        'ux_book_isbn_active',
        'ux_loan_item_loan_book',
        'ix_book_category_title_active',
        'ix_loan_loaned_at',
        'ix_loan_item_book_id',
        'ix_loan_item_due_at_open',
    ]);

    expect($byName['ux_book_isbn_active'])->toContain('WHERE (deleted_at IS NULL)');
    expect($byName['ix_loan_item_due_at_open'])->toContain('WHERE (returned_at IS NULL)');
    expect(array_keys($byName))->not->toContain('ix_user_password_hash');
});

it('siembra las cinco categorías fijas de data-model.md §9', function (): void {
    $categories = DB::table('library.category')->orderBy('id')->get(['id', 'name'])->all();

    expect(array_map(static fn (object $c): array => ['id' => $c->id, 'name' => $c->name], $categories))->toBe([
        ['id' => '11111111-1111-4111-8111-111111111111', 'name' => 'Novela'],
        ['id' => '22222222-2222-4222-8222-222222222222', 'name' => 'Ciencia'],
        ['id' => '33333333-3333-4333-8333-333333333333', 'name' => 'Tecnología'],
        ['id' => '44444444-4444-4444-8444-444444444444', 'name' => 'Historia'],
        ['id' => '55555555-5555-4555-8555-555555555555', 'name' => 'Infantil'],
    ]);
});

it('rechaza en el motor un libro con available_copies fuera de rango o total_copies < 1 y un rol inválido', function (): void {
    expect(fn () => DB::table('library.book')->insert([
        'id' => 'aaaaaaaa-aaaa-4aaa-8aaa-aaaaaaaaaaaa',
        'title' => 'Prueba',
        'author' => 'Autor',
        'isbn' => '9780307474728',
        'category_id' => '11111111-1111-4111-8111-111111111111',
        'total_copies' => 0,
        'available_copies' => 0,
        'cover_key' => null,
        'deleted_at' => null,
    ]))->toThrow(QueryException::class);

    expect(fn () => DB::table('library.user')->insert([
        'id' => 'bbbbbbbb-bbbb-4bbb-8bbb-bbbbbbbbbbbb',
        'username' => 'intruso',
        'password_hash' => 'hash',
        'role' => 'guest',
    ]))->toThrow(QueryException::class);
});
