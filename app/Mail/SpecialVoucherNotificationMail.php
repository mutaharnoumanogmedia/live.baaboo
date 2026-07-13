<?php

namespace App\Mail;

use App\Models\UserLiveShow;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class SpecialVoucherNotificationMail extends Mailable
{
    use Queueable, SerializesModels;

    public UserLiveShow $show_user;

    public function __construct(UserLiveShow $show_user)
    {
        $this->show_user = $show_user;
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            from: new Address(env('MAIL_WINNERS_FROM_ADDRESS'), env('MAIL_WINNERS_FROM_NAME')),
            subject: 'Herzlichen Glückwunsch! 🎉',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.special_voucher_winner_notification',
            with: [
                'user' => $this->show_user,
            ]
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
