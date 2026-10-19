<?php

$notificationEmail = env('BACKUP_NOTIFICATION_EMAIL');
$notify = filled($notificationEmail) ? ['mail'] : [];

return [

    'backup' => [
        'name' => 'IOMP',

        'source' => [
            'files' => [
                /*
                 * Database backups do not include the application tree.
                 * This single public file keeps a mistaken full run from
                 * packing the repository, and .env is excluded either way.
                 */
                'include' => [
                    public_path('manifest.webmanifest'),
                ],
                'exclude' => [
                    base_path('.env'),
                    base_path('.env.*'),
                    base_path('vendor'),
                    base_path('node_modules'),
                    storage_path('app/backups'),
                    storage_path('app/backup-temp'),
                    storage_path('logs'),
                ],
                'follow_links' => false,
                'ignore_unreadable_directories' => true,
                'relative_path' => base_path(),
            ],

            'databases' => [
                env('DB_CONNECTION', 'pgsql'),
            ],
        ],

        'database_dump_compressor' => null,
        'database_dump_file_timestamp_format' => null,
        'database_dump_filename_base' => 'database',
        'database_dump_file_extension' => '',

        'destination' => [
            'compression_method' => ZipArchive::CM_DEFAULT,
            'compression_level' => 9,
            'filename_prefix' => 'iomp-',
            'disks' => [
                'backups',
            ],
        ],

        'temporary_directory' => storage_path('app/backup-temp'),
        'password' => env('BACKUP_ARCHIVE_PASSWORD'),
        'encryption' => filled(env('BACKUP_ARCHIVE_PASSWORD')) ? 'default' : null,
        'tries' => 1,
        'retry_delay' => 0,
    ],

    'notifications' => [
        'notifications' => [
            \Spatie\Backup\Notifications\Notifications\BackupHasFailedNotification::class => $notify,
            \Spatie\Backup\Notifications\Notifications\UnhealthyBackupWasFoundNotification::class => $notify,
            \Spatie\Backup\Notifications\Notifications\CleanupHasFailedNotification::class => $notify,
        ],
        'notifiable' => \Spatie\Backup\Notifications\Notifiable::class,
        'mail' => [
            'to' => filled($notificationEmail) ? $notificationEmail : 'hello@example.com',
            'from' => [
                'address' => env('MAIL_FROM_ADDRESS', 'hello@example.com'),
                'name' => env('MAIL_FROM_NAME', 'GIPA'),
            ],
        ],
        'slack' => [
            'webhook_url' => '',
            'channel' => null,
            'username' => null,
            'icon' => null,
        ],
        'discord' => [
            'webhook_url' => '',
            'username' => '',
            'avatar_url' => '',
        ],
    ],

    'monitor_backups' => [
        [
            'name' => 'IOMP',
            'disks' => ['backups'],
            'health_checks' => [
                \Spatie\Backup\Tasks\Monitor\HealthChecks\MaximumAgeInDays::class => 1,
                \Spatie\Backup\Tasks\Monitor\HealthChecks\MaximumStorageInMegabytes::class => 5000,
            ],
        ],
    ],

    'cleanup' => [
        'strategy' => \Spatie\Backup\Tasks\Cleanup\Strategies\DefaultStrategy::class,
        'default_strategy' => [
            'keep_all_backups_for_days' => 7,
            'keep_daily_backups_for_days' => 16,
            'keep_weekly_backups_for_weeks' => 8,
            'keep_monthly_backups_for_months' => 4,
            'keep_yearly_backups_for_years' => 2,
            'delete_oldest_backups_when_using_more_megabytes_than' => 5000,
        ],
        'tries' => 1,
        'retry_delay' => 0,
    ],

];
