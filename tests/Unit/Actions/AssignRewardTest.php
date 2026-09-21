<?php

declare(strict_types=1);

use App\Actions\AssignReward;
use App\Models\Entry;
use App\Models\Reward;
use Illuminate\Support\Facades\Log;

it('assigns the next released reward', function (): void {
    $entry = Entry::factory()->create();
    $reward = Reward::factory()->create([
        'release_at' => now()->subMinute(),
    ]);

    $assigned = resolve(AssignReward::class)->handle($entry);

    expect($assigned)->not->toBeNull()
        ->and($assigned?->is($reward))->toBeTrue()
        ->and($assigned?->entry_id)->toBe($entry->id);
});

it('does not assign a future reward', function (): void {
    $entry = Entry::factory()->create();
    Reward::factory()->create([
        'release_at' => now()->addHour(),
    ]);

    expect(resolve(AssignReward::class)->handle($entry))->toBeNull();
});

it('skips assignment when the email already won', function (): void {
    Log::shouldReceive('info')->once()->withArgs(fn (string $message): bool => $message === 'User won before');

    $winner = Entry::factory()->create(['email' => 'winner@example.com']);
    Reward::factory()->create([
        'entry_id' => $winner->id,
        'release_at' => now()->subHour(),
    ]);

    $available = Reward::factory()->create([
        'release_at' => now()->subMinute(),
    ]);

    $nextEntry = Entry::factory()->create(['email' => 'winner@example.com']);

    expect(resolve(AssignReward::class)->handle($nextEntry))->toBeNull()
        ->and($available->fresh()?->entry_id)->toBeNull();
});
