<?php

namespace Database\Factories;

use App\Models\MikrotikRouter;
use Illuminate\Database\Eloquent\Factories\Factory;

class MikrotikRouterFactory extends Factory
{
    protected $model = MikrotikRouter::class;

    public function definition(): array
    {
        return [
            'name' => $this->faker->word,
            'ip_address' => $this->faker->ipv4,
            'api_port' => 8728,
            'username' => 'admin',
            'password' => 'password',
            'status' => 'active',
        ];
    }
}