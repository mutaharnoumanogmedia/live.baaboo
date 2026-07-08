<?php

namespace Tests\Feature\Controllers;

use App\Http\Controllers\Admin\LiveShowController;
use App\Jobs\GenerateWinnerDiscountCodeJob;
use App\Jobs\SendWinnerEmailJob;
use App\Models\LiveShow;
use App\Models\LiveShowWinnerPrize;
use App\Models\ShopifyPriceRule;
use App\Models\User;
use App\Models\UserLiveShow;
use App\Services\BrevoService;
use Carbon\Carbon;
use Database\Seeders\PermissionSeeder;
use Database\Seeders\RoleSeeder;
use Database\Seeders\TestLiveShowSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Queue;
use Mockery;
use Tests\Feature\Controllers\Concerns\AssertsDirectControllerResponses;
use Tests\TestCase;

/**
 * Direct controller calls for admin live-show lifecycle and winner flows.
 *
 * @see \App\Http\Controllers\Admin\LiveShowController::store
 * @see \App\Http\Controllers\Admin\LiveShowController::update
 * @see \App\Http\Controllers\Admin\LiveShowController::updateWinners
 * @see \App\Http\Controllers\Admin\LiveShowController::reupdateWinners
 */
class LiveShowControllerDirectTest extends TestCase
{
    use AssertsDirectControllerResponses;
    use RefreshDatabase;

    private LiveShowController $controller;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(PermissionSeeder::class);
        $this->seed(RoleSeeder::class);

        $this->controller = new LiveShowController;
    }

    protected function tearDown(): void
    {
        Mockery::close();

        parent::tearDown();
    }

    /** @test */
    public function store_returns_show_redirect_for_cash_and_voucher_prizes(): void
    {
        Http::fake([
            '*/price_rules.json' => Http::sequence()
                ->push($this->fakePriceRule(111), 201)
                ->push($this->fakePriceRule(112), 201),
        ]);

        $admin = $this->admin();
        Auth::login($admin);

        $request = Request::create('/admin/live-shows', 'POST', $this->storePayload([
            'title' => 'Direct Cash And Voucher Show',
            'max_winners' => 4,
            'is_test_show' => 0,
            'winner_prizes' => [
                1 => '1000.00',
                2 => '500.00',
                3 => '50.00',
                4 => '30.00',
            ],
            'winner_voucher' => [3 => 1, 4 => 1],
            'winner_voucher_amount' => [3 => 50, 4 => 30],
        ]));

        $response = $this->controller->store($request);
        $show = LiveShow::where('title', 'Direct Cash And Voucher Show')->firstOrFail();

        $this->assertRedirectResponse($response, route('admin.live-shows.show', $show->id));

        $prizes = LiveShowWinnerPrize::where('live_show_id', $show->id)->orderBy('rank')->get();
        $this->assertCount(4, $prizes);
        $this->assertFalse((bool) $prizes[0]->is_voucher);
        $this->assertTrue((bool) $prizes[2]->is_voucher);
        $this->assertNotNull($prizes[2]->discount_rule_id);
    }

    /** @test */
    public function store_allows_two_shows_at_the_same_scheduled_time(): void
    {
        $admin = $this->admin();
        Auth::login($admin);

        $scheduledAt = '2026-09-15 20:00:00';

        $first = Request::create('/admin/live-shows', 'POST', $this->storePayload([
            'title' => 'Direct Simultaneous A',
            'scheduled_at' => $scheduledAt,
        ]));
        $this->controller->store($first);

        $second = Request::create('/admin/live-shows', 'POST', $this->storePayload([
            'title' => 'Direct Simultaneous B',
            'scheduled_at' => $scheduledAt,
        ]));
        $this->controller->store($second);

        $this->assertSame(2, LiveShow::whereIn('title', ['Direct Simultaneous A', 'Direct Simultaneous B'])
            ->where('scheduled_at', Carbon::parse($scheduledAt))
            ->count());
    }

    /** @test */
    public function update_returns_edit_redirect_with_updated_fields(): void
    {
        $show = $this->createShow(['title' => 'Original Title', 'status' => 'scheduled']);
        Auth::login($this->admin());

        $request = Request::create('/admin/live-shows/'.$show->id, 'PUT', [
            'title' => 'Updated Title',
            'description' => 'Updated description.',
            'scheduled_at' => '2026-10-10 19:30:00',
            'status' => 'live',
            'max_players' => 250,
            'chat_enabled' => 0,
            'is_test_show' => 0,
        ]);

        $response = $this->controller->update($request, $show);

        $this->assertRedirectResponse($response, route('admin.live-shows.edit', $show->id));
        $this->assertDatabaseHas('live_shows', [
            'id' => $show->id,
            'title' => 'Updated Title',
            'status' => 'live',
            'max_players' => 250,
        ]);
    }

    /** @test */
    public function update_winners_returns_expected_json_and_dispatches_voucher_code_job(): void
    {
        Queue::fake();

        $admin = $this->admin();
        Auth::login($admin);

        $show = $this->createShow([
            'is_test_show' => false,
            'max_winners' => 1,
            'status' => 'completed',
            'winners_announced' => false,
        ]);

        $priceRule = ShopifyPriceRule::create([
            'shopify_id' => 987654321,
            'title' => 'BADABING Test Voucher Rule',
            'type' => 'fixed_amount',
            'value' => -50.0,
            'usage_limit' => 1,
            'starts_at' => now(),
            'ends_at' => now()->addDays(31),
            'active' => true,
        ]);

        LiveShowWinnerPrize::create([
            'live_show_id' => $show->id,
            'rank' => 1,
            'prize' => '50.00',
            'is_voucher' => true,
            'voucher_amount' => 50,
            'discount_rule_id' => $priceRule->id,
        ]);

        $winner = User::create([
            'name' => 'Voucher Winner',
            'email' => 'voucher.winner@example.com',
            'password' => bcrypt('secret-password'),
        ]);
        $winner->assignRole('user');

        $show->users()->attach($winner->id, [
            'score' => 1500,
            'status' => 'registered',
            'is_online' => false,
            'is_winner' => false,
            'discount_code' => null,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $request = Request::create('/fake', 'POST');
        $data = $this->assertJsonResponse(
            $this->controller->updateWinners($request, $show->id),
            200,
            ['success' => true, 'winners_announced' => true]
        );

        $this->assertArrayHasKey('winnerUsers', $data);
        $this->assertTrue($show->fresh()->winners_announced);

        Queue::assertPushed(GenerateWinnerDiscountCodeJob::class, function (GenerateWinnerDiscountCodeJob $job) use ($show, $winner, $priceRule) {
            return $job->userId === $winner->id
                && $job->liveShowId === $show->id
                && (string) $job->shopifyPriceRuleId === (string) $priceRule->shopify_id;
        });

        Queue::assertPushed(SendWinnerEmailJob::class);
    }

    /** @test */
    public function update_winners_returns_expected_json_for_fixture_show(): void
    {
        $this->seed(TestLiveShowSeeder::class);
        Queue::fake();
        Event::fake();

        Auth::login(User::role('admin')->firstOrFail());

        $show = LiveShow::where('title', TestLiveShowSeeder::SHOW_TITLE)->firstOrFail();
        $request = Request::create('/fake', 'POST');

        $this->assertJsonResponse(
            $this->controller->updateWinners($request, $show->id),
            200,
            ['success' => true, 'winners_announced' => true]
        );

        $this->assertCount(
            TestLiveShowSeeder::TOTAL_PRIZES,
            UserLiveShow::where('live_show_id', $show->id)->where('is_winner', true)->get()
        );
    }

    /** @test */
    public function update_winners_returns_422_when_winners_already_announced(): void
    {
        $this->seed(TestLiveShowSeeder::class);
        Queue::fake();

        Auth::login(User::role('admin')->firstOrFail());

        $show = LiveShow::where('title', TestLiveShowSeeder::SHOW_TITLE)->firstOrFail();
        $show->update(['winners_announced' => true]);

        $request = Request::create('/fake', 'POST');

        $this->assertJsonResponse(
            $this->controller->updateWinners($request, $show->id),
            422,
            ['success' => false, 'winners_announced' => true]
        );

        Queue::assertNotPushed(SendWinnerEmailJob::class);
    }

    /** @test */
    public function reupdate_winners_returns_success_without_queueing_extra_emails(): void
    {
        $this->seed(TestLiveShowSeeder::class);
        Queue::fake();
        Event::fake();

        Auth::login(User::role('admin')->firstOrFail());

        $show = LiveShow::where('title', TestLiveShowSeeder::SHOW_TITLE)->firstOrFail();
        $request = Request::create('/fake', 'POST');

        $this->controller->updateWinners($request, $show->id);
        $emailsAfterAnnounce = Queue::pushed(SendWinnerEmailJob::class)->count();
        $this->assertGreaterThan(0, $emailsAfterAnnounce);

        $this->assertJsonResponse(
            $this->controller->updateWinners($request, $show->id),
            422,
            ['success' => false, 'winners_announced' => true]
        );

        $this->assertJsonResponse(
            $this->controller->reupdateWinners($request, $show->id),
            200,
            ['success' => true, 'winners_announced' => true]
        );

        $this->assertSame($emailsAfterAnnounce, Queue::pushed(SendWinnerEmailJob::class)->count());
    }

    /** @test */
    public function send_winner_email_job_records_notification_and_prize_emails(): void
    {
        $this->seed(TestLiveShowSeeder::class);

        $show = LiveShow::where('title', TestLiveShowSeeder::SHOW_TITLE)->firstOrFail();

        $cashPrize = LiveShowWinnerPrize::where('live_show_id', $show->id)->where('is_voucher', false)->firstOrFail();
        $cash = UserLiveShow::where('live_show_id', $show->id)->where('winner_prize_id', $cashPrize->id)->firstOrFail();

        $voucherPrize = LiveShowWinnerPrize::where('live_show_id', $show->id)->where('is_voucher', true)->firstOrFail();
        $voucher = UserLiveShow::where('live_show_id', $show->id)->where('winner_prize_id', $voucherPrize->id)->firstOrFail();

        $brevo = Mockery::mock(BrevoService::class);
        $brevo->shouldReceive('send')->andReturn([
            'success' => true,
            'status_code' => 201,
            'message_id' => 'msg',
            'message_ids' => null,
            'error' => null,
        ]);

        (new SendWinnerEmailJob($cash->user_id, $cash->prize_won, $show))->handle($brevo);
        (new SendWinnerEmailJob($voucher->user_id, $voucher->prize_won, $show))->handle($brevo);

        $cashFresh = UserLiveShow::where('live_show_id', $show->id)->where('user_id', $cash->user_id)->firstOrFail();
        $voucherFresh = UserLiveShow::where('live_show_id', $show->id)->where('user_id', $voucher->user_id)->firstOrFail();

        $this->assertNotNull($cashFresh->winner_email_sent_at);
        $this->assertNotNull($cashFresh->winner_cash_email_sent_at);
        $this->assertNull($cashFresh->winner_voucher_email_sent_status);

        $this->assertNotNull($voucherFresh->winner_email_sent_at);
        $this->assertNotNull($voucherFresh->winner_voucher_email_sent_at);
        $this->assertNull($voucherFresh->winner_cash_email_sent_status);
    }

    private function admin(): User
    {
        $admin = User::create([
            'name' => 'Admin',
            'email' => 'admin'.uniqid().'@example.com',
            'password' => bcrypt('secret-password'),
        ]);
        $admin->assignRole('admin');

        return $admin;
    }

    /**
     * @return array<string, mixed>
     */
    private function storePayload(array $overrides = []): array
    {
        return array_merge([
            'title' => 'New Live Show',
            'description' => 'A freshly created show.',
            'scheduled_at' => '2026-08-01 18:00:00',
            'status' => 'scheduled',
            'host_name' => 'Host',
            'prize_amount' => 1000,
            'currency' => 'EUR',
            'max_winners' => 1,
            'max_players' => 100,
            'chat_enabled' => 1,
            'is_test_show' => 1,
            'winner_prizes' => [1 => '500.00'],
        ], $overrides);
    }

    /**
     * @param  array<string, mixed>  $overrides
     */
    private function createShow(array $overrides = []): LiveShow
    {
        $airedAt = Carbon::now()->addDay()->setTime(18, 0);

        $owner = User::create([
            'name' => 'Show Owner',
            'email' => 'owner'.uniqid().'@example.com',
            'password' => bcrypt('secret-password'),
        ]);

        return LiveShow::create(array_merge([
            'title' => 'Managed Show',
            'description' => 'Show under test.',
            'scheduled_at' => $airedAt,
            'status' => 'scheduled',
            'is_test_show' => false,
            'host_name' => 'Host',
            'prize_amount' => 1000,
            'currency' => 'EUR',
            'max_winners' => 3,
            'max_players' => 100,
            'chat_enabled' => true,
            'winners_announced' => false,
            'start_time' => $airedAt,
            'end_time' => (clone $airedAt)->addMinutes(45),
            'created_by' => $owner->id,
        ], $overrides));
    }

    /**
     * @return array<string, mixed>
     */
    private function fakePriceRule(int $id): array
    {
        return [
            'price_rule' => [
                'id' => $id,
                'title' => "BADABING Rule {$id}",
                'value_type' => 'fixed_amount',
                'value' => '-50.0',
                'usage_limit' => 1,
                'starts_at' => Carbon::now()->toIso8601String(),
                'ends_at' => Carbon::now()->addDays(31)->toIso8601String(),
            ],
        ];
    }
}
