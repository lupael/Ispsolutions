<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * SMS Payment Factory
 * 
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\SmsPayment>
 */
class SmsPaymentFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $smsQuantity = $this->faker->randomElement([100, 500, 1000, 2000, 5000, 10000]);
        $pricePerSms = 0.50; // 50 cents per SMS
        $amount = $smsQuantity * $pricePerSms;

        return [
            'operator_id' => User::factory(),
            'amount' => $amount,
            'sms_quantity' => $smsQuantity,
            'payment_method' => $this->faker->randomElement(['bkash', 'nagad', 'rocket', 'sslcommerz']),
            'transaction_id' => 'TXN' . $this->faker->unique()->numerify('##########'),
            'status' => 'pending',
            'notes' => null,
            'completed_at' => null,
        ];
    }

    /**
     * Indicate that the payment is pending
     */
    public function pending(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'pending',
            'completed_at' => null,
        ]);
    }

    /**
     * Indicate that the payment is completed
     */
    public function completed(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'completed',
            'completed_at' => now(),
        ]);
    }

    /**
     * Indicate that the payment failed
     */
    public function failed(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'failed',
            'completed_at' => null,
            'notes' => 'Payment failed: ' . $this->faker->sentence(),
        ]);
    }
}
