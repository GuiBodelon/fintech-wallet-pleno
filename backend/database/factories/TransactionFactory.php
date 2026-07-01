<?php

namespace Database\Factories;

use App\Models\Transaction;
use App\Models\User;
use App\Models\Wallet;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Transaction>
 */
class TransactionFactory extends Factory
{
    protected $model = Transaction::class;

    public function definition(): array
    {
        $user = User::factory();

        return [
            'user_id' => $user,
            'wallet_id' => Wallet::factory()->for($user),
            'type' => Transaction::TYPE_CREDIT,
            'amount_cents' => 1000,
            'balance_after_cents' => 1000,
        ];
    }

    public function credit(): static
    {
        return $this->state(fn () => [
            'type' => Transaction::TYPE_CREDIT,
        ]);
    }

    public function debit(): static
    {
        return $this->state(fn () => [
            'type' => Transaction::TYPE_DEBIT,
        ]);
    }
}
