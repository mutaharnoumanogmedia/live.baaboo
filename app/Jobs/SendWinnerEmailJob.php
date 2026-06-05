<?php

namespace App\Jobs;

use App\Mail\WinnerNotificationMail;
use App\Models\LiveShow;
use App\Models\User;
use App\Models\UserLiveShow;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

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

    public function handle(): void
    {
        $user = User::find($this->userId);
        $show_user = UserLiveShow::where('live_show_id', $this->liveShow->id)
            ->where('user_id', $this->userId)
            ->first();

        if (! $user || ! $user->email) {
            return;
        }
        if ($show_user && $show_user->discount_code != NULL) {
            SendWinnerVoucherEmailJob::dispatch($user,$show_user)->delay(now()->addMinutes(30));

            // Mail::to($user->email)
            //     ->send(new WinnerVoucherNotificationMail($show_user));
            Log::info("WinnerVoucherNotificationMail dispatched to user ID {$user->id} with email {$user->email} for live show ID {$this->liveShow->id} and prize won: {$this->prizeWon}");
        } else {

            Mail::to($user->email)
                ->send(new WinnerNotificationMail($user, $this->prizeWon, $this->liveShow));
        }
    }
}
