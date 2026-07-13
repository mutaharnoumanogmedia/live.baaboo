<?php

namespace App\Jobs;

use App\Mail\SpecialCashNotificationMail;
use App\Mail\SpecialCustomNotificationMail;
use App\Mail\SpecialVoucherNotificationMail;
use App\Mail\SpecialWinnerNotificationMail;
use App\Models\LiveShow;
use App\Models\User;
use App\Models\UserLiveShow;
use App\Services\BrevoService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class SendSpecialWinnerEmailJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $userId;

    public int $liveShowId;

    public function __construct(int $userId, int $liveShowId)
    {
        $this->userId = $userId;
        $this->liveShowId = $liveShowId;
    }

    public function handle(BrevoService $brevo): void
    {
        $liveShow = LiveShow::find($this->liveShowId);
        $user = User::find($this->userId);
        $show_user = UserLiveShow::with('specialGift')
            ->where('live_show_id', $this->liveShowId)
            ->where('user_id', $this->userId)
            ->first();

        if (! $liveShow || ! $user || ! $user->email || ! $show_user) {
            return;
        }

        $prizeWon = $show_user->special_prize_won ?: 'n/a';

        // Generic special winner email.
        $this->sendWinnerEmail(
            $brevo,
            $show_user,
            $user->email,
            new SpecialWinnerNotificationMail($user, $prizeWon, $liveShow),
            'special_winner_email_sent_status',
            'special_winner_email_sent_at',
            "SpecialWinnerNotificationMail for user ID {$user->id} (live show ID {$this->liveShowId}, prize: {$prizeWon})",
        );

        // Type-based email (cash / voucher / custom).
        $type = $show_user->specialGift->type ?? null;
        $typeMailable = null;

        if ($type === 'voucher' && $show_user->special_discount_code) {
            $typeMailable = new SpecialVoucherNotificationMail($show_user);
        } elseif ($type === 'cash') {
            $typeMailable = new SpecialCashNotificationMail($user, $prizeWon, $liveShow);
        } elseif ($type === 'custom') {
            $typeMailable = new SpecialCustomNotificationMail($user, $prizeWon, $liveShow);
        }

        if ($typeMailable) {
            $this->sendWinnerEmail(
                $brevo,
                $show_user,
                $user->email,
                $typeMailable,
                'special_type_email_sent_status',
                'special_type_email_sent_at',
                "Special type email ({$type}) for user ID {$user->id} (live show ID {$this->liveShowId}, prize: {$prizeWon})",
            );
        }
    }

    /**
     * Render a special-winner mailable and deliver it through Brevo, recording
     * the outcome on the given status field so it is never re-attempted.
     */
    protected function sendWinnerEmail(
        BrevoService $brevo,
        UserLiveShow $show_user,
        string $toEmail,
        Mailable $mailable,
        string $statusField,
        string $sentAtField,
        string $label,
    ): void {
        if (! empty($show_user->{$statusField})) {
            Log::info("{$label} already sent to {$toEmail}. Message ID: {$show_user->{$statusField}}. ".now()->format('d M Y, H:i:s'));

            return;
        }

        try {
            $result = $brevo->send(
                to: $toEmail,
                subject: (string) $mailable->envelope()->subject,
                htmlContent: $mailable->render(),
                sender: 'winners',
            );

            if ($result['success']) {
                $show_user->{$statusField} .= (',' . $result['message_id']);
                $show_user->{$sentAtField} = now();
                $show_user->save();

                Log::info("{$label} sent via Brevo. Message ID: {$result['message_id']}. ".now()->format('d M Y, H:i:s'));

                return;
            }

            $error = 'failed: '.($result['error'] ?? 'unknown error').' (status '.$result['status_code'].')';
            $show_user->{$statusField} = substr($error, 0, 250);
            $show_user->save();

            Log::error("{$label} could not be sent via Brevo. {$error} ".now()->format('d M Y, H:i:s'));
        } catch (\Throwable $e) {
            $show_user->{$statusField} = substr('failed: '.$e->getMessage(), 0, 250);
            $show_user->save();

            Log::error("{$label} threw an exception while sending via Brevo: ".$e->getMessage().' '.now()->format('d M Y, H:i:s'));
        }
    }
}
