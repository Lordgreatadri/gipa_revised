<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * ADR-0009 columns on the activitylog table plus database-level
 * append-only protection (PostgreSQL trigger).
 */
return new class extends Migration
{
    public function up(): void
    {
        $table = (string) config('activitylog.table_name');
        $connection = config('activitylog.database_connection');

        Schema::connection($connection)->table($table, function (Blueprint $blueprint) use ($table): void {
            $blueprint->uuid('uuid')->nullable()->unique()->after('id');
            $blueprint->uuid('request_id')->nullable()->index()->after('batch_uuid');
            $blueprint->string('source', 8)->default('UI')->after('request_id');
            $blueprint->string('ip_address', 45)->nullable()->after('source');
            $blueprint->string('user_agent', 512)->nullable()->after('ip_address');
            $blueprint->string('auth_context', 32)->nullable()->after('user_agent');
            $blueprint->index(['causer_type', 'causer_id', 'created_at'], $table.'_causer_created_idx');
            $blueprint->index(['event', 'created_at'], $table.'_event_created_idx');
        });

        if (Schema::connection($connection)->getConnection()->getDriverName() === 'pgsql') {
            DB::connection($connection)->statement(<<<SQL
                ALTER TABLE "{$table}"
                    ADD CONSTRAINT {$table}_source_check
                    CHECK (source IN ('UI', 'API', 'CLI', 'JOB'))
            SQL);

            $body = <<<'PLPGSQL'
                BEGIN
                    RAISE EXCEPTION 'audit records are append-only (% on %)', TG_OP, TG_TABLE_NAME
                        USING ERRCODE = 'integrity_constraint_violation';
                END;
            PLPGSQL;

            DB::connection($connection)->statement(
                "CREATE OR REPLACE FUNCTION {$table}_reject_mutation() RETURNS trigger AS \$body\$\n"
                .$body
                ."\n\$body\$ LANGUAGE plpgsql"
            );

            DB::connection($connection)->statement(<<<SQL
                CREATE TRIGGER {$table}_append_only
                    BEFORE UPDATE OR DELETE ON "{$table}"
                    FOR EACH ROW EXECUTE FUNCTION {$table}_reject_mutation();
            SQL);
        }
    }

    public function down(): void
    {
        $table = (string) config('activitylog.table_name');
        $connection = config('activitylog.database_connection');

        if (Schema::connection($connection)->getConnection()->getDriverName() === 'pgsql') {
            DB::connection($connection)->statement("DROP TRIGGER IF EXISTS {$table}_append_only ON \"{$table}\"");
            DB::connection($connection)->statement("DROP FUNCTION IF EXISTS {$table}_reject_mutation()");
            DB::connection($connection)->statement("ALTER TABLE \"{$table}\" DROP CONSTRAINT IF EXISTS {$table}_source_check");
        }

        Schema::connection($connection)->table($table, function (Blueprint $blueprint) use ($table): void {
            $blueprint->dropIndex($table.'_causer_created_idx');
            $blueprint->dropIndex($table.'_event_created_idx');
            $blueprint->dropColumn(['uuid', 'request_id', 'source', 'ip_address', 'user_agent', 'auth_context']);
        });
    }
};
