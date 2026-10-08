<?php

namespace App\Support;

use Carbon\Carbon;
use Closure;

class ExpiryMonth
{
    public static function rules(bool $required, string $receivedDate): array
    {
        return [
            $required ? 'required' : 'nullable',
            'date_format:Y-m',
            function (string $attribute, mixed $value, Closure $fail) use ($receivedDate): void {
                if (is_string($value) && preg_match('/^\d{4}-\d{2}$/', $value)
                    && $value < substr($receivedDate, 0, 7)) {
                    $fail('Expiry month cannot be earlier than the receiving month.');
                }
            },
        ];
    }

    public static function toDate(?string $value): ?string
    {
        if (blank($value)) {
            return null;
        }

        // Existing receipt drafts may already contain a complete date.
        return preg_match('/^\d{4}-\d{2}$/', $value)
            ? Carbon::createFromFormat('!Y-m', $value)->endOfMonth()->toDateString()
            : $value;
    }
}
