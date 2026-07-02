<?php

namespace App\Jobs;

use App\Mail\WinnerVoucherNotificationMail;
use App\Models\User;
use App\Models\UserLiveShow;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class SendWinnerVoucherEmailJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */
    public User $user;

    public UserLiveShow $show_user;

    public function __construct(User $user, UserLiveShow $show_user)
    {
        $this->user = $user;
        $this->show_user = $show_user;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        if ($this->show_user && $this->show_user->discount_code != null) {

            try {
                Mail::to($this->user->email)
                    ->send(new WinnerVoucherNotificationMail($this->show_user));
                $this->show_user->winner_voucher_email_sent_at = now();
                $this->show_user->save();

                Log::info("WinnerVoucherNotificationMail dispatched to user ID {$this->user->id} with email {$this->user->email} for live show ID {$this->show_user->live_show_id} and prize won: {$this->show_user->prize_won}");
            } catch (\Exception $e) {
                Log::error("Failed to dispatch WinnerVoucherNotificationMail for user ID {$this->user->id}: ".$e->getMessage().' '.now()->format('d M Y, H:i:s'));
            }
        } else {
            Log::warning("No discount code found for user ID {$this->user->id} in live show ID {$this->show_user->live_show_id}. WinnerVoucherNotificationMail not dispatched.");
        }
    }
}
