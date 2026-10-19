# Database backups

Database copies are managed at `/admin/backups` by Super Administrator and Content / Data Manager. Those roles can list archives, queue a dump, apply retention, check that an archive opens, download it, and delete it. A district officer cannot open the screen. There is no restore button. Restoring a government database replaces live records and stays a planned operation.

## What is stored

Each run dumps the application database only. The application tree and `.env` are not packed into the archive. Cache, session, and queue tables are left out of the dump. The archive is a zip on the private `backups` disk under `storage/app/backups`, which is not web-accessible.

Set `BACKUP_ARCHIVE_PASSWORD` so the zip uses AES encryption. Until that password is set, the screen warns that a downloaded file is readable by anyone who obtains it. Set `PG_DUMP_BINARY_PATH` when `pg_dump` is not on the system path. Set `BACKUP_NOTIFICATION_EMAIL` to receive a mail when a dump, a cleanup, or the daily health check fails. Successful dumps are not mailed.

## Schedule and retention

`backup:database` queues a run every day at 02:15 Africa/Accra. `backup:clean` applies retention at 03:00. Both need `php artisan schedule:run` every minute. With the sync queue the dump finishes inside the scheduler. With the database or Redis queue a worker must be running.

Retention keeps every archive for 7 days, then one daily archive for 16 days, one weekly archive for 8 weeks, one monthly archive for 4 months, and one yearly archive for 2 years. The newest archive is never deleted. Storage is also capped at 5,000 MB.

A person can queue one dump at a time from the screen. The request returns immediately. The dump runs on the queue.

## Integrity and restoration

Check confirms the zip opens and contains a database dump. It does not load that dump. A backup that has only been downloaded has not been restore-tested.

To restore: download to an encrypted device, check the archive, extract it with the environment password, restore with `psql` or `pg_restore` into a standby database, confirm a sign-in and a known record, and only then replace the live database during a window when visitors are told the platform is unavailable.

Downloads, deletions, checks, and queued runs are written to the audit log and to `storage/logs/backups.log`. The log records the archive name and the run id. It does not record the archive password or the dump contents.
