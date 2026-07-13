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

class SpecialCashNotificationMail extends Mailable
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

    public function envelope(): Envelope
    {
        return new Envelope(
            from: new Address('winners@badabing.show', 'Badabing Game Show'),
            subject: 'Herzlichen Glückwunsch 🎉',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.special_cash_winner_notification',
            with: [
                'user' => $this->user,
                'amount' => $this->prizeWon,
                'liveShow' => $this->liveShow,
            ],
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
