<?php

namespace Database\Factories;

use App\Models\HotspotUser;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;

class HotspotUserFactory extends Factory
{
    protected $model = HotspotUser::class;

    public function definition(): array
    {
        return [
            'tenant_id' => 1,
            'phone_number' => $this->faker->unique()->phoneNumber,
            'username' => $this->faker->unique()->userName,
            'password' => Hash::make('password'),
            'is_verified' => true,
            'verified_at' => now(),
            'status' => 'active',
            'expires_at' => now()->addDays(30),
            'mac_address' => $this->faker->macAddress,
        ];
    }
}