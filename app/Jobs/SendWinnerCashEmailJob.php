<?php

namespace App\Jobs;

use App\Mail\WinnerCashNotificationMail;
use App\Models\LiveShow;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class SendWinnerCashEmailJob implements ShouldQueue
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

    public function handle(): void
    {
        $user = User::find($this->userId);

        if (! $user || ! $user->email) {
            Log::warning("SendWinnerCashEmailJob: user ID {$this->userId} not found or has no email. Cash winner email not sent.");

            return;
        }

        Mail::to($user->email)
            ->send(new WinnerCashNotificationMail($user, $this->prizeWon, $this->liveShow))
            ->from('winners@badabing.show', env('APP_NAME'));

        Log::info("WinnerCashNotificationMail sent to user ID {$user->id} with email {$user->email} for live show ID {$this->liveShow->id} and prize won: {$this->prizeWon}");
    }
}
