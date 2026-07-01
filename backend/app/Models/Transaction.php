<?php

namespace App\Models;

use Database\Factories\TransactionFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['wallet_id', 'user_id', 'type', 'amount_cents', 'balance_after_cents'])]
class Transaction extends Model
{
    /** @use HasFactory<TransactionFactory> */
    use HasFactory;

    public const TYPE_CREDIT = 'credit';

    public const TYPE_DEBIT = 'debit';

    protected function casts(): array
    {
        return [
            'amount_cents' => 'integer',
            'balance_after_cents' => 'integer',
        ];
    }

    public function wallet(): BelongsTo
    {
        return $this->belongsTo(Wallet::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
