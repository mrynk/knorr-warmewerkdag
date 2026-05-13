<?php

namespace App\Actions\Knorr;

use App\Models\Reward;

final class CreateReward
{
    public function handle(string $rewardName, $release_at = null): Reward
    {
        $rewards = json_decode(file_get_contents(base_path('resources/rewards.json')), true);
        $reward = $rewards[$rewardName];

        $reward = Reward::create([
            ...$reward,
            'release_at' => $release_at ?? now()->subSecond(),
        ]);

        return $reward;
    }
}
