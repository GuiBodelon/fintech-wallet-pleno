<?php

namespace Tests\Feature;

use App\Models\Transaction;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class TransactionHistoryTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_sees_only_own_transactions(): void
    {
        [$user, $wallet] = $this->userWithWallet();
        [$otherUser, $otherWallet] = $this->userWithWallet();

        Transaction::factory()->for($user)->for($wallet)->create([
            'amount_cents' => 1000,
        ]);
        Transaction::factory()->for($otherUser)->for($otherWallet)->create([
            'amount_cents' => 9999,
        ]);

        Sanctum::actingAs($user);

        $this->getJson('/api/transactions')
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonCount(1, 'data.transactions')
            ->assertJsonPath('data.transactions.0.amount_cents', 1000);
    }

    public function test_filter_by_type_works(): void
    {
        [$user, $wallet] = $this->userWithWallet();

        Transaction::factory()->credit()->for($user)->for($wallet)->create();
        Transaction::factory()->debit()->for($user)->for($wallet)->create();

        Sanctum::actingAs($user);

        $this->getJson('/api/transactions?type=debit')
            ->assertOk()
            ->assertJsonCount(1, 'data.transactions')
            ->assertJsonPath('data.transactions.0.type', Transaction::TYPE_DEBIT);
    }

    public function test_filter_by_date_period_works(): void
    {
        [$user, $wallet] = $this->userWithWallet();

        Transaction::factory()->for($user)->for($wallet)->create([
            'amount_cents' => 1000,
            'created_at' => '2026-06-30 12:00:00',
        ]);
        Transaction::factory()->for($user)->for($wallet)->create([
            'amount_cents' => 2000,
            'created_at' => '2026-07-01 12:00:00',
        ]);
        Transaction::factory()->for($user)->for($wallet)->create([
            'amount_cents' => 3000,
            'created_at' => '2026-07-02 12:00:00',
        ]);

        Sanctum::actingAs($user);

        $this->getJson('/api/transactions?from=2026-07-01&to=2026-07-01')
            ->assertOk()
            ->assertJsonCount(1, 'data.transactions')
            ->assertJsonPath('data.transactions.0.amount_cents', 2000);
    }

    public function test_pagination_metadata_exists(): void
    {
        [$user, $wallet] = $this->userWithWallet();

        Transaction::factory(3)->for($user)->for($wallet)->create();

        Sanctum::actingAs($user);

        $this->getJson('/api/transactions?per_page=2')
            ->assertOk()
            ->assertJsonCount(2, 'data.transactions')
            ->assertJsonStructure([
                'data' => [
                    'pagination' => [
                        'current_page',
                        'per_page',
                        'total',
                        'last_page',
                        'from',
                        'to',
                    ],
                ],
            ])
            ->assertJsonPath('data.pagination.current_page', 1)
            ->assertJsonPath('data.pagination.per_page', 2)
            ->assertJsonPath('data.pagination.total', 3)
            ->assertJsonPath('data.pagination.last_page', 2);
    }

    private function userWithWallet(): array
    {
        $user = User::factory()->create();
        $wallet = $user->wallet()->create([
            'balance_cents' => 0,
        ]);

        return [$user, $wallet];
    }
}
