<?php

namespace App\Mail;

use App\Models\LiveShow;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class WinnerCashNotificationMail extends Mailable
{
    use Queueable, SerializesModels;

    public User $user;

    public string $prizeWon;

    public LiveShow $liveShow;

    public function __construct(User $user, string $prizeWon, LiveShow $liveShow)
    {
        $this->user = $user;
        $this->prizeWon = $prizeWon;
        $this->liveShow = $liveShow;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            from: new Address('winners@badabing.show', 'Badabing Team'),
            subject: 'Herzlichen Glückwunsch 🎉',
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        // Prize is stored as free text (e.g. "50€", "50"). Normalise it to a bare amount.
        $amount = $this->prizeWon;

        return new Content(
            view: 'emails.cash_winner_notification',
            with: [
                'user' => $this->user,
                'amount' => $amount,
                'liveShow' => $this->liveShow,
            ],
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array
     */
    public function attachments(): array
    {
        return [];
    }
}
