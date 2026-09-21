<?php

declare(strict_types=1);

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Sqids\Sqids;

final readonly class ValidCode implements ValidationRule
{
    public function __construct(private Sqids $sqids) {}

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (! is_string($value)) {
            $fail('Dit is een ongeldige actiecode. Controlleer je code en probeer het opnieuw (001)');

            return;
        }

        $decoded = $this->sqids->decode($value);

        if (count($decoded) !== 2) {
            $fail('Dit is een ongeldige actiecode. Controlleer je code en probeer het opnieuw (001)');

            return;
        }

        [$batchNumber, $serialNumber] = $decoded;

        if ($this->sqids->encode([$batchNumber, $serialNumber]) !== $value) {
            $fail('Dit is een ongeldige actiecode. Controlleer je code en probeer het opnieuw (002)');

            return;
        }

        /** @var array<int, array{name: string, amount: int}> $batches */
        $batches = config('unique_codes.batches');

        if (! array_key_exists($batchNumber, $batches)) {
            $fail('Dit is een ongeldige actiecode. Controlleer je code en probeer het opnieuw (003)');

            return;
        }

        if ($batches[$batchNumber]['amount'] < $serialNumber) {
            $fail('Dit is een ongeldige actiecode. Controlleer je code en probeer het opnieuw (004)');
        }
    }
}
