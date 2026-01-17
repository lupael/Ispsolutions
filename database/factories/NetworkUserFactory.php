<?php

namespace Database\Factories;

use App\Models\NetworkUser;
use App\Models\Package;
use App\Models\Tenant;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\NetworkUser>
 */
class NetworkUserFactory extends Factory
{
    protected $model = NetworkUser::class;

    public function definition(): array
    {
        return [
            'username' => fake()->unique()->userName(),
            'password' => fake()->password(),
            'service_type' => fake()->randomElement(['pppoe', 'hotspot', 'static']),
            'package_id' => null,
            'status' => 'active',
            'user_id' => null,
            'tenant_id' => null,
        ];
    }

    public function active(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'active',
        ]);
    }

    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'inactive',
        ]);
    }

    public function suspended(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'suspended',
        ]);
    }

    public function pppoe(): static
    {
        return $this->state(fn (array $attributes) => [
            'service_type' => 'pppoe',
        ]);
    }

    public function hotspot(): static
    {
        return $this->state(fn (array $attributes) => [
            'service_type' => 'hotspot',
        ]);
    }

    public function staticService(): static
    {
        return $this->state(fn (array $attributes) => [
            'service_type' => 'static',
        ]);
    }

    public function withPackage(): static
    {
        return $this->state(fn (array $attributes) => [
            'package_id' => Package::factory(),
        ]);
    }

    public function withTenant(): static
    {
        return $this->state(fn (array $attributes) => [
            'tenant_id' => Tenant::factory(),
        ]);
    }
}
