<?php

namespace Database\Seeders;

use App\Models\User;
use App\Services\WalletService;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $user = User::query()->updateOrCreate(
            ['email' => 'demo@fintech.test'],
            [
                'name' => 'Demo User',
                'password' => 'password',
            ],
        );

        $wallet = $user->wallet()->firstOrCreate([
            'user_id' => $user->id,
        ]);

        if ($wallet->transactions()->doesntExist()) {
            app(WalletService::class)->deposit($user, 100000);
        }
    }
}
