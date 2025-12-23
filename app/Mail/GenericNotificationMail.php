<?php

namespace App\Mail;

use App\Data\NotificationPayload;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class GenericNotificationMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public function __construct(
        public User $user,
        public NotificationPayload $payload
    ) {}

    public function build()
    {
        return $this
            ->subject($this->payload->title)
            ->view('emails.generic-notification');
    }
}
