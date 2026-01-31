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
                // Parse reduced speed (supports "download/upload" or a single value like "512K")
                $rawSpeed = trim((string) $fup->reduced_speed);

                if ($rawSpeed === '') {
                    Log::warning("FUP exceeded for user {$user->id} but reduced speed is empty");
                    return false;
                }

                $downloadSpeed = $rawSpeed;
                $uploadSpeed = $rawSpeed;

                // Check if speed contains "/" separator
                if (strpos($rawSpeed, '/') !== false) {
                    // Limit to two parts and trim each one
                    $parts = explode('/', $rawSpeed, 2);
                    $downloadPart = isset($parts[0]) ? trim($parts[0]) : '';
                    $uploadPart = isset($parts[1]) ? trim($parts[1]) : '';

                    // If one side is missing or empty, reuse the other side
                    if ($downloadPart === '' && $uploadPart === '') {
                        Log::warning("FUP exceeded for user {$user->id} but reduced speed '{$rawSpeed}' has empty parts");
                        return false;
                    }

                    if ($downloadPart === '') {
                        $downloadPart = $uploadPart;
                    }

                    if ($uploadPart === '') {
                        $uploadPart = $downloadPart;
                    }

                    $downloadSpeed = $downloadPart;
                    $uploadSpeed = $uploadPart;
                }
                
                // Change customer speed profile on MikroTik
                // Note: This assumes MikrotikService will have a changeCustomerSpeed method
                // If the method doesn't exist, consider using updatePppoeUser instead
                if (method_exists($this->mikrotikService, 'changeCustomerSpeed')) {
                    $this->mikrotikService->changeCustomerSpeed(
                        $user->router,
                        $user,
                        $downloadSpeed,
                        $uploadSpeed
                    );
                } else {
                    // Fallback to updatePppoeUser if changeCustomerSpeed doesn't exist
                    $this->mikrotikService->updatePppoeUser($user->pppoe_username, [
                        'profile' => 'fup-limited', // Or create dynamic profile
                        'rate-limit' => "{$downloadSpeed}/{$uploadSpeed}",
                    ]);
                }
                
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
     * Reset FUP limits and restore speeds for customers
     * 
     * Note: This method clears FUP exceeded flags and restores speeds, but does NOT
     * reset actual usage counters (bytes consumed, time used). Usage counter reset
     * should be handled by the RADIUS accounting system or usage tracking service.
     * 
     * This would be called by a scheduled job based on reset_period (daily, weekly, monthly)
     */
    public function resetFupLimits(PackageFup $fup): void
    {
        Log::info("Resetting FUP limits for package {$fup->package_id}");
        
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
                    if (method_exists($this->mikrotikService, 'changeCustomerSpeed')) {
                        $this->mikrotikService->changeCustomerSpeed(
                            $customer->router,
                            $customer,
                            $package->download_speed,
                            $package->upload_speed
                        );
                    } else {
                        // Fallback to updatePppoeUser
                        $this->mikrotikService->updatePppoeUser($customer->pppoe_username, [
                            'profile' => $package->mikrotik_profile ?? 'default',
                        ]);
                    }
                }
                
                // Clear FUP exceeded flag
                $customer->update([
                    'fup_exceeded' => false,
                    'fup_exceeded_at' => null,
                    'fup_reset_at' => now(),
                ]);
                
                // Send reset notification to customer
                $customer->notify(new FupResetNotification($fup));
                
                Log::info("FUP limits reset for user {$customer->id}");
            }
            
            Log::info("FUP limit reset completed for package {$fup->package_id}: {$affectedCustomers->count()} customers affected");
        } catch (\Exception $e) {
            Log::error("Failed to reset FUP limits for package {$fup->package_id}", [
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Legacy method name - kept for backward compatibility
     * @deprecated Use resetFupLimits() instead
     */
    public function resetFupUsage(PackageFup $fup): void
    {
        $this->resetFupLimits($fup);
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
