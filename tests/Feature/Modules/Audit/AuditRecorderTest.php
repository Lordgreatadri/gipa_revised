<?php

namespace Tests\Feature\Modules\Audit;

use App\Modules\Audit\Application\AuditEntry;
use App\Modules\Audit\Application\Contracts\AuditRecorder;
use App\Modules\Audit\Domain\Exceptions\AuditRecordIsImmutable;
use App\Modules\Audit\Infrastructure\Persistence\AuditRecord;
use App\Modules\Identity\Domain\Models\User;
use App\Modules\Shared\Application\RequestContext;
use App\Modules\Shared\Domain\RequestSource;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class AuditRecorderTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_writes_a_record_with_correlation_fields_and_redacted_payloads(): void
    {
        $user = User::factory()->create();
        $context = $this->app->make(RequestContext::class);
        $context->setRequestId('11111111-1111-4111-8111-111111111111');
        $context->setSource(RequestSource::Api);

        $this->app->make(AuditRecorder::class)->record(
            AuditEntry::for('test.action', $user)
                ->causedBy($user)
                ->withChange(['password' => 'old', 'name' => 'A'], ['password' => 'new', 'name' => 'B'])
                ->withMetadata(['otp' => '123456', 'note' => 'kept'])
                ->withReason('because')
        );

        $record = AuditRecord::query()->where('event', 'test.action')->firstOrFail();

        $this->assertNotEmpty($record->uuid);
        $this->assertSame('11111111-1111-4111-8111-111111111111', $record->request_id);
        $this->assertSame('API', $record->source);
        $this->assertSame((string) $user->id, (string) $record->subject_id);
        $this->assertSame((string) $user->id, (string) $record->causer_id);
        $this->assertSame('web', $record->auth_context);
        $this->assertSame('iomp', $record->log_name);

        $this->assertSame('[REDACTED]', $record->properties['before']['password']);
        $this->assertSame('[REDACTED]', $record->properties['after']['password']);
        $this->assertSame('[REDACTED]', $record->properties['metadata']['otp']);
        $this->assertSame('kept', $record->properties['metadata']['note']);
        $this->assertSame('A', $record->properties['before']['name']);
        $this->assertSame('because', $record->properties['reason']);
    }

    public function test_it_falls_back_to_the_authenticated_user_as_causer(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $this->app->make(AuditRecorder::class)->record(AuditEntry::for('test.implicit_causer'));

        $record = AuditRecord::query()->where('event', 'test.implicit_causer')->firstOrFail();
        $this->assertSame((string) $user->id, (string) $record->causer_id);
    }

    public function test_records_cannot_be_updated_through_the_model(): void
    {
        $this->app->make(AuditRecorder::class)->record(AuditEntry::for('test.immutable'));
        $record = AuditRecord::query()->where('event', 'test.immutable')->firstOrFail();

        $this->expectException(AuditRecordIsImmutable::class);

        $record->update(['description' => 'tampered']);
    }

    public function test_records_cannot_be_deleted_through_the_model(): void
    {
        $this->app->make(AuditRecorder::class)->record(AuditEntry::for('test.immutable'));
        $record = AuditRecord::query()->where('event', 'test.immutable')->firstOrFail();

        $this->expectException(AuditRecordIsImmutable::class);

        $record->delete();
    }

    public function test_the_database_rejects_updates_and_deletes_even_with_raw_sql(): void
    {
        $this->app->make(AuditRecorder::class)->record(AuditEntry::for('test.trigger'));
        $table = config('activitylog.table_name');

        // Nested transactions become savepoints, so a rejected statement does
        // not poison the test's outer transaction.
        try {
            DB::transaction(fn () => DB::table($table)->where('event', 'test.trigger')->update(['description' => 'tampered']));
            $this->fail('UPDATE should have been rejected by the append-only trigger.');
        } catch (QueryException $e) {
            $this->assertStringContainsString('append-only', $e->getMessage());
        }

        try {
            DB::transaction(fn () => DB::table($table)->where('event', 'test.trigger')->delete());
            $this->fail('DELETE should have been rejected by the append-only trigger.');
        } catch (QueryException $e) {
            $this->assertStringContainsString('append-only', $e->getMessage());
        }

        $this->assertSame(1, DB::table($table)->where('event', 'test.trigger')->where('description', 'test.trigger')->count());
    }

    public function test_the_source_column_is_constrained(): void
    {
        $this->expectException(QueryException::class);

        DB::table(config('activitylog.table_name'))->insert([
            'log_name' => 'iomp',
            'description' => 'x',
            'event' => 'x',
            'properties' => '{}',
            'source' => 'BOGUS',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}
