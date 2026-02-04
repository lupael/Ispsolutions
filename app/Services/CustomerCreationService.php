<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;

class CustomerCreationService
{
    public function createCustomer(array $data): User
    {
        try {
            DB::beginTransaction();

            // Create customer user with network credentials
            $customer = User::create([
                'tenant_id' => $data['tenant_id'],
                'name' => $data['name'],
                'email' => $data['email'],
                'username' => $data['username'],
                'password' => Hash::make($data['password']), // Hashed for app login
                'radius_password' => $data['password'], // Plain text for RADIUS
                'phone' => $data['phone'] ?? null,
                'address' => $data['address'] ?? null,
                'operator_level' => User::OPERATOR_LEVEL_CUSTOMER,
                'is_active' => $data['is_active'] ?? true,
                'is_subscriber' => true, // Mark as subscriber for customer list filtering
                'activated_at' => now(),
                'created_by' => $data['created_by'],
                'service_package_id' => $data['service_package_id'],
                // Network service fields
                'service_type' => $data['service_type'],
                'status' => $data['status'],
                'ip_address' => $data['ip_address'] ?? null,
                'mac_address' => $data['mac_address'] ?? null,
                'zone_id' => $data['zone_id'] ?? null,
                'city' => $data['city'] ?? null,
                'state' => $data['state'] ?? null,
                'postal_code' => $data['postal_code'] ?? null,
                'country' => $data['country'] ?? null,
            ]);

            // Assign customer role
            $customer->assignRole('customer');

            // Note: RADIUS provisioning now happens automatically via UserObserver
            // The observer will sync customer to RADIUS when created

            DB::commit();

            // Clear customer cache to ensure new customer appears immediately
            if (class_exists('\App\Services\CustomerCacheService')) {
                \Cache::tags(['customers'])->flush();
            }

            return $customer;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to create customer: ' . $e->getMessage());
            throw $e;
        }
    }
}
