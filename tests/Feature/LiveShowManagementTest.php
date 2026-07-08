<?php

namespace Tests\Feature;

use App\Jobs\GenerateWinnerDiscountCodeJob;
use App\Models\LiveShow;
use App\Models\LiveShowWinnerPrize;
use App\Models\ShopifyPriceRule;
use App\Models\User;
use App\Models\UserLiveShow;
use Carbon\Carbon;
use Database\Seeders\PermissionSeeder;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

/**
 * Checklist: Live Show Creations
 *
 *  1. Creating on same time
 *  2. Cash prizes and voucher prize
 *  3. For voucher prizes, voucher code should be generated with Shopify test shop credentials
 *  4. Update Live Show
 *
 * Cases 1–2 and 4 hit LiveShowController::store / ::update over HTTP.
 * Case 3 hits LiveShowController::updateWinners (which dispatches
 * GenerateWinnerDiscountCodeJob) so the voucher-code path is exercised through
 * the controller, not by instantiating the job alone.
 *
 * @see \App\Http\Controllers\Admin\LiveShowController::store
 * @see \App\Http\Controllers\Admin\LiveShowController::update
 * @see \App\Http\Controllers\Admin\LiveShowController::syncWinnerPrizes
 * @see \App\Http\Controllers\Admin\LiveShowController::updateWinners
 * @see \App\Jobs\GenerateWinnerDiscountCodeJob
 */
class LiveShowManagementTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(PermissionSeeder::class);
        $this->seed(RoleSeeder::class);
    }

    private function admin(): User
    {
        $admin = User::create([
            'name' => 'Admin',
            'email' => 'admin@example.com',
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

    /** @test */
    public function able_to_create_two_shows_at_the_same_scheduled_time(): void
    {
        $admin = $this->admin();
        $scheduledAt = '2026-09-15 20:00:00';

        $this->actingAs($admin, 'admin')
            ->post(route('admin.live-shows.store'), $this->storePayload([
                'title' => 'Simultaneous A',
                'scheduled_at' => $scheduledAt,
            ]))
            ->assertRedirect();

        $this->actingAs($admin, 'admin')
            ->post(route('admin.live-shows.store'), $this->storePayload([
                'title' => 'Simultaneous B',
                'scheduled_at' => $scheduledAt,
            ]))
            ->assertRedirect();

        $this->assertDatabaseHas('live_shows', ['title' => 'Simultaneous A']);
        $this->assertDatabaseHas('live_shows', ['title' => 'Simultaneous B']);
        $this->assertSame(2, LiveShow::whereIn('title', ['Simultaneous A', 'Simultaneous B'])
            ->where('scheduled_at', Carbon::parse($scheduledAt))
            ->count());
    }

    /** @test */
    public function able_to_create_a_show_with_cash_and_voucher_prizes(): void
    {
        Http::fake([
            '*/price_rules.json' => Http::sequence()
                ->push($this->fakePriceRule(111), 201)
                ->push($this->fakePriceRule(112), 201),
        ]);

        $admin = $this->admin();

        $response = $this->actingAs($admin, 'admin')->post(route('admin.live-shows.store'), $this->storePayload([
            'title' => 'Cash And Voucher Show',
            'max_winners' => 4,
            'is_test_show' => 0,
            'winner_prizes' => [
                1 => '1000.00',
                2 => '500.00',
                3 => '50.00',
                4 => '30.00',
            ],
            'winner_voucher' => [
                3 => 1,
                4 => 1,
            ],
            'winner_voucher_amount' => [
                3 => 50,
                4 => 30,
            ],
        ]));

        $show = LiveShow::where('title', 'Cash And Voucher Show')->firstOrFail();
        $response->assertRedirect(route('admin.live-shows.show', $show->id));

        $prizes = LiveShowWinnerPrize::where('live_show_id', $show->id)->orderBy('rank')->get();
        $this->assertCount(4, $prizes);

        $this->assertFalse((bool) $prizes[0]->is_voucher);
        $this->assertFalse((bool) $prizes[1]->is_voucher);
        $this->assertNull($prizes[0]->discount_rule_id);
        $this->assertNull($prizes[1]->discount_rule_id);

        $this->assertTrue((bool) $prizes[2]->is_voucher);
        $this->assertTrue((bool) $prizes[3]->is_voucher);
        $this->assertNotNull($prizes[2]->discount_rule_id);
        $this->assertNotNull($prizes[3]->discount_rule_id);

        $this->assertDatabaseHas('shopify_price_rule', ['shopify_id' => 111]);
        $this->assertDatabaseHas('shopify_price_rule', ['shopify_id' => 112]);
    }

    /** @test */
    public function able_to_generate_voucher_code_with_shopify_test_shop_credentials(): void
    {
        Queue::fake();

        $admin = $this->admin();
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
            'prize_won' => null,
            'discount_code' => null,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Controller path: announcing winners dispatches GenerateWinnerDiscountCodeJob
        // for voucher ranks that have a Shopify price rule.
        $response = $this->actingAs($admin, 'admin')
            ->postJson(route('admin.live-shows.update-winners', $show->id));

        $response->assertStatus(200);
        $response->assertJson(['success' => true, 'winners_announced' => true]);

        Queue::assertPushed(GenerateWinnerDiscountCodeJob::class, function (GenerateWinnerDiscountCodeJob $job) use ($show, $winner, $priceRule) {
            return $job->userId === $winner->id
                && $job->liveShowId === $show->id
                && (string) $job->shopifyPriceRuleId === (string) $priceRule->shopify_id;
        });

        // Run the queued job with Shopify faked against test-shop credentials.
        Http::fake([
            '*/discount_codes/lookup.json*' => Http::response(['discount_code' => ['id' => null]], 200),
            '*/discount_codes.json' => Http::response([
                'discount_code' => ['id' => 555, 'code' => 'BADABINGVOUCHER'],
            ], 201),
        ]);

        (new GenerateWinnerDiscountCodeJob($winner->id, $show->id, $priceRule->shopify_id))->handle();

        $pivot = UserLiveShow::where('live_show_id', $show->id)
            ->where('user_id', $winner->id)
            ->firstOrFail();

        $this->assertSame('BADABINGVOUCHER', $pivot->discount_code);

        Http::assertSent(fn ($request) => str_contains($request->url(), "/price_rules/{$priceRule->shopify_id}/discount_codes.json")
            && str_contains($request->url(), (string) rtrim((string) env('SHOPIFY_API_DOMAIN'), '/')));
    }

    /** @test */
    public function able_to_update_a_live_show(): void
    {
        $show = $this->createShow([
            'title' => 'Original Title',
            'status' => 'scheduled',
        ]);

        $admin = $this->admin();

        $response = $this->actingAs($admin, 'admin')->put(route('admin.live-shows.update', $show->id), [
            'title' => 'Updated Title',
            'description' => 'Updated description.',
            'scheduled_at' => '2026-10-10 19:30:00',
            'status' => 'live',
            'max_players' => 250,
            'chat_enabled' => 0,
            'is_test_show' => 0,
        ]);

        $response->assertRedirect(route('admin.live-shows.edit', $show->id));

        $this->assertDatabaseHas('live_shows', [
            'id' => $show->id,
            'title' => 'Updated Title',
            'status' => 'live',
            'max_players' => 250,
            'chat_enabled' => 0,
        ]);
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
