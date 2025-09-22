<?php

namespace App\Mail;

use App\Models\Entry;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class RewardConfirmationEmail extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * Create a new message instance.
     */
    public function __construct(
        public Entry $entry
    ) {
        //
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Je hebt een warme prijs gewonnen!',
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        $rewards = json_decode(file_get_contents(base_path('resources/rewards.json')), true);

        return new Content(
            view: 'emails.reward-confirmation',
            with: [
                'entry' => $this->entry,
                'reward' => $rewards[$this->entry->reward->name],
            ],
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        return [];
    }
}
