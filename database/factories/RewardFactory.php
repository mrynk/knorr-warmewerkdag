<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Reward;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Reward>
 */
final class RewardFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => 'sokken',
            'description' => 'De sokken van Soxs.',
            'release_at' => now()->subMinute(),
            'entry_id' => null,
        ];
    }
}
