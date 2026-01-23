<?php

namespace Database\Factories;

use App\Models\Role;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Role>
 */
class RoleFactory extends Factory
{
    protected $model = Role::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $name = $this->faker->unique()->randomElement([
            'super-admin',
            'admin',
            'manager',
            'accountant',
            'staff',
            'operator',
            'sub-operator',
            'customer',
            'developer',
        ]);

        return [
            'name' => $name,
            'slug' => $name,
            'level' => $this->faker->numberBetween(10, 100),
        ];
    }

    /**
     * Configure the factory.
     */
    public function configure(): self
    {
        return $this->afterMaking(function (Role $role) {
            // Ensure slug matches name if they differ
            // This handles the case where tests override 'name' but not 'slug'
            if ($role->name !== $role->slug) {
                $role->slug = $role->name;
            }
        });
    }
}
