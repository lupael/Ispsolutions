<?php

namespace Database\Seeders;

use App\Models\SubscriptionPlan;
use Illuminate\Database\Seeder;

class SubscriptionPlanSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $plans = [
            [
                'name' => 'Starter',
                'slug' => 'starter',
                'description' => 'Perfect for small ISPs getting started',
                'price' => 999.00,
                'currency' => 'BDT',
                'billing_cycle' => 'monthly',
                'features' => json_encode([
                    'Up to 100 users',
                    '2 MikroTik routers',
                    '1 OLT device',
                    'Basic support',
                    'Email notifications',
                    'Basic reporting',
                ]),
                'max_users' => 100,
                'max_routers' => 2,
                'max_olts' => 1,
                'is_active' => true,
                'trial_days' => 14,
                'sort_order' => 1,
            ],
            [
                'name' => 'Professional',
                'slug' => 'professional',
                'description' => 'Ideal for growing ISP businesses',
                'price' => 2499.00,
                'currency' => 'BDT',
                'billing_cycle' => 'monthly',
                'features' => json_encode([
                    'Up to 500 users',
                    '5 MikroTik routers',
                    '3 OLT devices',
                    'Priority support',
                    'SMS notifications',
                    'Advanced reporting',
                    'Billing integration',
                    'API access',
                ]),
                'max_users' => 500,
                'max_routers' => 5,
                'max_olts' => 3,
                'is_active' => true,
                'trial_days' => 14,
                'sort_order' => 2,
            ],
            [
                'name' => 'Enterprise',
                'slug' => 'enterprise',
                'description' => 'For large-scale ISP operations',
                'price' => 4999.00,
                'currency' => 'BDT',
                'billing_cycle' => 'monthly',
                'features' => json_encode([
                    'Unlimited users',
                    'Unlimited routers',
                    'Unlimited OLT devices',
                    'Premium 24/7 support',
                    'Multi-channel notifications',
                    'Custom reporting',
                    'Full billing integration',
                    'Advanced API access',
                    'Multi-tenancy support',
                    'Custom integrations',
                ]),
                'max_users' => null, // unlimited
                'max_routers' => null, // unlimited
                'max_olts' => null, // unlimited
                'is_active' => true,
                'trial_days' => 30,
                'sort_order' => 3,
            ],
        ];

        foreach ($plans as $plan) {
            SubscriptionPlan::updateOrCreate(
                ['slug' => $plan['slug']],
                $plan
            );
        }

        $this->command->info('Subscription plans seeded successfully!');
    }
}
