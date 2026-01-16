<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Contracts\RadiusServiceInterface;
use App\Models\NetworkUser;
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
            // Try to find user by ID or username
            $user = is_numeric($userIdentifier)
                ? NetworkUser::findOrFail($userIdentifier)
                : NetworkUser::where('username', $userIdentifier)->firstOrFail();

            $success = $radiusService->syncUser($user, $password);

            if (! $success) {
                $this->error('Failed to sync user to RADIUS');

                return Command::FAILURE;
            }

            $this->info("✓ User '{$user->username}' synced successfully");
            $this->info("  - Service Type: {$user->service_type}");
            $this->info("  - Status: {$user->status}");
            if ($user->package) {
                $this->info("  - Package: {$user->package->name}");
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
