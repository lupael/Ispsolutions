<?php

/**
 * Application & Database Backup Configuration
 *
 * For full backup automation (filesystem + database), install Spatie Laravel Backup:
 *   composer require spatie/laravel-backup
 *   php artisan vendor:publish --provider="Spatie\Backup\BackupServiceProvider"
 *
 * This file is a placeholder. After installing spatie/laravel-backup, replace
 * with the published config or merge these defaults.
 */
return [
    'name' => env('APP_NAME', 'isp-solution'),

    'source' => [
        'files' => [
            'include' => [base_path()],
            'exclude' => [
                base_path('vendor'),
                base_path('node_modules'),
                base_path('storage/app/backups'),
            ],
            'follow_links' => false,
        ],
        'databases' => [env('DB_CONNECTION', 'mysql')],
    ],

    'destination' => [
        'filename_prefix' => '',
        'disks' => [env('BACKUP_DISK', 'local')],
    ],

    'temporary_directory' => storage_path('app/backup-temp'),

    /*
    |--------------------------------------------------------------------------
    | Router-side backups (MikroTik) are handled by RouterBackupService
    | and RouterRadiusProvisioningService (PPP export, system backup).
    | OLT backups use vendor-specific commands (TFTP/FTP) via OltBackup.
    */
];
