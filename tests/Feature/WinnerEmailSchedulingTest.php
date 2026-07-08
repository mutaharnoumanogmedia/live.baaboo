<?php

namespace Tests\Feature;

use App\Events\LiveShowAdminStateEvent;
use App\Events\ShowLiveShowWinnersTabEvent;
use App\Jobs\SendWinnerEmailJob;
use App\Models\LiveShow;
use App\Models\User;
use App\Models\UserLiveShow;
use Database\Seeders\TestLiveShowSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

/**
 * Covers the "announce winners" flow on the stream-management screen:
 * hitting the update-winners endpoint must schedule a winner notification
 * email for every winner (dispatched as a delayed queue job) exactly once,
 * and assign the correct prize type per rank (top three cash, the rest
 * vouchers).
 *
 * These tests run against the {@see TestLiveShowSeeder} fixture: a single show
 * with 10 prizes (3 cash + 7 voucher), 10 questions and 10 scoring players.
 *
 * @see \App\Http\Controllers\Admin\LiveShowController::updateWinners
 */
class WinnerEmailSchedulingTest extends TestCase
{
    /**
     * Rebuild a fresh schema on the isolated in-memory SQLite connection
     * (configured in phpunit.xml) before every test and roll it back after,
     * so the real MySQL database is never touched.
     */
    use RefreshDatabase;

    /**
     * Seed the winner-email fixture.
     *
     * Seeding is done here rather than via the {@see $seed}/{@see $seeder}
     * properties on purpose: RefreshDatabase only auto-seeds during the one-off
     * "migrate:fresh" step, which is tied to whichever test happens to migrate
     * first in the whole suite. Seeding in setUp() (inside the per-test
     * transaction that RefreshDatabase opens) guarantees the data exists for
     * every test regardless of execution order, and it is rolled back after
     * each test.
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(TestLiveShowSeeder::class);
    }

    private function admin(): User
    {
        return User::role('admin')->firstOrFail();
    }

    /**
     * The single show created by the fixture (not yet announced).
     */
    private function testShow(): LiveShow
    {
        return LiveShow::where('title', TestLiveShowSeeder::SHOW_TITLE)->firstOrFail();
    }

    /** @test */
    public function announcing_winners_schedules_a_winner_email_for_each_winner(): void
    {
        Queue::fake();
        Event::fake([ShowLiveShowWinnersTabEvent::class, LiveShowAdminStateEvent::class]);

        $show = $this->testShow();

        // The endpoint emails the top `max_winners` registered players, so the
        // number of scheduled emails is bounded by however many are registered.
        $registeredCount = $show->users()->wherePivot('status', 'registered')->count();
        $expectedWinners = min((int) $show->max_winners, $registeredCount);

        $response = $this->actingAs($this->admin(), 'admin')
            ->postJson(route('admin.live-shows.update-winners', $show->id));

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'winners_announced' => true,
        ]);

        // The show is flagged as announced.
        $this->assertTrue($show->fresh()->winners_announced);

        // One winner email is scheduled per winner.
        Queue::assertPushed(SendWinnerEmailJob::class, $expectedWinners);

        // Every scheduled job targets this show and is a *delayed* (scheduled)
        // dispatch rather than an immediate one.
        Queue::assertPushed(SendWinnerEmailJob::class, function (SendWinnerEmailJob $job) use ($show) {
            return $job->liveShow->id === $show->id
                && $job->delay !== null;
        });

        // The winners tab and admin-state sync events are broadcast.
        Event::assertDispatched(ShowLiveShowWinnersTabEvent::class);
        Event::assertDispatched(LiveShowAdminStateEvent::class);
    }

    /** @test */
    public function announced_winners_get_the_expected_cash_and_voucher_prize_split(): void
    {
        Queue::fake();
        Event::fake([ShowLiveShowWinnersTabEvent::class, LiveShowAdminStateEvent::class]);

        $show = $this->testShow();

        $this->actingAs($this->admin(), 'admin')
            ->postJson(route('admin.live-shows.update-winners', $show->id))
            ->assertStatus(200);

        // After announcing, exactly the top three winners hold cash prizes and
        // the remaining winners hold voucher prizes — mirroring how the winner
        // emails will later be routed (cash vs voucher).
        $winners = UserLiveShow::where('live_show_id', $show->id)
            ->where('is_winner', true)
            ->with('winnerPrize')
            ->get();

        $this->assertCount(TestLiveShowSeeder::TOTAL_PRIZES, $winners);

        $cashWinners = $winners->filter(fn ($w) => $w->winnerPrize && ! $w->winnerPrize->is_voucher);
        $voucherWinners = $winners->filter(fn ($w) => $w->winnerPrize && $w->winnerPrize->is_voucher);

        $this->assertCount(TestLiveShowSeeder::CASH_WINNER_RANKS, $cashWinners);
        $this->assertCount(
            TestLiveShowSeeder::TOTAL_PRIZES - TestLiveShowSeeder::CASH_WINNER_RANKS,
            $voucherWinners
        );
    }

    /** @test */
    public function winners_cannot_be_announced_twice_and_no_extra_emails_are_scheduled(): void
    {
        Queue::fake();
        Event::fake([ShowLiveShowWinnersTabEvent::class, LiveShowAdminStateEvent::class]);

        // Simulate a show whose winners were already announced.
        $show = $this->testShow();
        $show->update(['winners_announced' => true]);

        $response = $this->actingAs($this->admin(), 'admin')
            ->postJson(route('admin.live-shows.update-winners', $show->id));

        $response->assertStatus(422);
        $response->assertJson([
            'success' => false,
            'winners_announced' => true,
        ]);

        Queue::assertNotPushed(SendWinnerEmailJob::class);
    }

    /** @test */
    public function announcing_winners_requires_authentication(): void
    {
        Queue::fake();

        $show = $this->testShow();

        // Unauthenticated request is rejected by the auth middleware and
        // must not schedule any emails.
        $this->postJson(route('admin.live-shows.update-winners', $show->id))
            ->assertStatus(401);

        Queue::assertNotPushed(SendWinnerEmailJob::class);
    }
}
