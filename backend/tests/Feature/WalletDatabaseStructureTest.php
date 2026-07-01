<?php

namespace Tests\Feature;

use App\Models\Transaction;
use App\Models\User;
use App\Models\Wallet;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class WalletDatabaseStructureTest extends TestCase
{
    use RefreshDatabase;

    public function test_wallet_and_transaction_tables_have_required_columns(): void
    {
        $this->assertTrue(Schema::hasColumns('wallets', [
            'id',
            'user_id',
            'balance_cents',
            'created_at',
            'updated_at',
        ]));

        $this->assertTrue(Schema::hasColumns('transactions', [
            'id',
            'wallet_id',
            'user_id',
            'type',
            'amount_cents',
            'balance_after_cents',
            'created_at',
            'updated_at',
        ]));
    }

    public function test_wallet_balance_defaults_to_zero_cents(): void
    {
        $wallet = Wallet::query()->create([
            'user_id' => User::factory()->create()->id,
        ]);

        $this->assertSame(0, $wallet->refresh()->balance_cents);
    }

    public function test_wallet_and_transaction_relationships_are_configured(): void
    {
        $user = User::factory()->create();
        $wallet = Wallet::factory()->for($user)->create();
        $transaction = Transaction::factory()
            ->for($user)
            ->for($wallet)
            ->credit()
            ->create([
                'amount_cents' => 2500,
                'balance_after_cents' => 2500,
            ]);

        $this->assertTrue($user->wallet->is($wallet));
        $this->assertTrue($wallet->user->is($user));
        $this->assertTrue($wallet->transactions->first()->is($transaction));
        $this->assertTrue($transaction->wallet->is($wallet));
        $this->assertTrue($transaction->user->is($user));
    }

    public function test_transaction_type_must_be_credit_or_debit(): void
    {
        $this->assertSame('credit', Transaction::TYPE_CREDIT);
        $this->assertSame('debit', Transaction::TYPE_DEBIT);
    }
}
