<?php

declare(strict_types=1);

namespace App\Actions;

use App\Models\Entry;
use App\Models\Reward;

final readonly class StoreWinnerAddress
{
    /**
     * @param  array{full_name: string, zipcode: string, house_number: string, street: string, city: string}  $data
     */
    public function handle(Entry $entry, string $token, array $data): void
    {
        abort_unless($entry->reward instanceof Reward, 404);
        abort_unless($entry->addressTokenMatches($token), 403);

        Entry::query()
            ->whereKey($entry->id)
            ->whereNull('full_name')
            ->whereNull('zipcode')
            ->whereNull('house_number')
            ->whereNull('street')
            ->whereNull('city')
            ->update([
                'full_name' => $data['full_name'],
                'zipcode' => $data['zipcode'],
                'house_number' => $data['house_number'],
                'street' => $data['street'],
                'city' => $data['city'],
            ]);
    }
}
