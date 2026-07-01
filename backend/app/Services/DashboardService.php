<?php

namespace App\Services;

use App\Models\Transaction;
use App\Models\User;
use DomainException;

class DashboardService
{
    /**
     * @return array{wallet: array{balance_cents: int}, last_transactions: mixed, current_month: array{deposited_cents: int, withdrawn_cents: int}}
     */
    public function summaryFor(User $user): array
    {
        $wallet = $user->wallet()->first();

        if (! $wallet) {
            throw new DomainException('Wallet not found for user.');
        }

        $monthStart = now()->startOfMonth();
        $monthEnd = now()->endOfMonth();

        return [
            'wallet' => [
                'balance_cents' => $wallet->balance_cents,
            ],
            'last_transactions' => $user->transactions()
                ->orderByDesc('created_at')
                ->orderByDesc('id')
                ->limit(5)
                ->get()
                ->map(fn (Transaction $transaction) => [
                    'id' => $transaction->id,
                    'occurred_at' => $transaction->created_at?->toJSON(),
                    'type' => $transaction->type,
                    'amount_cents' => $transaction->amount_cents,
                    'balance_after_cents' => $transaction->balance_after_cents,
                ])
                ->values(),
            'current_month' => [
                'deposited_cents' => (int) $user->transactions()
                    ->where('type', Transaction::TYPE_CREDIT)
                    ->whereBetween('created_at', [$monthStart, $monthEnd])
                    ->sum('amount_cents'),
                'withdrawn_cents' => (int) $user->transactions()
                    ->where('type', Transaction::TYPE_DEBIT)
                    ->whereBetween('created_at', [$monthStart, $monthEnd])
                    ->sum('amount_cents'),
            ],
        ];
    }
}
