<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Schedule network service commands
Schedule::command('ipam:cleanup --force')->daily()->at('00:00');
Schedule::command('radius:sync-users --force')->everyFiveMinutes();
Schedule::command('mikrotik:sync-sessions')->everyMinute();
Schedule::command('mikrotik:health-check')->everyFifteenMinutes();

// Schedule OLT service commands
Schedule::command('olt:health-check')->everyFifteenMinutes();
Schedule::command('olt:sync-onus')->hourly();
Schedule::command('olt:backup')->daily()->at('02:00');

// Schedule monitoring commands
Schedule::command('monitoring:collect')->everyFiveMinutes();
Schedule::command('monitoring:aggregate-hourly')->hourly();
Schedule::command('monitoring:aggregate-daily')->daily()->at('01:00');
Schedule::command('monitoring:cleanup --days=90')->daily()->at('03:00');

// Schedule billing commands
Schedule::command('billing:generate-daily --force')->daily()->at('00:30');
Schedule::command('billing:generate-monthly --force')->monthlyOn(1, '01:00');
Schedule::command('billing:generate-static-ip --force')->monthlyOn(1, '01:15');
Schedule::command('billing:lock-expired --force')->daily()->at('04:00');

// Schedule commission commands
Schedule::command('commission:pay-pending --threshold=100 --force')->weekly()->mondays()->at('09:00');

// Schedule notification commands
Schedule::command('notifications:pre-expiration --days=3 --force')->daily()->at('08:00');
Schedule::command('notifications:pre-expiration --days=7 --force')->daily()->at('08:15');
Schedule::command('notifications:overdue --force')->daily()->at('09:00');

// Schedule hotspot commands
Schedule::command('hotspot:deactivate-expired --force')->daily()->at('00:45');

// Schedule bandwidth collection
Schedule::job(new \App\Jobs\CollectBandwidthDataJob)->everyFiveMinutes();

// Schedule temp customer cleanup
Schedule::job(new \App\Jobs\CleanupExpiredTempCustomersJob)->daily()->at('02:30');

// Schedule auto-debit processing
Schedule::command('auto-debit:process')->daily()->at('05:00');

// Schedule subscription billing
Schedule::command('subscription:generate-bills')->monthlyOn(1, '00:30');
