<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/*
| KH-WORKS | Module 01 — Identity and Access (10 of 10)
|
| Everything the app needs at login, in one read.
|
| Two rewrites for MySQL:
|   array_agg(r.code ORDER BY r.sort_order)
|       -> GROUP_CONCAT(...). MySQL has no array type, so `roles` comes back as
|          a comma-separated string. Callers must explode() it rather than
|          reading an array. GROUP_CONCAT also truncates at group_concat_max_len
|          (1024 bytes by default) — not a risk with three roles, but it is a
|          silent truncation if the role list ever grows.
|   max(r.code) FILTER (WHERE ur.is_primary)
|       -> MAX(CASE WHEN ur.is_primary THEN r.code END), the standard-SQL
|          spelling of the same aggregate filter.
|
| GROUP BY u.id alone is legal under ONLY_FULL_GROUP_BY on MySQL 8, which
| recognises the other selected columns as functionally dependent on the
| primary key. MariaDB does not extend that exception to views, so the
| other u.* columns are wrapped in MAX() — a no-op here since each is
| already single-valued per u.id, and it works unchanged on MySQL 8 too.
| (ANY_VALUE() would read better but isn't available before MariaDB 10.5.)
*/
return new class extends Migration
{
    public function up(): void
    {
        DB::statement('DROP VIEW IF EXISTS v_user_access');

        DB::statement(
            Schema::getConnection()->getDriverName() === 'pgsql'
                ? $this->postgresView()
                : $this->mysqlView()
        );
    }

    public function down(): void
    {
        DB::statement('DROP VIEW IF EXISTS v_user_access');
    }

    /*
    | PostgreSQL keeps the aggregate filter, but `roles` is still built with
    | string_agg rather than array_agg so both engines hand back the same
    | comma-separated string and callers do not have to branch.
    */
    private function postgresView(): string
    {
        return <<<'SQL'
            CREATE VIEW v_user_access AS
            SELECT
                u.id,
                MAX(u.email) AS email,
                MAX(u.phone) AS phone,
                MAX(u.display_name) AS display_name,
                MAX(u.status) AS status,
                MAX(u.preferred_locale) AS preferred_locale,
                string_agg(r.code, ',' ORDER BY r.sort_order) AS roles,
                MAX(r.code) FILTER (WHERE ur.is_primary) AS primary_role
            FROM users u
            LEFT JOIN user_roles ur ON ur.user_id = u.id
            LEFT JOIN roles r       ON r.id = ur.role_id
            WHERE u.deleted_at IS NULL
            GROUP BY u.id
        SQL;
    }

    private function mysqlView(): string
    {
        return <<<'SQL'
            CREATE VIEW `v_user_access` AS
            SELECT
                u.id,
                MAX(u.email) AS email,
                MAX(u.phone) AS phone,
                MAX(u.display_name) AS display_name,
                MAX(u.status) AS status,
                MAX(u.preferred_locale) AS preferred_locale,
                GROUP_CONCAT(r.code ORDER BY r.sort_order SEPARATOR ',') AS roles,
                MAX(CASE WHEN ur.is_primary THEN r.code END) AS primary_role
            FROM users u
            LEFT JOIN user_roles ur ON ur.user_id = u.id
            LEFT JOIN roles r       ON r.id = ur.role_id
            WHERE u.deleted_at IS NULL
            GROUP BY u.id
        SQL;
    }
};
