<?php

namespace Database\Factories;

use App\Models\IpPool;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\IpPool>
 */
class IpPoolFactory extends Factory
{
    protected $model = IpPool::class;

    public function definition(): array
    {
        return [
            'name' => fake()->unique()->words(2, true) . ' Pool',
            'description' => fake()->sentence(),
            'pool_type' => fake()->randomElement(['public', 'private']),
            'is_active' => true,
        ];
    }

    public function publicPool(): static
    {
        return $this->state(fn (array $attributes) => [
            'pool_type' => 'public',
        ]);
    }

    public function privatePool(): static
    {
        return $this->state(fn (array $attributes) => [
            'pool_type' => 'private',
        ]);
    }
}
