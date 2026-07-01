<?php

namespace App\Services;

use App\Models\Transaction;
use App\Models\User;
use App\Models\Wallet;
use DomainException;
use Illuminate\Support\Facades\DB;

class WalletService
{
    public function deposit(User $user, int $amountCents): Transaction
    {
        $this->ensurePositiveAmount($amountCents);

        return DB::transaction(function () use ($user, $amountCents): Transaction {
            $wallet = $this->lockedWalletFor($user);
            $balanceAfterCents = $wallet->balance_cents + $amountCents;

            $wallet->update([
                'balance_cents' => $balanceAfterCents,
            ]);

            return $wallet->transactions()->create([
                'user_id' => $user->id,
                'type' => Transaction::TYPE_CREDIT,
                'amount_cents' => $amountCents,
                'balance_after_cents' => $balanceAfterCents,
            ]);
        });
    }

    public function withdraw(User $user, int $amountCents): Transaction
    {
        $this->ensurePositiveAmount($amountCents);

        return DB::transaction(function () use ($user, $amountCents): Transaction {
            $wallet = $this->lockedWalletFor($user);

            if ($wallet->balance_cents < $amountCents) {
                throw new DomainException('Insufficient wallet balance.');
            }

            $balanceAfterCents = $wallet->balance_cents - $amountCents;

            $wallet->update([
                'balance_cents' => $balanceAfterCents,
            ]);

            return $wallet->transactions()->create([
                'user_id' => $user->id,
                'type' => Transaction::TYPE_DEBIT,
                'amount_cents' => $amountCents,
                'balance_after_cents' => $balanceAfterCents,
            ]);
        });
    }

    private function ensurePositiveAmount(int $amountCents): void
    {
        if ($amountCents <= 0) {
            throw new DomainException('Amount must be greater than zero cents.');
        }
    }

    private function lockedWalletFor(User $user): Wallet
    {
        $wallet = Wallet::query()
            ->where('user_id', $user->id)
            ->lockForUpdate()
            ->first();

        if (! $wallet) {
            throw new DomainException('Wallet not found for user.');
        }

        return $wallet;
    }
}
