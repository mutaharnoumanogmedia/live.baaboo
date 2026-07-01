<?php

namespace App\Jobs;

use App\Models\UserLiveShow;
use App\Services\ShopifyDiscountService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class GenerateWinnerDiscountCodeJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $userId;

    public int $liveShowId;

    public int|string $shopifyPriceRuleId;
    

    public function __construct(int $userId, int $liveShowId, int|string $shopifyPriceRuleId)
    {
        $this->userId = $userId;
        $this->liveShowId = $liveShowId;
        $this->shopifyPriceRuleId = $shopifyPriceRuleId;
    }

    public function handle(): void
    {
        $winnerUser = UserLiveShow::where('user_id', $this->userId)
            ->where('live_show_id', $this->liveShowId)
            ->first();

        if (! $winnerUser) {
            Log::error("GenerateWinnerDiscountCodeJob: UserLiveShow not found for user ID {$this->userId}, live show ID {$this->liveShowId}");

            return;
        }

        if ($winnerUser->discount_code) {
            Log::info("GenerateWinnerDiscountCodeJob: user ID {$this->userId} already has discount code {$winnerUser->discount_code}");

            return;
        }

        try {
            $discountService = new ShopifyDiscountService;
            $discountCode = $discountService->setUserDiscountCode($this->shopifyPriceRuleId, $winnerUser);

            if (! $discountCode) {
                Log::error("GenerateWinnerDiscountCodeJob: failed to generate discount code for user ID {$this->userId}, live show ID {$this->liveShowId}");
            }
        } catch (\Exception $e) {
            Log::error("GenerateWinnerDiscountCodeJob failed for user ID {$this->userId}: ".$e->getMessage());
        }
    }
}
