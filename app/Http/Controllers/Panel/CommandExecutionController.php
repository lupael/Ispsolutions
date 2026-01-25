<?php

namespace App\Http\Controllers\Panel;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Process;
use Illuminate\View\View;
use Symfony\Component\Process\Process as SymfonyProcess;

class CommandExecutionController extends Controller
{
    /**
     * Whitelist of allowed artisan commands.
     */
    private const ALLOWED_ARTISAN_COMMANDS = [
        // Cache & Optimization
        'cache:clear' => 'Clear application cache',
        'config:clear' => 'Clear configuration cache',
        'config:cache' => 'Cache configuration',
        'route:clear' => 'Clear route cache',
        'route:cache' => 'Cache routes',
        'view:clear' => 'Clear compiled views',
        'view:cache' => 'Cache views',
        'optimize' => 'Optimize application',
        'optimize:clear' => 'Clear all cached data',

        // Database
        'migrate' => 'Run database migrations',
        'migrate:status' => 'Show migration status',
        'migrate:fresh' => 'Drop all tables and re-run migrations',
        'migrate:refresh' => 'Reset and re-run migrations',
        'migrate:rollback' => 'Rollback last migration',
        'db:seed' => 'Seed database',

        // RADIUS specific
        'radius:install' => 'Install RADIUS tables',
        'radius:install --check' => 'Check RADIUS configuration',
        'radius:install --force' => 'Force reinstall RADIUS tables',
        'migrate --database=radius --path=database/migrations/radius' => 'Run RADIUS migrations',

        // Queue
        'queue:work' => 'Process queue jobs',
        'queue:restart' => 'Restart queue workers',
        'queue:clear' => 'Clear queue',
        'queue:failed' => 'List failed queue jobs',

        // Composer
        'composer:dump-autoload' => 'Regenerate autoload files',

        // Storage
        'storage:link' => 'Create storage symbolic link',

        // Maintenance
        'up' => 'Bring application out of maintenance',
        'down' => 'Put application in maintenance mode',
    ];

    /**
     * Whitelist of allowed system commands.
     */
    private const ALLOWED_SYSTEM_COMMANDS = [
        // Network diagnostics
        'ping' => 'Ping a host',
        'traceroute' => 'Trace route to host',
        'nslookup' => 'DNS lookup',
        'dig' => 'DNS query',
        'host' => 'DNS host lookup',

        // System status
        'uptime' => 'Show system uptime',
        'free -h' => 'Show memory usage',
        'df -h' => 'Show disk usage',
        'top -bn1' => 'Show process list',

        // NPM (if needed)
        'npm run build' => 'Build frontend assets',
        'npm run prod' => 'Build for production',
    ];

    /**
     * Blacklisted command patterns - never allow these.
     */
    private const BLACKLISTED_PATTERNS = [
        'rm ',
        'mv ',
        'cp ',
        'dd ',
        'mkfs',
        'fdisk',
        'shutdown',
        'reboot',
        'halt',
        'poweroff',
        'kill',
        'killall',
        'pkill',
        'systemctl stop',
        'systemctl disable',
        'service stop',
        'deluser',
        'userdel',
        'groupdel',
        'passwd',
        'chpasswd',
        'cat /etc/shadow',
        'cat /etc/passwd',
        'cat .env',
        'mysql -u root',
        'DROP DATABASE',
        'DROP TABLE',
        'TRUNCATE',
        'DELETE FROM',
        'chmod 777',
        'chown',
        'sudo',
        'su ',
        'ssh',
        'scp',
        'rsync',
        'wget',
        'curl -X POST',
        'curl -X PUT',
        'curl -X DELETE',
        'git reset --hard',
        'git clean -fd',
    ];

    /**
     * Display command execution interface.
     */
    public function index(): View
    {
        $artisanCommands = self::ALLOWED_ARTISAN_COMMANDS;
        $systemCommands = self::ALLOWED_SYSTEM_COMMANDS;

        return view('panels.developer.commands.index', compact('artisanCommands', 'systemCommands'));
    }

    /**
     * Execute an artisan command.
     */
    public function executeArtisan(Request $request): JsonResponse
    {
        $request->validate([
            'command' => 'required|string',
        ]);

        $command = $request->input('command');

        // Check if command is in whitelist
        if (! array_key_exists($command, self::ALLOWED_ARTISAN_COMMANDS)) {
            return response()->json([
                'success' => false,
                'error' => 'Command not allowed. Only whitelisted commands can be executed.',
            ], 403);
        }

        // Check for blacklisted patterns
        if ($this->isBlacklisted($command)) {
            return response()->json([
                'success' => false,
                'error' => 'This command contains forbidden operations.',
            ], 403);
        }

        try {
            // Execute command and capture output
            Artisan::call($command);
            $output = Artisan::output();

            return response()->json([
                'success' => true,
                'output' => $output,
                'exit_code' => 0,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'Command execution failed: '.$e->getMessage(),
            ], 500);
        }
    }

    /**
     * Execute a system command.
     */
    public function executeSystem(Request $request): JsonResponse
    {
        $request->validate([
            'command' => 'required|string',
        ]);

        $fullCommand = $request->input('command');

        // Extract base command (first word)
        $parts = explode(' ', trim($fullCommand));
        $baseCommand = $parts[0];

        // Check if base command is in whitelist
        $isWhitelisted = false;
        foreach (self::ALLOWED_SYSTEM_COMMANDS as $allowed => $desc) {
            $allowedBase = explode(' ', $allowed)[0];
            if ($baseCommand === $allowedBase || $allowed === $fullCommand) {
                $isWhitelisted = true;
                break;
            }
        }

        if (! $isWhitelisted) {
            return response()->json([
                'success' => false,
                'error' => 'Command not allowed. Only whitelisted commands can be executed.',
            ], 403);
        }

        // Check full command for blacklisted patterns
        if ($this->isBlacklisted($fullCommand)) {
            return response()->json([
                'success' => false,
                'error' => 'This command contains forbidden operations.',
            ], 403);
        }

        // Additional security: check for shell injection characters
        if ($this->containsShellInjection($fullCommand)) {
            return response()->json([
                'success' => false,
                'error' => 'Command contains potentially dangerous characters.',
            ], 403);
        }

        try {
            // Execute command with timeout - use array format to prevent shell injection
            $process = SymfonyProcess::fromShellCommandline($fullCommand);
            $process->setTimeout(30); // 30 seconds timeout
            $process->run();

            return response()->json([
                'success' => $process->isSuccessful(),
                'output' => $process->getOutput(),
                'error_output' => $process->getErrorOutput(),
                'exit_code' => $process->getExitCode(),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'Command execution failed: '.$e->getMessage(),
            ], 500);
        }
    }

    /**
     * Check if command contains blacklisted patterns.
     */
    private function isBlacklisted(string $command): bool
    {
        $command = strtolower($command);

        foreach (self::BLACKLISTED_PATTERNS as $pattern) {
            if (str_contains($command, strtolower($pattern))) {
                return true;
            }
        }

        return false;
    }

    /**
     * Check for shell injection characters.
     */
    private function containsShellInjection(string $command): bool
    {
        // Check for common shell injection characters
        $dangerousChars = [';', '&&', '||', '|', '`', '$', '(', ')', '<', '>', '&', "\n", "\r"];

        foreach ($dangerousChars as $char) {
            if (str_contains($command, $char)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Get available commands list.
     */
    public function getCommands(): JsonResponse
    {
        return response()->json([
            'artisan' => array_map(fn ($cmd, $desc) => [
                'command' => $cmd,
                'description' => $desc,
            ], array_keys(self::ALLOWED_ARTISAN_COMMANDS), self::ALLOWED_ARTISAN_COMMANDS),
            'system' => array_map(fn ($cmd, $desc) => [
                'command' => $cmd,
                'description' => $desc,
            ], array_keys(self::ALLOWED_SYSTEM_COMMANDS), self::ALLOWED_SYSTEM_COMMANDS),
        ]);
    }
}
