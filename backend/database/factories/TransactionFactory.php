<?php

namespace Database\Factories;

use App\Models\Invitation;
use App\Models\Package;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Transaction>
 */
class TransactionFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $package = Package::factory();

        return [
            'invoice_number' => 'INV-'.fake()->unique()->numerify('########'),
            'user_id' => User::factory(),
            'package_id' => $package,
            'package_name_snapshot' => fake()->words(2, true),
            'invitation_id' => Invitation::factory(),
            'amount' => fake()->randomElement([99000, 249000]),
            'payment_method' => fake()->randomElement(Transaction::PAYMENT_METHODS),
            'payment_channel' => fake()->randomElement(['BCA', 'BNI', 'QRIS']),
            'status' => Transaction::STATUS_PENDING,
        ];
    }

    public function paid(): static
    {
        return $this->state([
            'status' => Transaction::STATUS_PAID,
            'paid_at' => now(),
            'verified_at' => now(),
        ]);
    }

    public function awaitingVerification(): static
    {
        return $this->state([
            'proof_image' => 'transactions/sample-proof.jpg',
            'proof_uploaded_at' => now(),
        ]);
    }
}
