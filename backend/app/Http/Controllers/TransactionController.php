<?php

namespace App\Http\Controllers;

use App\Http\Requests\Transaction\TransactionHistoryRequest;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;

class TransactionController extends Controller
{
    public function index(TransactionHistoryRequest $request): JsonResponse
    {
        $query = $request->user()
            ->transactions()
            ->latest();

        if ($request->filled('type')) {
            $query->where('type', $request->validated('type'));
        }

        if ($request->filled('from')) {
            $query->whereDate('created_at', '>=', $request->validated('from'));
        }

        if ($request->filled('to')) {
            $query->whereDate('created_at', '<=', $request->validated('to'));
        }

        $transactions = $query->paginate($request->perPage());

        return ApiResponse::success([
            'transactions' => $transactions->getCollection()->map(fn ($transaction) => [
                'id' => $transaction->id,
                'occurred_at' => $transaction->created_at?->toJSON(),
                'type' => $transaction->type,
                'amount_cents' => $transaction->amount_cents,
                'balance_after_cents' => $transaction->balance_after_cents,
            ])->values(),
            'pagination' => [
                'current_page' => $transactions->currentPage(),
                'per_page' => $transactions->perPage(),
                'total' => $transactions->total(),
                'last_page' => $transactions->lastPage(),
                'from' => $transactions->firstItem(),
                'to' => $transactions->lastItem(),
            ],
        ]);
    }
}
