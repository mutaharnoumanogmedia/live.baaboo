<?php

namespace Tests\Feature;

use App\Jobs\SendWinnerEmailJob;
use App\Models\LiveShow;
use App\Models\LiveShowWinnerPrize;
use App\Models\User;
use App\Models\UserLiveShow;
use App\Services\BrevoService;
use Database\Seeders\TestLiveShowSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Mockery;
use Tests\TestCase;

/**
 * Verifies what happens when a scheduled winner email job actually runs:
 * it must push the notification through {@see BrevoService}, persist the
 * returned message id, and never re-send an email that was already handled.
 *
 * The Brevo API itself is never touched here: BrevoService is swapped for a
 * Mockery double so we assert the job's own behaviour in isolation.
 *
 * The fixture ({@see TestLiveShowSeeder}) builds a single show whose winners
 * mirror reality: ranks 1-3 are cash winners (prize is_voucher = 0, no discount
 * code) and ranks 4-10 are voucher winners (prize is_voucher = 1, discount code
 * set). This lets us assert the job routes to the right emails per winner type,
 * exactly as {@see \App\Jobs\SendWinnerEmailJob::handle()} does in production.
 */
class SendWinnerEmailJobTest extends TestCase
{
    /**
     * Rebuild a fresh schema on the isolated in-memory SQLite connection
     * (configured in phpunit.xml) before every test and roll it back after,
     * so the real MySQL database is never touched.
     */
    use RefreshDatabase;

    /**
     * Seed the winner-email fixture (roles, admin, a fully-formed show with 10
     * prizes / 10 questions, and pre-marked cash + voucher winners).
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

    protected function tearDown(): void
    {
        Mockery::close();

        parent::tearDown();
    }

    /**
     * The single show created by the fixture.
     */
    private function testShow(): LiveShow
    {
        return LiveShow::where('title', TestLiveShowSeeder::SHOW_TITLE)->firstOrFail();
    }

    /**
     * The winner pivot for the given prize type on the fixture show.
     *
     * @param  bool  $voucher  true => a voucher winner, false => a cash winner
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

    /**
     * A Brevo double that always reports a successful send with the given id.
     */
    private function successfulBrevo(string $messageId, int $times): BrevoService
    {
        $brevo = Mockery::mock(BrevoService::class);
        $brevo->shouldReceive('send')
            ->times($times)
            ->andReturn([
                'success' => true,
                'status_code' => 201,
                'message_id' => $messageId,
                'message_ids' => null,
                'error' => null,
            ]);

        return $brevo;
    }

    /**
     * Pull a non-winning participant of the fixture show. The job only sends
     * the single generic winner email for such a user (no cash/voucher
     * follow-up), which keeps the Brevo mock's "send" expectations unambiguous.
     *
     * @return array{show: LiveShow, user: User, pivot: UserLiveShow}
     */
    private function winnerSetup(): array
    {
        $show = $this->testShow();

        $user = $show->users()->wherePivot('is_winner', false)->firstOrFail();

        $pivot = UserLiveShow::query()
            ->where('live_show_id', $show->id)
            ->where('user_id', $user->id)
            ->first();

        return ['show' => $show, 'user' => $user, 'pivot' => $pivot];
    }

    /** @test */
    public function it_sends_the_winner_email_and_records_the_message_id(): void
    {
        ['show' => $show, 'user' => $user] = $this->winnerSetup();

        $brevo = Mockery::mock(BrevoService::class);
        $brevo->shouldReceive('send')
            ->once()
            ->andReturn([
                'success' => true,
                'status_code' => 201,
                'message_id' => 'msg_123',
                'message_ids' => null,
                'error' => null,
            ]);

        (new SendWinnerEmailJob($user->id, '100', $show))->handle($brevo);

        $pivot = UserLiveShow::query()
            ->where('live_show_id', $show->id)
            ->where('user_id', $user->id)
            ->first();

        $this->assertStringContainsString('msg_123', $pivot->winner_email_sent_status);
        $this->assertNotNull($pivot->winner_email_sent_at);
    }

    /** @test */
    public function a_brevo_failure_is_recorded_and_the_email_is_not_marked_as_sent(): void
    {
        ['show' => $show, 'user' => $user] = $this->winnerSetup();

        $brevo = Mockery::mock(BrevoService::class);
        $brevo->shouldReceive('send')
            ->once()
            ->andReturn([
                'success' => false,
                'status_code' => 400,
                'message_id' => null,
                'message_ids' => null,
                'error' => 'The email address is invalid',
            ]);

        (new SendWinnerEmailJob($user->id, '100', $show))->handle($brevo);

        $pivot = UserLiveShow::query()
            ->where('live_show_id', $show->id)
            ->where('user_id', $user->id)
            ->first();

        $this->assertStringStartsWith('failed:', $pivot->winner_email_sent_status);
        $this->assertStringContainsString('The email address is invalid', $pivot->winner_email_sent_status);
        $this->assertNull($pivot->winner_email_sent_at);
    }

    /** @test */
    public function it_does_not_resend_an_email_that_was_already_handled(): void
    {
        ['show' => $show, 'user' => $user, 'pivot' => $pivot] = $this->winnerSetup();

        // Simulate a previously successful send.
        $pivot->winner_email_sent_status = ',already_sent_id';
        $pivot->save();

        $brevo = Mockery::mock(BrevoService::class);
        $brevo->shouldReceive('send')->never();

        (new SendWinnerEmailJob($user->id, '100', $show))->handle($brevo);

        $fresh = UserLiveShow::query()
            ->where('live_show_id', $show->id)
            ->where('user_id', $user->id)
            ->first();

        $this->assertSame(',already_sent_id', $fresh->winner_email_sent_status);
    }

    /** @test */
    public function it_does_nothing_when_the_user_is_not_a_participant_of_the_show(): void
    {
        $show = LiveShow::has('users')->firstOrFail();

        // An existing user who is deliberately NOT attached to this show.
        $participantIds = $show->users()->pluck('users.id')->all();
        $user = User::whereNotIn('id', $participantIds)->firstOrFail();

        $brevo = Mockery::mock(BrevoService::class);
        $brevo->shouldReceive('send')->never();

        (new SendWinnerEmailJob($user->id, '100', $show))->handle($brevo);

        $this->assertDatabaseMissing('user_live_shows', [
            'live_show_id' => $show->id,
            'user_id' => $user->id,
        ]);
    }

    /** @test */
    public function a_cash_winner_receives_the_generic_and_cash_emails_only(): void
    {
        $show = $this->testShow();
        $pivot = $this->winnerPivot($show, voucher: false);

        // Generic winner email + cash winner email = 2 sends. No voucher email,
        // because a cash winner has no discount code.
        $brevo = $this->successfulBrevo('cash_msg', times: 2);

        (new SendWinnerEmailJob($pivot->user_id, $pivot->prize_won, $show))->handle($brevo);

        $fresh = UserLiveShow::query()
            ->where('live_show_id', $show->id)
            ->where('user_id', $pivot->user_id)
            ->first();

        $this->assertStringContainsString('cash_msg', $fresh->winner_email_sent_status);
        $this->assertStringContainsString('cash_msg', $fresh->winner_cash_email_sent_status);
        $this->assertNotNull($fresh->winner_email_sent_at);
        $this->assertNotNull($fresh->winner_cash_email_sent_at);

        // A cash winner never gets the voucher email.
        $this->assertNull($fresh->winner_voucher_email_sent_status);
        $this->assertNull($fresh->winner_voucher_email_sent_at);
    }

    /** @test */
    public function a_voucher_winner_receives_the_generic_and_voucher_emails_only(): void
    {
        $show = $this->testShow();
        $pivot = $this->winnerPivot($show, voucher: true);

        // Generic winner email + voucher winner email = 2 sends. No cash email,
        // because the winner's prize is a voucher (is_voucher = 1).
        $brevo = $this->successfulBrevo('voucher_msg', times: 2);

        (new SendWinnerEmailJob($pivot->user_id, $pivot->prize_won, $show))->handle($brevo);

        $fresh = UserLiveShow::query()
            ->where('live_show_id', $show->id)
            ->where('user_id', $pivot->user_id)
            ->first();

        $this->assertStringContainsString('voucher_msg', $fresh->winner_email_sent_status);
        $this->assertStringContainsString('voucher_msg', $fresh->winner_voucher_email_sent_status);
        $this->assertNotNull($fresh->winner_email_sent_at);
        $this->assertNotNull($fresh->winner_voucher_email_sent_at);

        // A voucher winner never gets the cash email.
        $this->assertNull($fresh->winner_cash_email_sent_status);
        $this->assertNull($fresh->winner_cash_email_sent_at);
    }
}
