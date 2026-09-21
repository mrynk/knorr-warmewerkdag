<?php

declare(strict_types=1);

namespace App\Mail;

use App\Models\Entry;
use App\Models\Reward;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\File;

final class RewardConfirmationEmail extends Mailable implements ShouldQueue
{
    use Queueable;
    use SerializesModels;

    public function __construct(public Entry $entry) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Je hebt een warme prijs gewonnen!',
        );
    }

    public function content(): Content
    {
        $reward = $this->entry->reward;

        abort_unless($reward instanceof Reward, 404);

        /** @var array<string, array{name: string, title: string, description: string, amount: int}> $rewards */
        $rewards = json_decode(File::get(base_path('resources/rewards.json')), true, flags: JSON_THROW_ON_ERROR);

        return new Content(
            view: 'emails.reward-confirmation',
            with: [
                'entry' => $this->entry,
                'reward' => $rewards[$reward->name],
            ],
        );
    }

    /**
     * @return list<Attachment>
     */
    public function attachments(): array
    {
        return [];
    }
}
