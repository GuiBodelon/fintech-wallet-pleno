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
        $this->recordOperation(
            $lowBalanceUser,
            $walletService,
            'deposit',
            500,
            now()->subHours(12),
        );
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
            ['deposit', 120000, $this->pastMonthDate(4, 8, 9, 30)],
            ['withdraw', 4990, $this->pastMonthDate(4, 12, 14, 10)],
            ['deposit', 25075, $this->pastMonthDate(3, 5, 10, 0)],
            ['withdraw', 3290, $this->pastMonthDate(3, 18, 16, 45)],
            ['deposit', 10000, $this->pastMonthDate(2, 3, 8, 15)],
            ['deposit', 7550, $this->pastMonthDate(2, 20, 11, 20)],
            ['withdraw', 12000, $this->pastMonthDate(1, 7, 15, 0)],
            ['deposit', 4990, $this->pastMonthDate(1, 16, 12, 5)],
            ['withdraw', 8750, $this->pastMonthDate(1, 22, 18, 30)],

            // Current month, always before now.
            ['deposit', 30025, $this->recentCurrentMonthDate(10, 9, 0)],
            ['deposit', 10000, $this->recentCurrentMonthDate(7, 13, 40)],
            ['withdraw', 4990, $this->recentCurrentMonthDate(5, 17, 15)],
            ['deposit', 25075, $this->recentCurrentMonthDate(3, 10, 25)],
            ['withdraw', 15000, $this->recentCurrentMonthDate(1, 19, 5)],
        ];

        foreach ($operations as [$operation, $amountCents, $date]) {
            $this->recordOperation($user, $walletService, $operation, $amountCents, $date);
        }
    }

    private function pastMonthDate(int $monthsAgo, int $day, int $hour, int $minute): \Illuminate\Support\Carbon
    {
        $date = now()
            ->subMonthsNoOverflow($monthsAgo)
            ->startOfMonth();

        $safeDay = min($day, $date->daysInMonth);

        return $date
            ->setDay($safeDay)
            ->setTime($hour, $minute);
    }

    private function recentCurrentMonthDate(int $daysAgo, int $hour, int $minute): \Illuminate\Support\Carbon
    {
        $date = now()
            ->startOfDay()
            ->subDays($daysAgo)
            ->setTime($hour, $minute);

        if ($date->isFuture() || !$date->isSameMonth(now())) {
            return now()->subHours($daysAgo + 1);
        }

        return $date;
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
