<?php

namespace Database\Seeders;

use App\Models\Tenant;
use App\Models\VatProfile;
use Illuminate\Database\Seeder;

class VatProfileSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get all tenants or create for first tenant if none exist
        $tenants = Tenant::all();
        
        // If no tenants exist, create without tenant_id (for single-tenant setups)
        if ($tenants->isEmpty()) {
            $this->createVatProfiles(null);
            return;
        }
        
        // Create VAT profiles for each tenant
        foreach ($tenants as $tenant) {
            $this->createVatProfiles($tenant->id);
        }
    }
    
    /**
     * Create VAT profiles for a tenant
     */
    private function createVatProfiles(?int $tenantId): void
    {
        // Standard VAT rate (common in many countries)
        VatProfile::create([
            'tenant_id' => $tenantId,
            'name' => 'Standard VAT',
            'rate' => 15.00,
            'description' => 'Standard VAT rate applied to most goods and services',
            'is_default' => true,
            'is_active' => true,
        ]);

        // Reduced VAT rate
        VatProfile::create([
            'tenant_id' => $tenantId,
            'name' => 'Reduced VAT',
            'rate' => 5.00,
            'description' => 'Reduced VAT rate for specific goods or services',
            'is_default' => false,
            'is_active' => true,
        ]);

        // Zero VAT rate
        VatProfile::create([
            'tenant_id' => $tenantId,
            'name' => 'Zero VAT',
            'rate' => 0.00,
            'description' => 'Zero-rated VAT for exempt goods or services',
            'is_default' => false,
            'is_active' => true,
        ]);

        // High VAT rate (luxury items)
        VatProfile::create([
            'tenant_id' => $tenantId,
            'name' => 'High VAT',
            'rate' => 25.00,
            'description' => 'High VAT rate for luxury or premium services',
            'is_default' => false,
            'is_active' => false,
        ]);
    }
}
