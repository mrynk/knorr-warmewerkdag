<?php

declare(strict_types=1);

namespace App\Actions;

use App\Models\Reward;
use Carbon\CarbonInterface;
use Illuminate\Support\Facades\File;

final readonly class CreateReward
{
    public function handle(string $rewardName, ?CarbonInterface $releaseAt = null): Reward
    {
        /** @var array<string, array{name: string, title: string, description: string, amount: int}> $rewards */
        $rewards = json_decode(File::get(base_path('resources/rewards.json')), true, flags: JSON_THROW_ON_ERROR);

        $reward = $rewards[$rewardName];

        return Reward::query()->create([
            'name' => $reward['name'],
            'description' => $reward['description'],
            'release_at' => $releaseAt ?? now()->subSecond(),
        ]);
    }
}
