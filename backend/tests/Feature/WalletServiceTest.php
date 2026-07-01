<?php

namespace Tests\Feature;

use App\Models\Transaction;
use App\Models\User;
use App\Services\WalletService;
use DomainException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WalletServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_deposit_updates_balance_and_creates_credit_transaction(): void
    {
        $user = User::factory()->create();
        $wallet = $user->wallet()->create([
            'balance_cents' => 1000,
        ]);

        $transaction = app(WalletService::class)->deposit($user, 2500);

        $this->assertSame(Transaction::TYPE_CREDIT, $transaction->type);
        $this->assertSame(2500, $transaction->amount_cents);
        $this->assertSame(3500, $transaction->balance_after_cents);
        $this->assertSame(3500, $wallet->fresh()->balance_cents);

        $this->assertDatabaseHas('transactions', [
            'wallet_id' => $wallet->id,
            'user_id' => $user->id,
            'type' => Transaction::TYPE_CREDIT,
            'amount_cents' => 2500,
            'balance_after_cents' => 3500,
        ]);
    }

    public function test_withdraw_updates_balance_and_creates_debit_transaction(): void
    {
        $user = User::factory()->create();
        $wallet = $user->wallet()->create([
            'balance_cents' => 5000,
        ]);

        $transaction = app(WalletService::class)->withdraw($user, 1250);

        $this->assertSame(Transaction::TYPE_DEBIT, $transaction->type);
        $this->assertSame(1250, $transaction->amount_cents);
        $this->assertSame(3750, $transaction->balance_after_cents);
        $this->assertSame(3750, $wallet->fresh()->balance_cents);
    }

    public function test_deposit_rejects_non_positive_amount(): void
    {
        $user = User::factory()->create();
        $user->wallet()->create([
            'balance_cents' => 1000,
        ]);

        $this->expectException(DomainException::class);
        $this->expectExceptionMessage('Amount must be greater than zero cents.');

        app(WalletService::class)->deposit($user, 0);
    }

    public function test_withdraw_rejects_insufficient_balance(): void
    {
        $user = User::factory()->create();
        $wallet = $user->wallet()->create([
            'balance_cents' => 1000,
        ]);

        try {
            app(WalletService::class)->withdraw($user, 1500);
            $this->fail('Expected insufficient balance exception was not thrown.');
        } catch (DomainException $exception) {
            $this->assertSame('Insufficient wallet balance.', $exception->getMessage());
        }

        $this->assertSame(1000, $wallet->fresh()->balance_cents);
        $this->assertDatabaseCount('transactions', 0);
    }
}
