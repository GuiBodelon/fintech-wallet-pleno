<?php

namespace Database\Seeders;

use App\Models\Transaction;
use App\Models\User;
use App\Services\WalletService;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    private const PASSWORD = 'password';

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $walletService = app(WalletService::class);

        $demoUser = $this->seedUser('demo@fintech.test', 'Demo User');
        $this->resetWallet($demoUser);
        $this->seedDemoTransactions($demoUser, $walletService);

        $emptyUser = $this->seedUser('empty@fintech.test', 'Empty Wallet User');
        $this->resetWallet($emptyUser);

        $lowBalanceUser = $this->seedUser('lowbalance@fintech.test', 'Low Balance User');
        $this->resetWallet($lowBalanceUser);
        $this->recordOperation($lowBalanceUser, $walletService, 'deposit', 500, now()->subDays(2));
    }

    private function seedUser(string $email, string $name): User
    {
        return User::query()->updateOrCreate(
            ['email' => $email],
            [
                'name' => $name,
                'password' => self::PASSWORD,
            ],
        );
    }

    private function resetWallet(User $user): void
    {
        $user->transactions()->delete();

        $user->wallet()->updateOrCreate(
            ['user_id' => $user->id],
            ['balance_cents' => 0],
        );
    }

    private function seedDemoTransactions(User $user, WalletService $walletService): void
    {
        $operations = [
            ['deposit', 120000, now()->subMonths(4)->setDay(8)->setTime(9, 30)],
            ['withdraw', 4990, now()->subMonths(4)->setDay(12)->setTime(14, 10)],
            ['deposit', 25075, now()->subMonths(3)->setDay(5)->setTime(10, 0)],
            ['withdraw', 3290, now()->subMonths(3)->setDay(18)->setTime(16, 45)],
            ['deposit', 10000, now()->subMonths(2)->setDay(3)->setTime(8, 15)],
            ['deposit', 7550, now()->subMonths(2)->setDay(20)->setTime(11, 20)],
            ['withdraw', 12000, now()->subMonths(1)->setDay(7)->setTime(15, 0)],
            ['deposit', 4990, now()->subMonths(1)->setDay(16)->setTime(12, 5)],
            ['withdraw', 8750, now()->subMonths(1)->setDay(22)->setTime(18, 30)],
            ['deposit', 30025, now()->startOfMonth()->addDays(1)->setTime(9, 0)],
            ['deposit', 10000, now()->startOfMonth()->addDays(5)->setTime(13, 40)],
            ['withdraw', 4990, now()->startOfMonth()->addDays(8)->setTime(17, 15)],
            ['deposit', 25075, now()->startOfMonth()->addDays(12)->setTime(10, 25)],
            ['withdraw', 15000, now()->startOfMonth()->addDays(15)->setTime(19, 5)],
        ];

        foreach ($operations as [$operation, $amountCents, $date]) {
            $this->recordOperation($user, $walletService, $operation, $amountCents, $date);
        }
    }

    private function recordOperation(
        User $user,
        WalletService $walletService,
        string $operation,
        int $amountCents,
        mixed $date,
    ): Transaction {
        $transaction = $operation === 'deposit'
            ? $walletService->deposit($user, $amountCents)
            : $walletService->withdraw($user, $amountCents);

        $transaction->update([
            'created_at' => $date,
            'updated_at' => $date,
        ]);

        return $transaction;
    }
}
