<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\AutoDebitHistory;
use App\Models\SubscriptionBill;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<AutoDebitHistory>
 */
class AutoDebitHistoryFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var class-string<AutoDebitHistory>
     */
    protected $model = AutoDebitHistory::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $status = $this->faker->randomElement(['pending', 'success', 'failed']);
        
        return [
            'customer_id' => User::factory(),
            'bill_id' => null,
            'amount' => $this->faker->randomFloat(2, 100, 5000),
            'status' => $status,
            'failure_reason' => $status === 'failed' ? $this->faker->sentence() : null,
            'retry_count' => $this->faker->numberBetween(0, 3),
            'payment_method' => $this->faker->randomElement(['bkash', 'nagad', 'rocket', 'ssl_commerce']),
            'transaction_id' => $status === 'success' ? 'TXN' . $this->faker->unique()->numerify('##########') : null,
            'attempted_at' => $this->faker->dateTimeBetween('-1 month', 'now'),
        ];
    }

    /**
     * Indicate that the auto-debit was successful.
     */
    public function successful(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'success',
            'transaction_id' => 'TXN' . $this->faker->unique()->numerify('##########'),
            'failure_reason' => null,
        ]);
    }

    /**
     * Indicate that the auto-debit failed.
     */
    public function failed(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'failed',
            'failure_reason' => $this->faker->sentence(),
            'transaction_id' => null,
        ]);
    }

    /**
     * Indicate that the auto-debit is pending.
     */
    public function pending(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'pending',
            'failure_reason' => null,
            'transaction_id' => null,
        ]);
    }
}
