<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class RadiusInstall extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'radius:install
                            {--force : Force installation even if tables already exist}
                            {--check : Check if RADIUS is properly configured}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Install RADIUS database tables (radcheck, radreply, radacct)';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        if ($this->option('check')) {
            return $this->checkRadiusConfiguration();
        }

        $this->info('Installing RADIUS database tables...');
        $this->newLine();

        // Check if RADIUS connection is configured
        if (! $this->checkRadiusConnection()) {
            $this->error('RADIUS database connection is not configured properly.');
            $this->newLine();
            $this->warn('Please configure the following environment variables in your .env file:');
            $this->line('  RADIUS_DB_CONNECTION=mysql');
            $this->line('  RADIUS_DB_HOST=127.0.0.1');
            $this->line('  RADIUS_DB_PORT=3306');
            $this->line('  RADIUS_DB_DATABASE=radius');
            $this->line('  RADIUS_DB_USERNAME=radius');
            $this->line('  RADIUS_DB_PASSWORD=your_password');
            $this->newLine();
            $this->info('See RADIUS_SETUP_GUIDE.md for detailed setup instructions.');

            return self::FAILURE;
        }

        $this->info('✓ RADIUS database connection configured');

        // Check if RADIUS database exists
        if (! $this->checkRadiusDatabase()) {
            $this->error('RADIUS database does not exist.');
            $this->newLine();
            $this->warn('Please create the RADIUS database first:');
            $this->line('  mysql -u root -p');
            $this->line('  CREATE DATABASE <database_name> CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;');
            $this->line('  GRANT ALL PRIVILEGES ON <database_name>.* TO \'<username>\'@\'<host>\';');
            $this->line('  FLUSH PRIVILEGES;');
            $this->newLine();
            $this->info('Replace <database_name>, <username>, and <host> with your actual values from .env');
            $this->newLine();

            return self::FAILURE;
        }

        $this->info('✓ RADIUS database exists');

        // Check if tables already exist
        if (! $this->option('force') && $this->tablesExist()) {
            $this->warn('RADIUS tables already exist.');
            $this->newLine();
            $this->info('Use --force option to reinstall tables (this will drop existing tables).');

            return self::SUCCESS;
        }

        // Run RADIUS migrations
        $this->info('Running RADIUS migrations...');
        $this->newLine();

        $exitCode = $this->call('migrate', [
            '--database' => 'radius',
            '--path' => 'database/migrations/radius',
            '--force' => true,
        ]);

        if ($exitCode === 0) {
            $this->newLine();
            $this->info('✓ RADIUS tables installed successfully!');
            $this->newLine();
            $this->info('The following tables have been created:');
            $this->line('  - radcheck (authentication credentials)');
            $this->line('  - radreply (reply attributes)');
            $this->line('  - radacct (accounting/session data)');
            $this->newLine();

            return self::SUCCESS;
        }

        $this->error('Failed to install RADIUS tables.');

        return self::FAILURE;
    }

    /**
     * Check if RADIUS connection is configured.
     */
    protected function checkRadiusConnection(): bool
    {
        try {
            DB::connection('radius')->getPdo();

            return true;
        } catch (\PDOException|\Illuminate\Database\QueryException $e) {
            return false;
        }
    }

    /**
     * Check if RADIUS database exists.
     */
    protected function checkRadiusDatabase(): bool
    {
        try {
            DB::connection('radius')->select('SELECT 1');

            return true;
        } catch (\PDOException|\Illuminate\Database\QueryException $e) {
            return false;
        }
    }

    /**
     * Check if RADIUS tables exist.
     */
    protected function tablesExist(): bool
    {
        try {
            return Schema::connection('radius')->hasTable('radcheck')
                && Schema::connection('radius')->hasTable('radreply')
                && Schema::connection('radius')->hasTable('radacct');
        } catch (\PDOException|\Illuminate\Database\QueryException $e) {
            return false;
        }
    }

    /**
     * Check RADIUS configuration status.
     */
    protected function checkRadiusConfiguration(): int
    {
        $this->info('Checking RADIUS configuration...');
        $this->newLine();

        // Check connection
        $connectionOk = $this->checkRadiusConnection();
        $this->line('Database Connection: '.($connectionOk ? '✓ OK' : '✗ FAILED'));

        if (! $connectionOk) {
            $this->newLine();
            $this->error('RADIUS database connection failed.');
            $this->warn('Please check your .env configuration and ensure the database server is running.');

            return self::FAILURE;
        }

        // Check database
        $databaseOk = $this->checkRadiusDatabase();
        $this->line('Database Exists: '.($databaseOk ? '✓ OK' : '✗ FAILED'));

        if (! $databaseOk) {
            $this->newLine();
            $this->error('RADIUS database does not exist.');
            $this->warn('Run "php artisan radius:install" to create the database tables.');

            return self::FAILURE;
        }

        // Check tables
        $tablesOk = $this->tablesExist();
        $this->line('Tables Exist: '.($tablesOk ? '✓ OK' : '✗ FAILED'));

        if (! $tablesOk) {
            $this->newLine();
            $this->warn('RADIUS tables are not installed.');
            $this->info('Run "php artisan radius:install" to create the tables.');

            return self::FAILURE;
        }

        // Check table structure
        try {
            $radcheckCount = DB::connection('radius')->table('radcheck')->count();
            $radreplyCount = DB::connection('radius')->table('radreply')->count();
            $radacctCount = DB::connection('radius')->table('radacct')->count();

            $this->newLine();
            $this->info('✓ RADIUS is properly configured!');
            $this->newLine();
            $this->info('Table Statistics:');
            $this->line("  radcheck: {$radcheckCount} records");
            $this->line("  radreply: {$radreplyCount} records");
            $this->line("  radacct: {$radacctCount} records");

            return self::SUCCESS;
        } catch (\PDOException|\Illuminate\Database\QueryException $e) {
            $this->newLine();
            $this->error('Error querying RADIUS tables: '.$e->getMessage());

            return self::FAILURE;
        }
    }
}
