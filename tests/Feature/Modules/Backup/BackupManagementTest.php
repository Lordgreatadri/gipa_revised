<?php

namespace Tests\Feature\Modules\Backup;

use App\Modules\Backup\Application\Contracts\DatabaseBackupRunner;
use App\Modules\Backup\Domain\Models\BackupRun;
use App\Modules\Backup\Jobs\RunDatabaseBackup;
use App\Modules\Identity\Domain\Access\AccessPermissions;
use App\Modules\Identity\Domain\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Storage;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;
use ZipArchive;

class BackupManagementTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('backups');

        foreach ([
            AccessPermissions::ADMIN_DASHBOARD,
            AccessPermissions::BACKUPS_VIEW,
            AccessPermissions::BACKUPS_MANAGE,
            AccessPermissions::BACKUPS_DOWNLOAD,
        ] as $permission) {
            Permission::findOrCreate($permission, 'web');
        }
    }

    public function test_a_district_officer_cannot_open_database_backups(): void
    {
        $officer = User::factory()->staff()->create();
        $officer->givePermissionTo(AccessPermissions::ADMIN_DASHBOARD);

        $this->actingAs($officer)->get(route('admin.backups.index'))->assertForbidden();
    }

    public function test_a_content_manager_can_see_archives_and_the_restoration_procedure(): void
    {
        Storage::disk('backups')->put('IOMP/notes.txt', 'ignored');
        Storage::disk('backups')->put('IOMP/iomp-older.zip', 'older');
        Storage::disk('backups')->put('IOMP/iomp-newer.zip', 'newer');
        touch(Storage::disk('backups')->path('IOMP/iomp-older.zip'), time() - 3600);

        $this->actingAs($this->manager())
            ->get(route('admin.backups.index'))
            ->assertOk()
            ->assertSee('Database backups')
            ->assertSee('iomp-newer.zip')
            ->assertSee('iomp-older.zip')
            ->assertDontSee('notes.txt')
            ->assertSee('This screen does not restore')
            ->assertSee('Download archive')
            ->assertSee('Check archive')
            ->assertSee('Delete archive')
            ->assertSee('Confirm delete')
            ->assertDontSee('Restore database');
    }

    public function test_requesting_a_backup_queues_one_run_and_refuses_a_second(): void
    {
        Queue::fake();
        $manager = $this->manager();

        $this->actingAs($manager)
            ->post(route('admin.backups.store'))
            ->assertRedirect();

        $run = BackupRun::query()->firstOrFail();
        $this->assertSame(BackupRun::TYPE_DATABASE, $run->type);
        $this->assertSame(BackupRun::STATUS_QUEUED, $run->status);
        $this->assertSame($manager->id, $run->triggered_by);
        Queue::assertPushed(RunDatabaseBackup::class);

        $this->actingAs($manager)
            ->post(route('admin.backups.store'))
            ->assertRedirect()
            ->assertSessionHas('backup_error');

        $this->assertSame(1, BackupRun::query()->count());
    }

    public function test_a_viewer_cannot_download_or_delete_an_archive(): void
    {
        Storage::disk('backups')->put('IOMP/iomp-one.zip', 'archive-bytes');
        $token = rtrim(strtr(base64_encode('IOMP/iomp-one.zip'), '+/', '-_'), '=');
        $viewer = User::factory()->staff()->create();
        $viewer->givePermissionTo([
            AccessPermissions::ADMIN_DASHBOARD,
            AccessPermissions::BACKUPS_VIEW,
        ]);

        $this->actingAs($viewer)->get(route('admin.backups.download', $token))->assertForbidden();
        $this->actingAs($viewer)->delete(route('admin.backups.destroy', $token))->assertForbidden();
        Storage::disk('backups')->assertExists('IOMP/iomp-one.zip');
    }

    public function test_a_manager_can_download_and_delete_only_a_real_archive(): void
    {
        Storage::disk('backups')->put('IOMP/iomp-one.zip', 'archive-bytes');
        $token = rtrim(strtr(base64_encode('IOMP/iomp-one.zip'), '+/', '-_'), '=');
        $outside = rtrim(strtr(base64_encode('../../../.env'), '+/', '-_'), '=');
        $manager = $this->manager();

        $this->actingAs($manager)
            ->get(route('admin.backups.download', $token))
            ->assertOk()
            ->assertDownload('iomp-one.zip');

        $this->actingAs($manager)->get(route('admin.backups.download', $outside))->assertNotFound();
        $this->actingAs($manager)->delete(route('admin.backups.destroy', $outside))->assertNotFound();

        $this->actingAs($manager)->delete(route('admin.backups.destroy', $token))->assertRedirect();
        Storage::disk('backups')->assertMissing('IOMP/iomp-one.zip');
    }

    public function test_an_integrity_check_confirms_a_dump_without_restoring_it(): void
    {
        Storage::disk('backups')->makeDirectory('IOMP');
        $absolute = Storage::disk('backups')->path('IOMP/iomp-check.zip');
        $zip = new ZipArchive;
        $zip->open($absolute, ZipArchive::CREATE);
        $zip->addFromString('db-dumps/database.sql', 'SELECT 1;');
        $zip->close();

        $run = new BackupRun;
        $run->forceFill([
            'type' => BackupRun::TYPE_DATABASE,
            'status' => BackupRun::STATUS_COMPLETED,
            'disk' => 'backups',
            'destination_path' => 'IOMP/iomp-check.zip',
            'finished_at' => now(),
        ])->save();

        $token = rtrim(strtr(base64_encode('IOMP/iomp-check.zip'), '+/', '-_'), '=');

        $this->actingAs($this->manager())
            ->post(route('admin.backups.inspect'), ['archive' => $token])
            ->assertRedirect()
            ->assertSessionHas('status');

        $this->assertNotNull($run->fresh()->verified_at);
        $this->assertSame(BackupRun::STATUS_COMPLETED, $run->fresh()->status);
    }

    public function test_a_failed_dump_is_recorded_without_the_database_password(): void
    {
        $this->app->instance(DatabaseBackupRunner::class, new class implements DatabaseBackupRunner
        {
            public function dump(): void
            {
                throw new \RuntimeException('pg_dump failed PGPASSWORD=super-secret');
            }

            public function applyRetention(): void {}
        });

        $run = new BackupRun;
        $run->forceFill([
            'type' => BackupRun::TYPE_DATABASE,
            'status' => BackupRun::STATUS_QUEUED,
            'disk' => 'backups',
        ])->save();

        app()->call([new RunDatabaseBackup($run->id), 'handle']);

        $run->refresh();
        $this->assertSame(BackupRun::STATUS_FAILED, $run->status);
        $this->assertStringNotContainsString('super-secret', (string) $run->failure_reason);
        $this->assertStringContainsString('PGPASSWORD=[redacted]', (string) $run->failure_reason);
    }

    public function test_a_completed_dump_stores_the_archive_checksum(): void
    {
        Storage::disk('backups')->put('IOMP/iomp-done.zip', 'archive-bytes');

        $this->app->instance(DatabaseBackupRunner::class, new class implements DatabaseBackupRunner
        {
            public function dump(): void {}

            public function applyRetention(): void {}
        });

        $run = new BackupRun;
        $run->forceFill([
            'type' => BackupRun::TYPE_DATABASE,
            'status' => BackupRun::STATUS_QUEUED,
            'disk' => 'backups',
        ])->save();

        app()->call([new RunDatabaseBackup($run->id), 'handle']);

        $run->refresh();
        $this->assertSame(BackupRun::STATUS_COMPLETED, $run->status);
        $this->assertSame('IOMP/iomp-done.zip', $run->destination_path);
        $this->assertSame(hash('sha256', 'archive-bytes'), $run->checksum);
    }

    public function test_retention_uses_the_backup_runner(): void
    {
        $runner = new class implements DatabaseBackupRunner
        {
            public bool $cleaned = false;

            public function dump(): void {}

            public function applyRetention(): void
            {
                $this->cleaned = true;
            }
        };
        $this->app->instance(DatabaseBackupRunner::class, $runner);

        $this->actingAs($this->manager())
            ->post(route('admin.backups.retention'))
            ->assertRedirect()
            ->assertSessionHas('status');

        $this->assertTrue($runner->cleaned);
    }

    public function test_the_scheduler_command_queues_a_database_backup(): void
    {
        Queue::fake();

        $this->artisan('backup:database')->assertSuccessful();

        $this->assertSame(1, BackupRun::query()->count());
        $this->assertNull(BackupRun::query()->firstOrFail()->triggered_by);
        Queue::assertPushed(RunDatabaseBackup::class);
    }

    private function manager(): User
    {
        $manager = User::factory()->staff()->create();
        $manager->givePermissionTo([
            AccessPermissions::ADMIN_DASHBOARD,
            AccessPermissions::BACKUPS_VIEW,
            AccessPermissions::BACKUPS_MANAGE,
            AccessPermissions::BACKUPS_DOWNLOAD,
        ]);

        return $manager;
    }
}
