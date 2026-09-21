<?php

declare(strict_types=1);

use App\Mail\RewardConfirmationEmail;
use App\Models\Entry;
use App\Models\Reward;
use Illuminate\Mail\Mailables\Content;
use Symfony\Component\HttpKernel\Exception\HttpException;

it('builds the confirmation envelope and content', function (): void {
    $entry = Entry::factory()->create();
    Reward::factory()->create([
        'name' => 'sokken',
        'entry_id' => $entry->id,
    ]);

    $mailable = new RewardConfirmationEmail($entry->fresh(['reward']));

    expect($mailable->envelope()->subject)->toBe('Je hebt een warme prijs gewonnen!')
        ->and($mailable->attachments())->toBe([]);

    $content = $mailable->content();

    expect($content->view)->toBe('emails.reward-confirmation')
        ->and($content->with['reward']['title'])->toBe('paar sokken van Soxs');
});

it('aborts when the entry has no reward', function (): void {
    $entry = Entry::factory()->create();

    expect(fn (): Content => new RewardConfirmationEmail($entry)->content())
        ->toThrow(HttpException::class);
});
