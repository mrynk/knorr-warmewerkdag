<?php

declare(strict_types=1);

namespace App\Exceptions;

use RuntimeException;

final class DailySubmissionLimitReached extends RuntimeException
{
    public const int LIMIT = 4;

    public function __construct()
    {
        parent::__construct(
            'Je kunt maximaal '.self::LIMIT.' codes per dag insturen. Probeer het morgen opnieuw.',
        );
    }
}
