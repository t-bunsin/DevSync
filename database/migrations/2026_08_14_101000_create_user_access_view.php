<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

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
| GROUP BY u.id is legal under ONLY_FULL_GROUP_BY because MySQL 8 recognises
| the other selected columns as functionally dependent on the primary key.
*/
return new class extends Migration
{
    public function up(): void
    {
        DB::statement('DROP VIEW IF EXISTS `v_user_access`');

        DB::statement(<<<'SQL'
            CREATE VIEW `v_user_access` AS
            SELECT
                u.id,
                u.email,
                u.phone,
                u.display_name,
                u.status,
                u.preferred_locale,
                GROUP_CONCAT(r.code ORDER BY r.sort_order SEPARATOR ',') AS roles,
                MAX(CASE WHEN ur.is_primary THEN r.code END) AS primary_role
            FROM users u
            LEFT JOIN user_roles ur ON ur.user_id = u.id
            LEFT JOIN roles r       ON r.id = ur.role_id
            WHERE u.deleted_at IS NULL
            GROUP BY u.id
        SQL);
    }

    public function down(): void
    {
        DB::statement('DROP VIEW IF EXISTS `v_user_access`');
    }
};
