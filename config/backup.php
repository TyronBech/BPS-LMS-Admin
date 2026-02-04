<?php

return [

    'backup' => [
        /*
         * The name of this application. You can use this name to monitor
         * the backups.
         */
        'name' => env('APP_NAME', 'laravel-backup'),

        'dump' => [
            'mysql' => [
                'dump_binary_path' => env('MYSQL_DUMP_PATH'),
            ],
        ],

        'source' => [
            'files' => [
                'include' => [
                    storage_path('logs'),
                ],

                'exclude' => [
                    base_path('vendor'),
                    base_path('node_modules'),
                ],

                'follow_links' => false,
                'ignore_unreadable_directories' => false,
                'relative_path' => null,
            ],

            'databases' => [
                env('DB_CONNECTION', 'mysql'),
            ],
        ],

        'database_dump_file_extension' => '',

        'temporary_directory' => storage_path('app/backup-temp'),

        'destination' => [
            'filename_prefix' => '',
            'disks' => ['backups'],
        ],
    ],

    'notifications' => [
        /*
         * Notifications are disabled here because we use a custom listener
         * (SendBackupSucceededNotification) to handle backup notifications
         * with our own email template design.
         */
        'notifications' => [
            \Spatie\Backup\Events\BackupHasFailed::class => [],
            \Spatie\Backup\Events\UnhealthyBackupWasFound::class => [],
            \Spatie\Backup\Events\CleanupHasFailed::class => [],
            \Spatie\Backup\Events\BackupWasSuccessful::class => [],
            \Spatie\Backup\Events\HealthyBackupWasFound::class => [],
            \Spatie\Backup\Events\CleanupWasSuccessful::class => [],
        ],
        'notifiable' => \Spatie\Backup\Notifications\Notifiable::class,

        'mail' => [
            'to' => env('MAIL_TO_ADDRESS', 'hello@example.com'),
            'from' => [
                'address' => env('MAIL_FROM_ADDRESS', 'hello@example.com'),
                'name' => env('MAIL_FROM_NAME', 'Example'),
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
            'name' => env('APP_NAME', 'laravel-backup'),
            'disks' => ['backups'], // monitor the same disk
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
            'keep_daily_backups_for_days' => 0,
            'keep_weekly_backups_for_weeks' => 0,
            'keep_monthly_backups_for_months' => 0,
            'keep_yearly_backups_for_years' => 0,
            'delete_oldest_backups_when_using_more_megabytes_than' => 5000,
        ],

        'tries' => 1,
        'retry_delay' => 0,
    ],

];
