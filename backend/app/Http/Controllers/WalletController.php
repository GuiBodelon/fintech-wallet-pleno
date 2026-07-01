<?php

namespace App\Http\Controllers;

use App\Http\Requests\Wallet\WalletAmountRequest;
use App\Models\Transaction;
use App\Support\ApiResponse;
use App\Services\WalletService;
use DomainException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class WalletController extends Controller
{
    public function show(Request $request): JsonResponse
    {
        $wallet = $request->user()->wallet()->first();

        if (! $wallet) {
            throw new DomainException('Wallet not found for user.');
        }

        return ApiResponse::success([
            'wallet' => [
                'id' => $wallet->id,
                'balance_cents' => $wallet->balance_cents,
            ],
        ]);
    }

    public function deposit(WalletAmountRequest $request, WalletService $walletService): JsonResponse
    {
        $transaction = $walletService->deposit($request->user(), $request->amountCents());

        return ApiResponse::success($this->transactionPayload($transaction), 'Deposit completed successfully.', 201);
    }

    public function withdraw(WalletAmountRequest $request, WalletService $walletService): JsonResponse
    {
        $transaction = $walletService->withdraw($request->user(), $request->amountCents());

        return ApiResponse::success($this->transactionPayload($transaction), 'Withdrawal completed successfully.', 201);
    }

    /**
     * @return array{wallet: array{id: int, balance_cents: int}, transaction: array{id: int, type: string, amount_cents: int, balance_after_cents: int}}
     */
    private function transactionPayload(Transaction $transaction): array
    {
        return [
            'wallet' => [
                'id' => $transaction->wallet_id,
                'balance_cents' => $transaction->balance_after_cents,
            ],
            'transaction' => [
                'id' => $transaction->id,
                'type' => $transaction->type,
                'amount_cents' => $transaction->amount_cents,
                'balance_after_cents' => $transaction->balance_after_cents,
            ],
        ];
    }
}
