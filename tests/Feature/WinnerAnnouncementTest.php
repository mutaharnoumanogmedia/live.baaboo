<?php

namespace Tests\Feature;

use App\Events\LiveShowAdminStateEvent;
use App\Events\ShowLiveShowWinnersTabEvent;
use App\Jobs\SendWinnerEmailJob;
use App\Models\LiveShow;
use App\Models\LiveShowWinnerPrize;
use App\Models\User;
use App\Models\UserLiveShow;
use App\Services\BrevoService;
use Database\Seeders\TestLiveShowSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Queue;
use Mockery;
use Tests\TestCase;

/**
 * Checklist: Winner announcements
 *
 *  1. Generate winners
 *  2. Send notification email for winners
 *  3. Send prize emails for winners
 *  4. Try to re-announce winners and send winner emails
 *
 * Cases 1, 2 and 4 hit LiveShowController::updateWinners / ::reupdateWinners
 * over HTTP. Case 3 runs SendWinnerEmailJob (dispatched by updateWinners) so
 * cash vs voucher prize emails are verified end-to-end.
 *
 * @see \App\Http\Controllers\Admin\LiveShowController::updateWinners
 * @see \App\Http\Controllers\Admin\LiveShowController::reupdateWinners
 * @see \App\Jobs\SendWinnerEmailJob
 */
class WinnerAnnouncementTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(TestLiveShowSeeder::class);
    }

    protected function tearDown(): void
    {
        Mockery::close();

        parent::tearDown();
    }

    private function admin(): User
    {
        return User::role('admin')->firstOrFail();
    }

    private function testShow(): LiveShow
    {
        return LiveShow::where('title', TestLiveShowSeeder::SHOW_TITLE)->firstOrFail();
    }

    /**
     * @param  bool  $voucher  true => voucher winner, false => cash winner
     */
    private function winnerPivot(LiveShow $show, bool $voucher): UserLiveShow
    {
        $prize = LiveShowWinnerPrize::where('live_show_id', $show->id)
            ->where('is_voucher', $voucher)
            ->firstOrFail();

        return UserLiveShow::where('live_show_id', $show->id)
            ->where('winner_prize_id', $prize->id)
            ->where('is_winner', true)
            ->firstOrFail();
    }

    private function alwaysSucceedingBrevo(string $messageId): BrevoService
    {
        $brevo = Mockery::mock(BrevoService::class);
        $brevo->shouldReceive('send')->andReturn([
            'success' => true,
            'status_code' => 201,
            'message_id' => $messageId,
            'message_ids' => null,
            'error' => null,
        ]);

        return $brevo;
    }

    /** @test */
    public function able_to_generate_winners(): void
    {
        Queue::fake();
        Event::fake([ShowLiveShowWinnersTabEvent::class, LiveShowAdminStateEvent::class]);

        $show = $this->testShow();

        $response = $this->actingAs($this->admin(), 'admin')
            ->postJson(route('admin.live-shows.update-winners', $show->id));

        $response->assertStatus(200);
        $response->assertJson(['success' => true, 'winners_announced' => true]);

        $this->assertTrue($show->fresh()->winners_announced);

        $winners = UserLiveShow::where('live_show_id', $show->id)
            ->where('is_winner', true)
            ->with('winnerPrize')
            ->get();
        $this->assertCount(TestLiveShowSeeder::TOTAL_PRIZES, $winners);

        $cash = $winners->filter(fn ($w) => $w->winnerPrize && ! $w->winnerPrize->is_voucher);
        $voucher = $winners->filter(fn ($w) => $w->winnerPrize && $w->winnerPrize->is_voucher);
        $this->assertCount(TestLiveShowSeeder::CASH_WINNER_RANKS, $cash);
        $this->assertCount(TestLiveShowSeeder::TOTAL_PRIZES - TestLiveShowSeeder::CASH_WINNER_RANKS, $voucher);

        $this->assertSame(
            TestLiveShowSeeder::EXTRA_PLAYERS,
            UserLiveShow::where('live_show_id', $show->id)->where('is_winner', false)->count()
        );

        Event::assertDispatched(ShowLiveShowWinnersTabEvent::class);
    }

    /** @test */
    public function able_to_send_notification_email_for_winners(): void
    {
        Queue::fake();
        Event::fake([ShowLiveShowWinnersTabEvent::class, LiveShowAdminStateEvent::class]);

        $show = $this->testShow();
        $expectedWinners = min(
            (int) $show->max_winners,
            $show->users()->wherePivot('status', 'registered')->count()
        );

        // Controller queues one delayed SendWinnerEmailJob per winner.
        $this->actingAs($this->admin(), 'admin')
            ->postJson(route('admin.live-shows.update-winners', $show->id))
            ->assertStatus(200);

        Queue::assertPushed(SendWinnerEmailJob::class, $expectedWinners);
        Queue::assertPushed(SendWinnerEmailJob::class, fn (SendWinnerEmailJob $job) => $job->liveShow->id === $show->id && $job->delay !== null);

        // Job execution records the generic notification email as sent.
        $winner = $this->winnerPivot($show->fresh(), voucher: false);
        (new SendWinnerEmailJob($winner->user_id, $winner->prize_won, $show))->handle($this->alwaysSucceedingBrevo('notify_msg'));

        $fresh = UserLiveShow::where('live_show_id', $show->id)
            ->where('user_id', $winner->user_id)
            ->firstOrFail();

        $this->assertStringContainsString('notify_msg', $fresh->winner_email_sent_status);
        $this->assertNotNull($fresh->winner_email_sent_at);
    }

    /** @test */
    public function able_to_send_prize_emails_for_winners(): void
    {
        $show = $this->testShow();

        // Cash winner -> cash prize email, never voucher.
        $cash = $this->winnerPivot($show, voucher: false);
        (new SendWinnerEmailJob($cash->user_id, $cash->prize_won, $show))->handle($this->alwaysSucceedingBrevo('cash_msg'));

        $cashFresh = UserLiveShow::where('live_show_id', $show->id)->where('user_id', $cash->user_id)->firstOrFail();
        $this->assertStringContainsString('cash_msg', $cashFresh->winner_cash_email_sent_status);
        $this->assertNotNull($cashFresh->winner_cash_email_sent_at);
        $this->assertNull($cashFresh->winner_voucher_email_sent_status);

        // Voucher winner -> voucher prize email, never cash.
        $voucher = $this->winnerPivot($show, voucher: true);
        (new SendWinnerEmailJob($voucher->user_id, $voucher->prize_won, $show))->handle($this->alwaysSucceedingBrevo('voucher_msg'));

        $voucherFresh = UserLiveShow::where('live_show_id', $show->id)->where('user_id', $voucher->user_id)->firstOrFail();
        $this->assertStringContainsString('voucher_msg', $voucherFresh->winner_voucher_email_sent_status);
        $this->assertNotNull($voucherFresh->winner_voucher_email_sent_at);
        $this->assertNull($voucherFresh->winner_cash_email_sent_status);
    }

    /** @test */
    public function trying_to_reannounce_winners_is_blocked_and_regeneration_does_not_resend_emails(): void
    {
        Queue::fake();
        Event::fake([ShowLiveShowWinnersTabEvent::class, LiveShowAdminStateEvent::class]);

        $show = $this->testShow();

        // First announcement succeeds and schedules winner emails.
        $this->actingAs($this->admin(), 'admin')
            ->postJson(route('admin.live-shows.update-winners', $show->id))
            ->assertStatus(200);

        $emailsAfterAnnounce = Queue::pushed(SendWinnerEmailJob::class)->count();
        $this->assertGreaterThan(0, $emailsAfterAnnounce);

        // Second announcement via updateWinners is rejected.
        $this->actingAs($this->admin(), 'admin')
            ->postJson(route('admin.live-shows.update-winners', $show->id))
            ->assertStatus(422)
            ->assertJson(['success' => false, 'winners_announced' => true]);

        // Regeneration via reupdateWinners recomputes winners but does NOT
        // re-queue winner notification emails.
        $this->actingAs($this->admin(), 'admin')
            ->postJson(route('admin.live-shows.reupdate-winners', $show->id))
            ->assertStatus(200);

        $this->assertSame(
            $emailsAfterAnnounce,
            Queue::pushed(SendWinnerEmailJob::class)->count(),
            'Re-announcing winners must not schedule additional winner emails.'
        );

        $this->assertTrue($show->fresh()->winners_announced);
    }
}
