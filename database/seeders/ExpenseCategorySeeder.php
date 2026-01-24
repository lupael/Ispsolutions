<?php

namespace Database\Seeders;

use App\Models\ExpenseCategory;
use App\Models\Tenant;
use Illuminate\Database\Seeder;

class ExpenseCategorySeeder extends Seeder
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
            $this->createExpenseCategories(null);
            return;
        }
        
        // Create expense categories for each tenant
        foreach ($tenants as $tenant) {
            $this->createExpenseCategories($tenant->id);
        }
    }
    
    /**
     * Create expense categories for a tenant
     */
    private function createExpenseCategories(?int $tenantId): void
    {
        // Operational Expenses
        $operational = ExpenseCategory::create([
            'tenant_id' => $tenantId,
            'name' => 'Operational Expenses',
            'description' => 'Day-to-day operational costs',
            'color' => '#3B82F6',
            'is_active' => true,
        ]);

        $operational->subcategories()->createMany([
            ['tenant_id' => $tenantId, 'name' => 'Office Rent', 'description' => 'Monthly office space rental', 'is_active' => true],
            ['tenant_id' => $tenantId, 'name' => 'Utilities', 'description' => 'Electricity, water, and other utilities', 'is_active' => true],
            ['tenant_id' => $tenantId, 'name' => 'Internet & Phone', 'description' => 'Communication expenses', 'is_active' => true],
            ['tenant_id' => $tenantId, 'name' => 'Office Supplies', 'description' => 'Stationery and supplies', 'is_active' => true],
        ]);

        // Network Infrastructure
        $network = ExpenseCategory::create([
            'tenant_id' => $tenantId,
            'name' => 'Network Infrastructure',
            'description' => 'Network equipment and maintenance',
            'color' => '#10B981',
            'is_active' => true,
        ]);

        $network->subcategories()->createMany([
            ['tenant_id' => $tenantId, 'name' => 'Equipment Purchase', 'description' => 'Routers, switches, and network hardware', 'is_active' => true],
            ['tenant_id' => $tenantId, 'name' => 'Equipment Maintenance', 'description' => 'Repair and maintenance costs', 'is_active' => true],
            ['tenant_id' => $tenantId, 'name' => 'Fiber & Cabling', 'description' => 'Fiber optic and cable installation', 'is_active' => true],
            ['tenant_id' => $tenantId, 'name' => 'Bandwidth & Transit', 'description' => 'Upstream bandwidth costs', 'is_active' => true],
        ]);

        // Personnel Expenses
        $personnel = ExpenseCategory::create([
            'tenant_id' => $tenantId,
            'name' => 'Personnel Expenses',
            'description' => 'Employee salaries and benefits',
            'color' => '#F59E0B',
            'is_active' => true,
        ]);

        $personnel->subcategories()->createMany([
            ['tenant_id' => $tenantId, 'name' => 'Salaries', 'description' => 'Employee monthly salaries', 'is_active' => true],
            ['tenant_id' => $tenantId, 'name' => 'Bonuses', 'description' => 'Performance and festival bonuses', 'is_active' => true],
            ['tenant_id' => $tenantId, 'name' => 'Benefits', 'description' => 'Health insurance and other benefits', 'is_active' => true],
            ['tenant_id' => $tenantId, 'name' => 'Training', 'description' => 'Employee training and development', 'is_active' => true],
        ]);

        // Marketing & Sales
        $marketing = ExpenseCategory::create([
            'tenant_id' => $tenantId,
            'name' => 'Marketing & Sales',
            'description' => 'Marketing and promotional expenses',
            'color' => '#EF4444',
            'is_active' => true,
        ]);

        $marketing->subcategories()->createMany([
            ['tenant_id' => $tenantId, 'name' => 'Advertising', 'description' => 'Online and offline advertising', 'is_active' => true],
            ['tenant_id' => $tenantId, 'name' => 'Promotions', 'description' => 'Customer promotional campaigns', 'is_active' => true],
            ['tenant_id' => $tenantId, 'name' => 'Sales Commissions', 'description' => 'Sales team commissions', 'is_active' => true],
        ]);

        // Administrative
        $administrative = ExpenseCategory::create([
            'tenant_id' => $tenantId,
            'name' => 'Administrative',
            'description' => 'Administrative and legal expenses',
            'color' => '#8B5CF6',
            'is_active' => true,
        ]);

        $administrative->subcategories()->createMany([
            ['tenant_id' => $tenantId, 'name' => 'Legal Fees', 'description' => 'Legal consultation and services', 'is_active' => true],
            ['tenant_id' => $tenantId, 'name' => 'Licenses & Permits', 'description' => 'Business licenses and permits', 'is_active' => true],
            ['tenant_id' => $tenantId, 'name' => 'Insurance', 'description' => 'Business insurance premiums', 'is_active' => true],
            ['tenant_id' => $tenantId, 'name' => 'Bank Fees', 'description' => 'Banking and transaction fees', 'is_active' => true],
        ]);

        // Miscellaneous
        ExpenseCategory::create([
            'tenant_id' => $tenantId,
            'name' => 'Miscellaneous',
            'description' => 'Other miscellaneous expenses',
            'color' => '#6B7280',
            'is_active' => true,
        ]);
    }
}
