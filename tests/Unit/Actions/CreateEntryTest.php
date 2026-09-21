<?php

declare(strict_types=1);

use App\Actions\CreateEntry;
use App\Mail\RewardConfirmationEmail;
use App\Models\Reward;
use Illuminate\Support\Facades\Mail;

it('creates an entry without a reward', function (): void {
    Mail::fake();

    $code = campaignCode();

    $entry = resolve(CreateEntry::class)->handle([
        'code' => $code,
        'name' => 'Michel',
        'email' => 'michel@example.com',
        'soup' => 'turkse-linzen',
    ]);

    expect($entry->code)->toBe($code)
        ->and($entry->reward)->toBeNull();

    Mail::assertNothingQueued();
});

it('queues a confirmation email when a reward is assigned', function (): void {
    Mail::fake();

    Reward::factory()->create([
        'release_at' => now()->subMinute(),
    ]);

    $entry = resolve(CreateEntry::class)->handle([
        'code' => campaignCode(),
        'name' => 'Michel',
        'email' => 'michel@example.com',
        'soup' => 'turkse-linzen',
    ]);

    expect($entry->reward)->not->toBeNull();

    Mail::assertQueued(RewardConfirmationEmail::class, fn (RewardConfirmationEmail $mail): bool => $mail->entry->is($entry));
});
