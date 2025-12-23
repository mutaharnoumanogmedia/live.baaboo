<?php

namespace App\Mail;

use App\Models\LiveShow;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use App\Models\User;

class WinnerNotificationMail extends Mailable
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
     *
     * @return \Illuminate\Mail\Mailables\Envelope
     */
    public function envelope()
    {
        return new Envelope(
            subject: 'Congratulations! You are a Winner',
        );
    }

    /**
     * Get the message content definition.
     *
     * @return \Illuminate\Mail\Mailables\Content
     */
    public function content()
    {
        return new Content(
            view: 'emails.winner_notification',
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array
     */
    public function attachments()
    {
        return [];
    }
}
