<?php

namespace App\Services;

use App\Models\Customer;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Psr\Log\LoggerInterface;
use Illuminate\Support\Facades\Cache;

class CustomerCreationService
{
    public function __construct(private LoggerInterface $logger)
    {
    }

    /**
     * Create a new customer with their network credentials.
     *
     * @param array $data The customer data.
     * @return Customer The newly created customer model.
     * @throws \Exception If customer creation fails.
     */
    public function createCustomer(array $data): Customer
    {
        try {
            DB::beginTransaction();

            // Use the Customer model to leverage its creating() event hook.
            // This automatically sets is_subscriber, operator_level, and customer_id.
            $customer = Customer::create([
                'tenant_id' => $data['tenant_id'],
                'name' => $data['name'],
                'email' => $data['email'],
                'username' => $data['username'],
                'password' => Hash::make($data['password']), // Hashed for app login
                'radius_password' => $data['password'], // Plain text for RADIUS
                'phone' => $data['phone'] ?? null,
                'address' => $data['address'] ?? null,
                'is_active' => $data['is_active'] ?? true,
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

            // Invalidate the specific cache for this tenant for better performance.
            Cache::forget("customers:tenant:{$customer->tenant_id}");

            return $customer;
        } catch (\Exception $e) {
            DB::rollBack();
            $this->logger->error('Failed to create customer', ['message' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
            throw $e;
        }
    }
}
