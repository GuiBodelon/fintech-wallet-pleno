<?php

namespace App\Http\Requests\Wallet;

use Closure;
use Illuminate\Foundation\Http\FormRequest;

class WalletAmountRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        return [
            'amount' => [
                'required',
                function (string $attribute, mixed $value, Closure $fail): void {
                    $amountCents = $this->parseAmountCents($value);

                    if ($amountCents === null || $amountCents <= 0) {
                        $fail('The amount must be a valid currency value greater than zero.');
                    }
                },
            ],
        ];
    }

    public function amountCents(): int
    {
        return $this->parseAmountCents($this->input('amount')) ?? 0;
    }

    private function parseAmountCents(mixed $value): ?int
    {
        if (is_int($value)) {
            return $value * 100;
        }

        if (! is_string($value)) {
            return null;
        }

        $value = trim($value);

        if (! preg_match('/^\d+([,.]\d{1,2})?$/', $value)) {
            return null;
        }

        $normalized = str_replace(',', '.', $value);
        [$reais, $cents] = array_pad(explode('.', $normalized, 2), 2, '');

        return ((int) $reais * 100) + (int) str_pad($cents, 2, '0');
    }
}
