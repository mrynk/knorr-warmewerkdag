<?php

declare(strict_types=1);

namespace App\Rules;

final readonly class CampaignEmail
{
    /**
     * @return list<string>
     */
    public static function rules(bool $strictDns = true): array
    {
        if ($strictDns) {
            return ['required', 'email:rfc,dns,strict,filter,spoof', 'max:255'];
        }

        return ['required', 'email:rfc,strict,filter', 'max:255'];
    }
}
