<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\PackageFup;
use App\Models\User;
use App\Notifications\FupExceededNotification;
use App\Notifications\FupWarningNotification;
use App\Notifications\FupResetNotification;
use Illuminate\Support\Facades\Log;

class FupService
{
    public function __construct(
        protected ?MikroTikService $mikrotikService = null
    ) {}

    /**
     * Check if user has exceeded FUP limits.
     */
    public function checkFupStatus(User $user): array
    {
        $package = $user->package;
        
        if (!$package || !$package->fup) {
            return [
                'has_fup' => false,
                'exceeded' => false,
                'should_notify' => false,
            ];
        }

        $fup = $package->fup;
        $usage = $this->getUserUsage($user);

        $exceeded = $fup->isExceeded($usage['bytes'], $usage['minutes']);
        $shouldNotify = $fup->shouldNotify($usage['bytes'], $usage['minutes']);

        return [
            'has_fup' => true,
            'exceeded' => $exceeded,
            'should_notify' => $shouldNotify,
            'fup' => $fup,
            'usage' => $usage,
            'data_percent' => $fup->getDataUsagePercent($usage['bytes']),
            'time_percent' => $fup->getTimeUsagePercent($usage['minutes']),
        ];
    }

    /**
     * Get user's current usage.
     */
    protected function getUserUsage(User $user): array
    {
        // This would integrate with actual RADIUS accounting data
        // For now, returning mock data structure
        return [
            'bytes' => 0, // Get from RADIUS acct_input_octets + acct_output_octets
            'minutes' => 0, // Get from RADIUS acct_session_time / 60
        ];
    }

    /**
     * Enforce FUP by reducing speed on router.
     */
    public function enforceFup(User $user): bool
    {
        $status = $this->checkFupStatus($user);

        if (!$status['has_fup'] || !$status['exceeded']) {
            return false;
        }

        $fup = $status['fup'];
        
        if (!$fup->reduced_speed) {
            // If no reduced speed specified, just log
            Log::info("FUP exceeded for user {$user->id} but no reduced speed configured");
            return false;
        }

        // Apply reduced speed to router
        try {
            if ($this->mikrotikService && $user->router) {
                // Parse reduced speed (e.g., "512K/512K")
                $speeds = explode('/', $fup->reduced_speed);
                $downloadSpeed = $speeds[0] ?? $fup->reduced_speed;
                $uploadSpeed = $speeds[1] ?? $downloadSpeed;
                
                // Change customer speed profile on MikroTik
                $this->mikrotikService->changeCustomerSpeed(
                    $user->router,
                    $user,
                    $downloadSpeed,
                    $uploadSpeed
                );
                
                Log::info("Applied FUP speed limit for user {$user->id}: {$fup->reduced_speed}");
                
                // Mark user as FUP limited
                $user->update([
                    'fup_exceeded' => true,
                    'fup_exceeded_at' => now(),
                ]);
                
                return true;
            }
            
            Log::warning("Cannot enforce FUP for user {$user->id}: MikroTik service not available or no router assigned");
            return false;
        } catch (\Exception $e) {
            Log::error("Failed to enforce FUP for user {$user->id}", [
                'error' => $e->getMessage(),
            ]);
            return false;
        }
    }

    /**
     * Send notification to customer about FUP.
     */
    public function sendFupNotification(User $user): void
    {
        $status = $this->checkFupStatus($user);

        if (!$status['should_notify']) {
            return;
        }

        try {
            $fup = $status['fup'];
            $usage = $status['usage'];
            
            // Determine if this is a warning or exceeded notification
            if ($status['exceeded']) {
                // Send exceeded notification
                $user->notify(new FupExceededNotification(
                    $fup,
                    $usage['bytes'],
                    $usage['minutes'],
                    $status['data_percent'],
                    $status['time_percent']
                ));
                
                Log::info("FUP exceeded notification sent to user {$user->id}");
            } else {
                // Send warning notification (near limit)
                $user->notify(new FupWarningNotification(
                    $fup,
                    $usage['bytes'],
                    $usage['minutes'],
                    $status['data_percent'],
                    $status['time_percent']
                ));
                
                Log::info("FUP warning notification sent to user {$user->id}");
            }
        } catch (\Exception $e) {
            Log::error("Failed to send FUP notification to user {$user->id}", [
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Reset FUP usage based on reset period.
     */
    public function resetFupUsage(PackageFup $fup): void
    {
        // This would be called by a scheduled job
        // Reset logic depends on the reset_period (daily, weekly, monthly)
        
        Log::info("Resetting FUP usage for package {$fup->package_id}");
        
        try {
            // Get all customers with this package who have FUP limits applied
            $affectedCustomers = User::where('package_id', $fup->package_id)
                ->where('fup_exceeded', true)
                ->get();
            
            foreach ($affectedCustomers as $customer) {
                // Restore normal speed on router
                if ($this->mikrotikService && $customer->router && $customer->package) {
                    $package = $customer->package;
                    
                    // Restore original package speed
                    $this->mikrotikService->changeCustomerSpeed(
                        $customer->router,
                        $customer,
                        $package->download_speed,
                        $package->upload_speed
                    );
                }
                
                // Clear FUP exceeded flag
                $customer->update([
                    'fup_exceeded' => false,
                    'fup_exceeded_at' => null,
                    'fup_reset_at' => now(),
                ]);
                
                // Send reset notification to customer
                $customer->notify(new FupResetNotification($fup));
                
                Log::info("FUP reset for user {$customer->id}");
            }
            
            Log::info("FUP reset completed for package {$fup->package_id}: {$affectedCustomers->count()} customers affected");
        } catch (\Exception $e) {
            Log::error("Failed to reset FUP for package {$fup->package_id}", [
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Get FUP statistics for a package.
     */
    public function getPackageFupStats(int $packageId): array
    {
        // Get statistics about how many customers are affected by FUP
        return [
            'total_customers' => 0,
            'exceeded_customers' => 0,
            'near_limit_customers' => 0,
        ];
    }
}
