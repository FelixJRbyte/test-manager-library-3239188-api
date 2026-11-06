<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Esquema completo de Manager Library según data-model.md §3, §4, §5 y §9.
 *
 * - Esquema `library`, tablas en singular, sin `DEFAULT`, sin `created_at`/`updated_at`.
 * - Incluye desde el inicio `ck_book_total_positive` y `ck_user_role` (D9, Art. VI y X).
 * - Siembra únicamente las cinco categorías con UUID fijos (data-model.md §9, D3).
 */
return new class extends Migration
{
    public function up(): void
    {
        DB::statement('CREATE SCHEMA IF NOT EXISTS library');

        Schema::create('library.category', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->string('name', 120)->unique('ux_category_name');
        });

        Schema::create('library.book', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->string('title', 250);
            $table->string('author', 200);
            $table->char('isbn', 13);
            $table->uuid('category_id');
            $table->integer('total_copies');
            $table->integer('available_copies');
            $table->string('cover_key', 512)->nullable();
            $table->timestampTz('deleted_at')->nullable();

            $table->foreign('category_id', 'fk_book_category')
                ->references('id')
                ->on('library.category')
                ->restrictOnDelete()
                ->noActionOnUpdate();
        });

        DB::statement(<<<'SQL'
            ALTER TABLE library.book
                ADD CONSTRAINT ck_book_total_positive CHECK (total_copies >= 1),
                ADD CONSTRAINT ck_book_available_range CHECK (available_copies >= 0 AND available_copies <= total_copies)
        SQL);

        DB::statement(<<<'SQL'
            CREATE UNIQUE INDEX ux_book_isbn_active
                ON library.book (isbn)
                WHERE deleted_at IS NULL
        SQL);

        DB::statement(<<<'SQL'
            CREATE INDEX ix_book_category_title_active
                ON library.book (category_id, title)
                WHERE deleted_at IS NULL
        SQL);

        Schema::create('library.member', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->string('full_name', 200);
            $table->string('document_number', 30)->unique('ux_member_document_number');
        });

        Schema::create('library.user', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->string('username', 120)->unique('ux_user_username');
            $table->string('password_hash', 512);
            $table->string('role', 40);
        });

        DB::statement(<<<'SQL'
            ALTER TABLE library."user"
                ADD CONSTRAINT ck_user_role CHECK (role IN ('admin', 'librarian'))
        SQL);

        Schema::create('library.loan', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->uuid('member_id');
            $table->uuid('loaned_by_user_id');
            $table->timestampTz('loaned_at');

            $table->foreign('member_id', 'fk_loan_member')
                ->references('id')
                ->on('library.member')
                ->restrictOnDelete()
                ->noActionOnUpdate();

            $table->foreign('loaned_by_user_id', 'fk_loan_user')
                ->references('id')
                ->on('library.user')
                ->restrictOnDelete()
                ->noActionOnUpdate();

            $table->index('loaned_at', 'ix_loan_loaned_at');
        });

        Schema::create('library.loan_item', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->uuid('loan_id');
            $table->uuid('book_id');
            $table->string('book_title', 250);
            $table->char('isbn', 13);
            // Sin FK a propósito: valor congelado en el instante del préstamo (data-model.md §3, ADR-004).
            $table->string('category_name', 120);
            $table->timestampTz('due_at');
            $table->timestampTz('returned_at')->nullable();

            $table->foreign('loan_id', 'fk_loan_item_loan')
                ->references('id')
                ->on('library.loan')
                ->cascadeOnDelete()
                ->noActionOnUpdate();

            $table->foreign('book_id', 'fk_loan_item_book')
                ->references('id')
                ->on('library.book')
                ->restrictOnDelete()
                ->noActionOnUpdate();

            $table->unique(['loan_id', 'book_id'], 'ux_loan_item_loan_book');
            $table->index('book_id', 'ix_loan_item_book_id');
        });

        DB::statement(<<<'SQL'
            CREATE INDEX ix_loan_item_due_at_open
                ON library.loan_item (due_at)
                WHERE returned_at IS NULL
        SQL);

        DB::table('library.category')->insert([
            ['id' => '11111111-1111-4111-8111-111111111111', 'name' => 'Novela'],
            ['id' => '22222222-2222-4222-8222-222222222222', 'name' => 'Ciencia'],
            ['id' => '33333333-3333-4333-8333-333333333333', 'name' => 'Tecnología'],
            ['id' => '44444444-4444-4444-8444-444444444444', 'name' => 'Historia'],
            ['id' => '55555555-5555-4555-8555-555555555555', 'name' => 'Infantil'],
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('library.loan_item');
        Schema::dropIfExists('library.loan');
        Schema::dropIfExists('library.user');
        Schema::dropIfExists('library.member');
        Schema::dropIfExists('library.book');
        Schema::dropIfExists('library.category');
        DB::statement('DROP SCHEMA IF EXISTS library');
    }
};
