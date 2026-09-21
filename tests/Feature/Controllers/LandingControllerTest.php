<?php

declare(strict_types=1);

use App\Mail\RewardConfirmationEmail;
use App\Models\Entry;
use App\Models\Reward;
use Illuminate\Support\Facades\Mail;
use Inertia\Testing\AssertableInertia as Assert;

it('renders the landing page', function (): void {
    $this->get(route('landing'))
        ->assertOk()
        ->assertInertia(fn (Assert $page): Assert => $page
            ->component('Landing')
            ->has('soups')
            ->has('soup_of_the_day'));
});

it('redeems a valid code without a reward', function (): void {
    Mail::fake();

    $code = campaignCode();

    $this->post(route('redeem'), [
        'code' => $code,
        'name' => 'Michel',
        'email' => 'michel@example.com',
        'soup' => 'turkse-linzen',
    ])->assertRedirect(route('result', $code));

    $this->assertDatabaseHas('entries', [
        'code' => $code,
        'email' => 'michel@example.com',
    ]);

    Mail::assertNothingQueued();
});

it('redeems a valid code and assigns a reward', function (): void {
    Mail::fake();

    Reward::factory()->create([
        'release_at' => now()->subMinute(),
    ]);

    $code = campaignCode();

    $this->post(route('redeem'), [
        'code' => $code,
        'name' => 'Michel',
        'email' => 'michel@example.com',
        'soup' => 'turkse-linzen',
    ])->assertRedirect(route('result', $code));

    Mail::assertQueued(RewardConfirmationEmail::class);
});

it('rejects a duplicate code', function (): void {
    $code = campaignCode();

    Entry::factory()->create([
        'code' => $code,
        'batch_number' => 0,
        'serial_number' => 1,
    ]);

    $this->from(route('landing'))
        ->post(route('redeem'), [
            'code' => $code,
            'name' => 'Michel',
            'email' => 'other@example.com',
            'soup' => 'turkse-linzen',
        ])
        ->assertRedirect(route('landing'))
        ->assertSessionHasErrors(['code' => 'Ongeldige actiecode (011)']);
});

it('rejects an unexpected create failure', function (): void {
    Reward::factory()->create([
        'release_at' => now()->subMinute(),
    ]);

    Mail::shouldReceive('to')->once()->andThrow(new RuntimeException('boom'));

    $this->from(route('landing'))
        ->post(route('redeem'), [
            'code' => campaignCode(),
            'name' => 'Michel',
            'email' => 'michel@example.com',
            'soup' => 'turkse-linzen',
        ])
        ->assertRedirect(route('landing'))
        ->assertSessionHasErrors(['code' => 'Ongeldige actiecode (010)']);
});

it('rejects an invalid code', function (): void {
    $this->from(route('landing'))
        ->post(route('redeem'), [
            'code' => 'INVALID1',
            'name' => 'Michel',
            'email' => 'michel@example.com',
            'soup' => 'turkse-linzen',
        ])
        ->assertRedirect(route('landing'))
        ->assertSessionHasErrors('code');
});

it('renders a result for an existing entry', function (): void {
    $entry = Entry::factory()->create([
        'code' => campaignCode(),
        'batch_number' => 0,
        'serial_number' => 1,
    ]);

    $this->get(route('result', $entry->code))
        ->assertOk()
        ->assertInertia(fn (Assert $page): Assert => $page
            ->component('Landing')
            ->has('entry'));
});

it('returns 404 for a malformed result code', function (): void {
    $this->get(route('result', 'INVALID1'))->assertNotFound();
});

it('returns 404 for a valid code without an entry', function (): void {
    $this->get(route('result', campaignCode(1, 2)))->assertNotFound();
});

it('throttles redeem requests', function (): void {
    foreach (range(1, 10) as $serialNumber) {
        $this->post(route('redeem'), [
            'code' => campaignCode(0, $serialNumber),
            'name' => 'Michel',
            'email' => "michel{$serialNumber}@example.com",
            'soup' => 'turkse-linzen',
        ])->assertRedirect();
    }

    $this->from(route('landing'))
        ->post(route('redeem'), [
            'code' => campaignCode(0, 11),
            'name' => 'Michel',
            'email' => 'last@example.com',
            'soup' => 'turkse-linzen',
        ])
        ->assertRedirect(route('landing'))
        ->assertSessionHasErrors('message');
});
