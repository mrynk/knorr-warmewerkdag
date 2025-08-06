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
            $fail('Ongeldige actiecode (001)');
            return;
        }

        [$batchNumber, $serialNumber] = $decoded;

        if( $sqids->encode([ $batchNumber, $serialNumber ] ) !== $value ) {
            $fail('Ongeldige actiecode (002)');
        }

        $batch = config('unique_codes.batches')[$batchNumber];

        if (!$batch || $batch['amount'] < $serialNumber) {
            $fail('Ongeldige actiecode (004)');
        }
    }
}
