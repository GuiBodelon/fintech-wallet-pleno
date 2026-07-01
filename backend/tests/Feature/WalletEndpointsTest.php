<?php

namespace Tests\Feature;

use App\Models\Transaction;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class WalletEndpointsTest extends TestCase
{
    use RefreshDatabase;

    public function test_authenticated_user_can_fetch_wallet(): void
    {
        $user = User::factory()->create();
        $wallet = $user->wallet()->create([
            'balance_cents' => 12345,
        ]);

        Sanctum::actingAs($user);

        $this->getJson('/api/wallet')
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.wallet.id', $wallet->id)
            ->assertJsonPath('data.wallet.balance_cents', 12345);
    }

    public function test_authenticated_user_can_deposit(): void
    {
        $user = User::factory()->create();
        $wallet = $user->wallet()->create([
            'balance_cents' => 1000,
        ]);

        Sanctum::actingAs($user);

        $this->postJson('/api/wallet/deposit', [
            'amount' => '25.50',
        ])
            ->assertCreated()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.wallet.balance_cents', 3550)
            ->assertJsonPath('data.transaction.type', Transaction::TYPE_CREDIT)
            ->assertJsonPath('data.transaction.amount_cents', 2550)
            ->assertJsonPath('data.transaction.balance_after_cents', 3550);

        $this->assertSame(3550, $wallet->fresh()->balance_cents);
    }

    public function test_deposit_rejects_invalid_amount(): void
    {
        $user = User::factory()->create();
        $wallet = $user->wallet()->create([
            'balance_cents' => 1000,
        ]);

        Sanctum::actingAs($user);

        $this->postJson('/api/wallet/deposit', [
            'amount' => '0.00',
        ])
            ->assertUnprocessable()
            ->assertJsonPath('success', false);

        $this->postJson('/api/wallet/deposit', [
            'amount' => '-10.00',
        ])
            ->assertUnprocessable()
            ->assertJsonPath('success', false);

        $this->postJson('/api/wallet/deposit', [
            'amount' => 'abc',
        ])
            ->assertUnprocessable()
            ->assertJsonPath('success', false);

        $this->postJson('/api/wallet/deposit', [
            'amount' => '0.001',
        ])
            ->assertUnprocessable()
            ->assertJsonPath('success', false);

        $this->assertSame(1000, $wallet->fresh()->balance_cents);
        $this->assertDatabaseCount('transactions', 0);
    }

    public function test_authenticated_user_can_withdraw_with_sufficient_balance(): void
    {
        $user = User::factory()->create();
        $wallet = $user->wallet()->create([
            'balance_cents' => 5000,
        ]);

        Sanctum::actingAs($user);

        $this->postJson('/api/wallet/withdraw', [
            'amount' => '12,75',
        ])
            ->assertCreated()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.wallet.balance_cents', 3725)
            ->assertJsonPath('data.transaction.type', Transaction::TYPE_DEBIT)
            ->assertJsonPath('data.transaction.amount_cents', 1275)
            ->assertJsonPath('data.transaction.balance_after_cents', 3725);

        $this->assertSame(3725, $wallet->fresh()->balance_cents);
    }

    public function test_withdrawal_rejects_insufficient_balance(): void
    {
        $user = User::factory()->create();
        $wallet = $user->wallet()->create([
            'balance_cents' => 1000,
        ]);

        Sanctum::actingAs($user);

        $this->postJson('/api/wallet/withdraw', [
            'amount' => '10.01',
        ])
            ->assertUnprocessable()
            ->assertJson([
                'success' => false,
                'message' => 'Insufficient wallet balance.',
            ]);

        $this->assertSame(1000, $wallet->fresh()->balance_cents);
        $this->assertDatabaseCount('transactions', 0);
    }

    public function test_unauthenticated_requests_are_rejected(): void
    {
        $this->getJson('/api/wallet')->assertUnauthorized();

        $this->postJson('/api/wallet/deposit', [
            'amount' => '10.00',
        ])->assertUnauthorized();

        $this->postJson('/api/wallet/withdraw', [
            'amount' => '10.00',
        ])->assertUnauthorized();
    }
}
