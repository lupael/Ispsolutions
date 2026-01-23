<?php

namespace Database\Factories;

use App\Models\Package;
use App\Models\Tenant;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Package>
 */
class PackageFactory extends Factory
{
    protected $model = Package::class;

    public function definition(): array
    {
        return [
            'tenant_id' => Tenant::factory(),
            'name' => $this->faker->unique()->words(2, true) . ' Package',
            'description' => $this->faker->sentence(),
            'billing_type' => $this->faker->randomElement(['monthly', 'daily', 'prepaid']),
            'price' => $this->faker->randomFloat(2, 100, 5000),
            'validity_days' => $this->faker->randomElement([1, 7, 15, 30, 90, 365]),
            'bandwidth_up' => $this->faker->randomElement([512, 1024, 2048, 5120, 10240]),
            'bandwidth_down' => $this->faker->randomElement([1024, 2048, 5120, 10240, 20480]),
            'is_active' => true,
        ];
    }

    public function monthly(): static
    {
        return $this->state(fn (array $attributes) => [
            'billing_type' => 'monthly',
            'validity_days' => 30,
        ]);
    }

    public function daily(): static
    {
        return $this->state(fn (array $attributes) => [
            'billing_type' => 'daily',
            'validity_days' => 1,
        ]);
    }

    public function prepaid(): static
    {
        return $this->state(fn (array $attributes) => [
            'billing_type' => 'prepaid',
        ]);
    }

    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => false,
        ]);
    }
}
