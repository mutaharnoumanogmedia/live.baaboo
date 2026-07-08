<?php

namespace Tests\E2E;

use App\Events\LiveShowAdminStateEvent;
use App\Events\ShowLiveShowWinnersTabEvent;
use App\Jobs\SendWinnerEmailJob;
use App\Models\LiveShow;
use App\Models\LiveShowWinnerPrize;
use App\Models\User;
use App\Models\UserLiveShow;
use App\Services\BrevoService;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Queue;
use Mockery;

/**
 * Step 4 of the E2E chain: winner generation and emails.
 */
class WinnerAnnouncementTest extends E2ETestCase
{
    protected function tearDown(): void
    {
        Mockery::close();

        parent::tearDown();
    }

    /**
     * @test
     *
     * @depends Tests\E2E\LiveShowJoinTest::test_joined_players_receive_scores_for_winner_ranking
     */
    public function test_winners_are_generated_and_prizes_assigned(): void
    {
        Queue::fake();
        Event::fake([ShowLiveShowWinnersTabEvent::class, LiveShowAdminStateEvent::class]);

        $show = LiveShow::findOrFail(E2EContext::$liveShowId);
        $admin = User::findOrFail(E2EContext::$adminUserId);

        $response = $this->actingAs($admin, 'admin')
            ->postJson(route('admin.live-shows.update-winners', $show->id));

        $response->assertStatus(200);
        $response->assertJson(['success' => true, 'winners_announced' => true]);

        $this->assertTrue($show->fresh()->winners_announced);

        $winners = UserLiveShow::where('live_show_id', $show->id)
            ->where('is_winner', true)
            ->with('winnerPrize')
            ->get();

        $this->assertCount(3, $winners);

        $cashWinner = $winners->first(fn ($w) => $w->winnerPrize && ! $w->winnerPrize->is_voucher);
        $voucherWinner = $winners->first(fn ($w) => $w->winnerPrize && $w->winnerPrize->is_voucher);

        $this->assertNotNull($cashWinner);
        $this->assertNotNull($voucherWinner);

        E2EContext::$topCashWinnerUserId = $cashWinner->user_id;
        E2EContext::$topVoucherWinnerUserId = $voucherWinner->user_id;

        Queue::assertPushed(SendWinnerEmailJob::class, 3);
        Queue::assertPushed(SendWinnerEmailJob::class, fn (SendWinnerEmailJob $job) => $job->liveShow->id === $show->id && $job->delay !== null);

        Event::assertDispatched(ShowLiveShowWinnersTabEvent::class);
    }

    /**
     * @test
     *
     * @depends test_winners_are_generated_and_prizes_assigned
     */
    public function test_winner_notification_emails_are_queued(): void
    {
        $show = LiveShow::findOrFail(E2EContext::$liveShowId);

        $winners = UserLiveShow::where('live_show_id', $show->id)
            ->where('is_winner', true)
            ->get();

        $this->assertCount(3, $winners);
        $this->assertTrue($show->fresh()->winners_announced);

        foreach ($winners as $winner) {
            $this->assertNotNull($winner->prize_won);
            $this->assertNotNull($winner->winner_prize_id);
        }
    }

    /**
     * @test
     *
     * @depends test_winner_notification_emails_are_queued
     */
    public function test_winner_notification_email_is_sent_when_the_job_runs(): void
    {
        $show = LiveShow::findOrFail(E2EContext::$liveShowId);
        $pivot = UserLiveShow::where('live_show_id', $show->id)
            ->where('user_id', E2EContext::$topCashWinnerUserId)
            ->firstOrFail();

        // One handle() sends the generic email and the cash prize email.
        $brevo = Mockery::mock(BrevoService::class);
        $brevo->shouldReceive('send')->twice()->andReturn([
            'success' => true,
            'status_code' => 201,
            'message_id' => 'e2e_notify_msg',
            'message_ids' => null,
            'error' => null,
        ]);

        (new SendWinnerEmailJob($pivot->user_id, $pivot->prize_won, $show))->handle($brevo);

        $fresh = $pivot->fresh();
        $this->assertStringContainsString('e2e_notify_msg', $fresh->winner_email_sent_status);
        $this->assertStringContainsString('e2e_notify_msg', $fresh->winner_cash_email_sent_status);
        $this->assertNotNull($fresh->winner_email_sent_at);
        $this->assertNotNull($fresh->winner_cash_email_sent_at);
    }

    /**
     * @test
     *
     * @depends test_winner_notification_email_is_sent_when_the_job_runs
     */
    public function test_prize_emails_are_sent_for_cash_and_voucher_winners(): void
    {
        $show = LiveShow::findOrFail(E2EContext::$liveShowId);

        // Cash + generic emails were asserted in the previous step.
        $cash = UserLiveShow::where('live_show_id', $show->id)
            ->where('user_id', E2EContext::$topCashWinnerUserId)
            ->firstOrFail();

        $this->assertStringContainsString('e2e_notify_msg', $cash->winner_cash_email_sent_status);
        $this->assertNull($cash->winner_voucher_email_sent_status);

        $voucher = UserLiveShow::where('live_show_id', $show->id)
            ->where('user_id', E2EContext::$topVoucherWinnerUserId)
            ->firstOrFail();

        // Voucher email path requires a discount code on the pivot.
        if (! $voucher->discount_code) {
            $voucher->update(['discount_code' => 'E2EVOUCHERSTUB']);
            $voucher->refresh();
        }

        $brevo = Mockery::mock(BrevoService::class);
        $brevo->shouldReceive('send')->twice()->andReturn([
            'success' => true,
            'status_code' => 201,
            'message_id' => 'e2e_voucher_msg',
            'message_ids' => null,
            'error' => null,
        ]);

        (new SendWinnerEmailJob($voucher->user_id, $voucher->prize_won, $show))->handle($brevo);

        $voucherFresh = $voucher->fresh();
        $this->assertStringContainsString('e2e_voucher_msg', $voucherFresh->winner_email_sent_status);
        $this->assertStringContainsString('e2e_voucher_msg', $voucherFresh->winner_voucher_email_sent_status);
        $this->assertNull($voucherFresh->winner_cash_email_sent_status);
    }

    /**
     * @test
     *
     * @depends test_prize_emails_are_sent_for_cash_and_voucher_winners
     */
    public function test_winners_cannot_be_reannounced_but_can_be_regenerated(): void
    {
        Queue::fake();
        Event::fake([ShowLiveShowWinnersTabEvent::class, LiveShowAdminStateEvent::class]);

        $show = LiveShow::findOrFail(E2EContext::$liveShowId);
        $admin = User::findOrFail(E2EContext::$adminUserId);

        $this->actingAs($admin, 'admin')
            ->postJson(route('admin.live-shows.update-winners', $show->id))
            ->assertStatus(422)
            ->assertJson(['success' => false, 'winners_announced' => true]);

        Queue::assertNotPushed(SendWinnerEmailJob::class);

        $this->actingAs($admin, 'admin')
            ->postJson(route('admin.live-shows.reupdate-winners', $show->id))
            ->assertStatus(200)
            ->assertJson(['success' => true, 'winners_announced' => true]);

        Queue::assertNotPushed(SendWinnerEmailJob::class);
    }
}
