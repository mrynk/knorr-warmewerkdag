<?php

declare(strict_types=1);

namespace App\Actions;

use App\Models\Entry;
use App\Models\Reward;
use Illuminate\Support\Facades\Log;

final readonly class AssignReward
{
    public function handle(Entry $entry): ?Reward
    {
        $alreadyWon = Reward::query()
            ->whereRelation('entry', 'email', $entry->email)
            ->exists();

        if ($alreadyWon) {
            Log::info('User won before', ['email' => $entry->email]);

            return null;
        }

        $reward = Reward::query()
            ->where('release_at', '<=', now())
            ->whereNull('entry_id')
            ->oldest('release_at')
            ->lockForUpdate()
            ->first();

        if (! $reward instanceof Reward) {
            return null;
        }

        $reward->entry()->associate($entry);
        $reward->save();

        return $reward;
    }
}
