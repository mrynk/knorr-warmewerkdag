<?php

declare(strict_types=1);

use App\Actions\CreateReward;

it('creates a reward from the catalog', function (): void {
    $reward = resolve(CreateReward::class)->handle('sokken');

    expect($reward->name)->toBe('sokken')
        ->and($reward->description)->toContain('Soxs')
        ->and($reward->release_at->lessThanOrEqualTo(now()))->toBeTrue();
});

it('uses a custom release date', function (): void {
    $releaseAt = now()->addDay();

    $reward = resolve(CreateReward::class)->handle('kachel', $releaseAt);

    expect($reward->name)->toBe('kachel')
        ->and($reward->release_at->getTimestamp())->toBe($releaseAt->getTimestamp());
});
