<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Contracts\RadiusServiceInterface;
use App\Models\User;
use Illuminate\Console\Command;

class RadiusSyncUser extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'radius:sync-user
                            {user : The username or user ID to sync}
                            {--password= : Optional new password to set}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sync a network user to the RADIUS database';

    /**
     * Execute the console command.
     */
    public function handle(RadiusServiceInterface $radiusService): int
    {
        $userIdentifier = $this->argument('user');
        $password = $this->option('password');

        $this->info("Syncing user: {$userIdentifier}");

        try {
            // Find user by ID or username, ensuring they are a subscriber
            $user = is_numeric($userIdentifier)
                ? User::where('is_subscriber', true)->findOrFail($userIdentifier)
                : User::where('is_subscriber', true)->where('username', $userIdentifier)->firstOrFail();

            $attributes = $password ? ['password' => $password] : [];
            $success = $radiusService->syncUser($user, $attributes);

            if (! $success) {
                $this->error('Failed to sync user to RADIUS');

                return Command::FAILURE;
            }

            $this->info("✓ User '{$user->username}' synced successfully");
            $this->info("  - Service Type: {$user->service_type}");
            $this->info("  - Status: {$user->status}");
            if ($user->package) {
                /** @var \App\Models\Package $package */
                $package = $user->package;
                $this->info("  - Package: {$package->name}");
            }

            return Command::SUCCESS;
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            $this->error("User not found: {$userIdentifier}");

            return Command::FAILURE;
        } catch (\Exception $e) {
            $this->error('Sync failed: ' . $e->getMessage());

            return Command::FAILURE;
        }
    }
}
