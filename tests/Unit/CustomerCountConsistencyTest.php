<?php

namespace Tests\Unit;

use Tests\TestCase;

/**
 * Test to verify that the customer count logic is consistent between
 * dashboard and customer list page.
 * 
 * This test verifies the fix for: "Admin dashboard shows 2 customer but /panel/admin/customers shows 1 customer"
 */
class CustomerCountConsistencyTest extends TestCase
{
    /**
     * Test that both dashboard and customer list use is_subscriber filter.
     * 
     * This test verifies that the dashboard's "new_customers_today" query
     * and the customer list page's customer query both use the same criteria:
     * filtering by is_subscriber = true instead of role = 'customer'.
     */
    public function test_dashboard_uses_is_subscriber_filter_for_new_customers_today(): void
    {
        // Read the ISPController file
        $controllerPath = base_path('app/Http/Controllers/Panel/ISPController.php');
        $this->assertFileExists($controllerPath, 'ISPController.php should exist');
        
        $controllerContent = file_get_contents($controllerPath);
        
        // Check that new_customers_today uses is_subscriber = true
        $this->assertStringContainsString(
            "'new_customers_today' => User::whereDate('created_at', today())\n                ->where('is_subscriber', true)",
            $controllerContent,
            "Dashboard should use 'is_subscriber = true' filter for new_customers_today"
        );
        
        // Ensure it's NOT using the role-based filter anymore
        $this->assertStringNotContainsString(
            "new_customers_today' => User::whereDate('created_at', today())\n                ->whereHas('roles', function (\$query) {\n                    \$query->where('slug', 'customer');",
            $controllerContent,
            "Dashboard should NOT use role-based filter for new_customers_today"
        );
    }
    
    /**
     * Test that CustomerCacheService uses is_subscriber filter.
     */
    public function test_customer_cache_service_uses_is_subscriber_filter(): void
    {
        // Read the CustomerCacheService file
        $servicePath = base_path('app/Services/CustomerCacheService.php');
        $this->assertFileExists($servicePath, 'CustomerCacheService.php should exist');
        
        $serviceContent = file_get_contents($servicePath);
        
        // Check that fetchCustomers uses is_subscriber = true
        $this->assertStringContainsString(
            "->where('is_subscriber', true) // Customers only",
            $serviceContent,
            "CustomerCacheService should use 'is_subscriber = true' filter"
        );
    }
    
    /**
     * Test that all customer-related stats in dashboard use is_subscriber filter consistently.
     */
    public function test_all_dashboard_customer_stats_use_is_subscriber_filter(): void
    {
        // Read the ISPController file
        $controllerPath = base_path('app/Http/Controllers/Panel/ISPController.php');
        $controllerContent = file_get_contents($controllerPath);
        
        // Check that various customer statistics use is_subscriber
        $statsToCheck = [
            'total_network_users',
            'expiring_today',
            'suspended_customers',
            'pppoe_customers',
            'hotspot_customers',
            'new_customers_today'
        ];
        
        foreach ($statsToCheck as $stat) {
            // Find the stat definition
            $pattern = "/'{$stat}'.*?where\('is_subscriber',\s*true\)/s";
            $this->assertMatchesRegularExpression(
                $pattern,
                $controllerContent,
                "Dashboard stat '{$stat}' should use 'is_subscriber = true' filter"
            );
        }
    }
}
