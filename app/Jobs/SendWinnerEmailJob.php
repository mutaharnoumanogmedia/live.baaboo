<?php

namespace App\Jobs;

use App\Mail\WinnerCashNotificationMail;
use App\Mail\WinnerNotificationMail;
use App\Mail\WinnerVoucherNotificationMail;
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

class SendWinnerEmailJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $userId;

    public string $prizeWon;

    public LiveShow $liveShow;

    public function __construct(int $userId, string $prizeWon, LiveShow $liveShow)
    {
        $this->userId = $userId;
        $this->prizeWon = $prizeWon;
        $this->liveShow = $liveShow;
    }

    public function handle(BrevoService $brevo): void
    {
        $user = User::find($this->userId);
        $show_user = UserLiveShow::where('live_show_id', $this->liveShow->id)
            ->where('user_id', $this->userId)
            ->first();

        if (! $user || ! $user->email || ! $show_user) {
            return;
        }

        // generic winner email
        $this->sendWinnerEmail(
            $brevo,
            $show_user,
            $user->email,
            new WinnerNotificationMail($user, $this->prizeWon, $this->liveShow),
            'winner_email_sent_status',
            'winner_email_sent_at',
            "WinnerNotificationMail for user ID {$user->id} (live show ID {$this->liveShow->id}, prize: {$this->prizeWon})",
        );

        // voucher winner email
        if ($show_user->discount_code != null) {
            $this->sendWinnerEmail(
                $brevo,
                $show_user,
                $user->email,
                new WinnerVoucherNotificationMail($show_user),
                'winner_voucher_email_sent_status',
                'winner_voucher_email_sent_at',
                "WinnerVoucherNotificationMail for user ID {$user->id} (live show ID {$this->liveShow->id}, prize: {$this->prizeWon})",
            );
        }

        // cash winner email (winner of a non-voucher prize)
        if ($show_user->is_winner == 1 && ($show_user->winnerPrize && $show_user->winnerPrize->is_voucher == 0)) {
            $this->sendWinnerEmail(
                $brevo,
                $show_user,
                $user->email,
                new WinnerCashNotificationMail($user, $this->prizeWon, $this->liveShow),
                'winner_cash_email_sent_status',
                'winner_cash_email_sent_at',
                "WinnerCashNotificationMail for user ID {$user->id} (live show ID {$this->liveShow->id}, prize: {$this->prizeWon})",
            );
        }
    }

    /**
     * Render a winner mailable and deliver it through Brevo, updating the
     * relevant status fields based on the outcome. A failure is recorded on
     * the status field so the email is never re-attempted.
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
        // Skip if this email was already attempted (sent or previously failed).
        if (! empty($show_user->{$statusField})) {
            Log::info("{$label} already sent to {$toEmail} , live show ID: {$show_user->live_show_id}, user ID: {$show_user->user_id}. Message ID: {$show_user->{$statusField}}. ".now()->format('d M Y, H:i:s'));
            
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
