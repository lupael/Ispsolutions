<?php

namespace App\Observers;

use App\Models\User;
use Illuminate\Support\Facades\Log;

/**
 * User Observer
 * 
 * Automatically provisions customers to RADIUS when they are created, updated, or deleted.
 * Only applies to users with operator_level = 100 (customers) and network service types.
 */
class UserObserver
{
    /**
     * Handle the User "created" event.
     * Automatically provision customer to RADIUS on creation.
     */
    public function created(User $user): void
    {
        // Only process customers with network service types
        if (!$user->isNetworkCustomer()) {
            return;
        }

        try {
            // Sync to RADIUS
            if ($user->username && $user->radius_password) {
                $user->syncToRadius(['password' => $user->radius_password]);
                Log::info("Customer {$user->username} provisioned to RADIUS", ['user_id' => $user->id]);
            }
        } catch (\Exception $e) {
            Log::error("Failed to provision customer to RADIUS on creation", [
                'user_id' => $user->id,
                'username' => $user->username,
                'error' => $e->getMessage(),
            ]);
            // Don't throw exception to avoid blocking customer creation
        }
    }

    /**
     * Handle the User "updated" event.
     * Sync changes to RADIUS when customer is updated.
     */
    public function updated(User $user): void
    {
        // Only process customers with network service types
        if (!$user->isNetworkCustomer()) {
            return;
        }

        try {
            // Check if network-related fields changed
            // Use wasChanged() because updated() fires after save (isDirty() would always be false)
            $networkFields = ['status', 'is_active', 'ip_address', 'mac_address', 'service_type', 'radius_password'];
            $changed = false;
            
            foreach ($networkFields as $field) {
                if ($user->wasChanged($field)) {
                    $changed = true;
                    break;
                }
            }

            if (!$changed) {
                return;
            }

            // Sync to RADIUS
            if ($user->username) {
                // If not active for RADIUS, remove from RADIUS
                if (!$user->isActiveForRadius()) {
                    $user->removeFromRadius();
                    Log::info("Customer {$user->username} removed from RADIUS (not active)", ['user_id' => $user->id]);
                } else {
                    // Update RADIUS attributes
                    $attributes = [];
                    if ($user->radius_password) {
                        $attributes['password'] = $user->radius_password;
                    }
                    if ($user->ip_address) {
                        $attributes['Framed-IP-Address'] = $user->ip_address;
                    }
                    
                    $user->syncToRadius($attributes);
                    Log::info("Customer {$user->username} synced to RADIUS", ['user_id' => $user->id]);
                }
            }
        } catch (\Exception $e) {
            Log::error("Failed to sync customer to RADIUS on update", [
                'user_id' => $user->id,
                'username' => $user->username,
                'error' => $e->getMessage(),
            ]);
            // Don't throw exception to avoid blocking customer updates
        }
    }

    /**
     * Handle the User "deleted" event.
     * Remove customer from RADIUS on deletion.
     */
    public function deleted(User $user): void
    {
        // Only process customers with network service types
        if (!$user->isNetworkCustomer()) {
            return;
        }

        try {
            if ($user->username) {
                $user->removeFromRadius();
                Log::info("Customer {$user->username} removed from RADIUS on deletion", ['user_id' => $user->id]);
            }
        } catch (\Exception $e) {
            Log::error("Failed to remove customer from RADIUS on deletion", [
                'user_id' => $user->id,
                'username' => $user->username,
                'error' => $e->getMessage(),
            ]);
            // Don't throw exception to avoid blocking customer deletion
        }
    }

    /**
     * Handle the User "restored" event.
     * Re-provision customer to RADIUS when restored from soft delete.
     */
    public function restored(User $user): void
    {
        // Only process customers with network service types
        if (!$user->isNetworkCustomer()) {
            return;
        }

        try {
            if ($user->username && $user->radius_password && $user->isActiveForRadius()) {
                $user->syncToRadius(['password' => $user->radius_password]);
                Log::info("Customer {$user->username} re-provisioned to RADIUS on restore", ['user_id' => $user->id]);
            }
        } catch (\Exception $e) {
            Log::error("Failed to re-provision customer to RADIUS on restore", [
                'user_id' => $user->id,
                'username' => $user->username,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Handle the User "force deleted" event.
     * Ensure customer is removed from RADIUS on permanent deletion.
     */
    public function forceDeleted(User $user): void
    {
        // Same as deleted event
        $this->deleted($user);
    }
}
