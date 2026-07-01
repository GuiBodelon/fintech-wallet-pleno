<?php

namespace Tests\Feature;

use App\Models\Transaction;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class DashboardTest extends TestCase
{
    use RefreshDatabase;

    public function test_dashboard_returns_current_balance(): void
    {
        [$user] = $this->userWithWallet(12345);

        Sanctum::actingAs($user);

        $this->getJson('/api/dashboard')
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.wallet.balance_cents', 12345);
    }

    public function test_dashboard_returns_last_5_transactions(): void
    {
        [$user, $wallet] = $this->userWithWallet();

        foreach ([1, 2, 3, 4, 5, 6] as $day) {
            Transaction::factory()->for($user)->for($wallet)->create([
                'amount_cents' => $day * 100,
                'created_at' => now()->subDays(6 - $day),
            ]);
        }

        Sanctum::actingAs($user);

        $this->getJson('/api/dashboard')
            ->assertOk()
            ->assertJsonCount(5, 'data.last_transactions')
            ->assertJsonPath('data.last_transactions.0.amount_cents', 600)
            ->assertJsonPath('data.last_transactions.4.amount_cents', 200);
    }

    public function test_dashboard_returns_current_month_deposited_total(): void
    {
        [$user, $wallet] = $this->userWithWallet();

        Transaction::factory()->credit()->for($user)->for($wallet)->create([
            'amount_cents' => 1000,
            'created_at' => now()->startOfMonth()->addDay(),
        ]);
        Transaction::factory()->credit()->for($user)->for($wallet)->create([
            'amount_cents' => 2500,
            'created_at' => now()->startOfMonth()->addDays(2),
        ]);
        Transaction::factory()->credit()->for($user)->for($wallet)->create([
            'amount_cents' => 9999,
            'created_at' => now()->subMonth(),
        ]);

        Sanctum::actingAs($user);

        $this->getJson('/api/dashboard')
            ->assertOk()
            ->assertJsonPath('data.current_month.deposited_cents', 3500);
    }

    public function test_dashboard_returns_current_month_withdrawn_total(): void
    {
        [$user, $wallet] = $this->userWithWallet();

        Transaction::factory()->debit()->for($user)->for($wallet)->create([
            'amount_cents' => 700,
            'created_at' => now()->startOfMonth()->addDay(),
        ]);
        Transaction::factory()->debit()->for($user)->for($wallet)->create([
            'amount_cents' => 800,
            'created_at' => now()->startOfMonth()->addDays(2),
        ]);
        Transaction::factory()->debit()->for($user)->for($wallet)->create([
            'amount_cents' => 9999,
            'created_at' => now()->subMonth(),
        ]);

        Sanctum::actingAs($user);

        $this->getJson('/api/dashboard')
            ->assertOk()
            ->assertJsonPath('data.current_month.withdrawn_cents', 1500);
    }

    public function test_dashboard_only_uses_authenticated_users_data(): void
    {
        [$user, $wallet] = $this->userWithWallet(5000);
        [$otherUser, $otherWallet] = $this->userWithWallet(999999);

        Transaction::factory()->credit()->for($user)->for($wallet)->create([
            'amount_cents' => 1000,
            'created_at' => now(),
        ]);
        Transaction::factory()->credit()->for($otherUser)->for($otherWallet)->create([
            'amount_cents' => 999999,
            'created_at' => now(),
        ]);

        Sanctum::actingAs($user);

        $this->getJson('/api/dashboard')
            ->assertOk()
            ->assertJsonPath('data.wallet.balance_cents', 5000)
            ->assertJsonPath('data.current_month.deposited_cents', 1000)
            ->assertJsonCount(1, 'data.last_transactions')
            ->assertJsonPath('data.last_transactions.0.amount_cents', 1000);
    }

    private function userWithWallet(int $balanceCents = 0): array
    {
        $user = User::factory()->create();
        $wallet = $user->wallet()->create([
            'balance_cents' => $balanceCents,
        ]);

        return [$user, $wallet];
    }
}
