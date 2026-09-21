<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Entry;
use Illuminate\Database\Eloquent\Factories\Factory;
use Sqids\Sqids;

/**
 * @extends Factory<Entry>
 */
final class EntryFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $batchNumber = 0;
        $serialNumber = fake()->unique()->numberBetween(1, 1000);

        return [
            'batch_number' => $batchNumber,
            'serial_number' => $serialNumber,
            'code' => resolve(Sqids::class)->encode([$batchNumber, $serialNumber]),
            'name' => fake()->name(),
            'email' => fake()->unique()->safeEmail(),
            'soup' => 'turkse-linzen',
        ];
    }
}
