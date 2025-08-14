<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class ValidCode implements ValidationRule
{
    /**
     * Run the validation rule.
     *
     * @param  \Closure(string, ?string=): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $sqids = app(\Sqids\Sqids::class);
        $decoded = $sqids->decode($value);

        if (empty($decoded) || count($decoded) !== 2) {
            $fail('Dit is een ongeldige actiecode. Controlleer je code en probeer het opnieuw (001)');

            return;
        }

        [$batchNumber, $serialNumber] = $decoded;

        if ($sqids->encode([$batchNumber, $serialNumber]) !== $value) {
            $fail('Dit is een ongeldige actiecode. Controlleer je code en probeer het opnieuw (002)');
        }

        $batch = config('unique_codes.batches')[$batchNumber];

        if (! $batch || $batch['amount'] < $serialNumber) {
            $fail('Dit is een ongeldige actiecode. Controlleer je code en probeer het opnieuw (004)');
        }
    }
}
