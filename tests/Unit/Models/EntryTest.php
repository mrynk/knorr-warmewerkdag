<?php

declare(strict_types=1);

use App\Models\Entry;
use App\Models\Reward;

it('masks short email usernames', function (): void {
    $entry = Entry::factory()->create([
        'email' => 'ab@example.com',
    ]);

    expect($entry->masked_email)->toBe('a***b@example.com');
});

it('masks long email usernames', function (): void {
    $entry = Entry::factory()->create([
        'email' => 'michelangelo@example.com',
    ]);

    expect($entry->masked_email)->toBe('mi***lo@example.com');
});

it('exposes a reward relation', function (): void {
    $entry = Entry::factory()->create();
    $reward = Reward::factory()->create([
        'entry_id' => $entry->id,
    ]);

    expect($entry->fresh()?->reward?->is($reward))->toBeTrue();
});
