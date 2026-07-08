<?php

namespace Tests\E2E;

use App\Jobs\GenerateWinnerDiscountCodeJob;
use App\Models\LiveShow;
use App\Models\LiveShowWinnerPrize;
use App\Models\ShopifyPriceRule;
use App\Models\User;
use App\Models\UserLiveShow;
use Carbon\Carbon;
use Illuminate\Support\Facades\Http;

/**
 * Step 2 of the E2E chain: admin live show creation and updates.
 */
class LiveShowCreationTest extends E2ETestCase
{
    /**
     * @test
     *
     * @depends Tests\E2E\RegistrationTest::test_visitor_registers_through_a_referral_link
     */
    public function test_admin_creates_a_show_with_cash_and_voucher_prizes(): void
    {
        Http::fake([
            '*/price_rules.json' => Http::sequence()
                ->push($this->fakePriceRule(201), 201)
                ->push($this->fakePriceRule(202), 201),
        ]);

        $admin = User::findOrFail(E2EContext::$adminUserId);

        $response = $this->actingAs($admin, 'admin')->post(route('admin.live-shows.store'), [
            'title' => E2EContext::LIVE_SHOW_TITLE,
            'description' => 'E2E sequential test show.',
            'scheduled_at' => '2026-08-01 18:00:00',
            'status' => 'scheduled',
            'host_name' => 'E2E Host',
            'prize_amount' => 1000,
            'currency' => 'EUR',
            'max_winners' => 4,
            'max_players' => 100,
            'chat_enabled' => 1,
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
        ]);

        $show = LiveShow::where('title', E2EContext::LIVE_SHOW_TITLE)->firstOrFail();
        E2EContext::$liveShowId = $show->id;

        $response->assertRedirect(route('admin.live-shows.show', $show->id));

        $prizes = LiveShowWinnerPrize::where('live_show_id', $show->id)->orderBy('rank')->get();
        $this->assertCount(4, $prizes);
        $this->assertFalse((bool) $prizes[0]->is_voucher);
        $this->assertFalse((bool) $prizes[1]->is_voucher);
        $this->assertTrue((bool) $prizes[2]->is_voucher);
        $this->assertTrue((bool) $prizes[3]->is_voucher);

        $voucherPrize = $prizes[2];
        $this->assertNotNull($voucherPrize->discount_rule_id);
        E2EContext::$voucherShopifyPriceRuleId = (int) ShopifyPriceRule::findOrFail($voucherPrize->discount_rule_id)->shopify_id;

        $this->assertDatabaseHas('shopify_price_rule', ['shopify_id' => 201]);
        $this->assertDatabaseHas('shopify_price_rule', ['shopify_id' => 202]);
    }

    /**
     * @test
     *
     * @depends test_admin_creates_a_show_with_cash_and_voucher_prizes
     */
    public function test_two_shows_can_be_created_for_the_same_scheduled_time(): void
    {
        $admin = User::findOrFail(E2EContext::$adminUserId);
        $scheduledAt = '2026-09-15 20:00:00';

        $payload = [
            'description' => 'Same-time show.',
            'scheduled_at' => $scheduledAt,
            'status' => 'scheduled',
            'host_name' => 'Host',
            'prize_amount' => 500,
            'currency' => 'EUR',
            'max_winners' => 1,
            'max_players' => 50,
            'chat_enabled' => 1,
            'is_test_show' => 1,
            'winner_prizes' => [1 => '100.00'],
        ];

        $this->actingAs($admin, 'admin')
            ->post(route('admin.live-shows.store'), array_merge($payload, ['title' => 'E2E Simultaneous A']))
            ->assertRedirect();

        $this->actingAs($admin, 'admin')
            ->post(route('admin.live-shows.store'), array_merge($payload, ['title' => 'E2E Simultaneous B']))
            ->assertRedirect();

        $this->assertSame(2, LiveShow::whereIn('title', ['E2E Simultaneous A', 'E2E Simultaneous B'])
            ->where('scheduled_at', Carbon::parse($scheduledAt))
            ->count());
    }

    /**
     * @test
     *
     * @depends test_two_shows_can_be_created_for_the_same_scheduled_time
     */
    public function test_voucher_discount_code_is_generated_with_shopify_credentials(): void
    {
        $show = LiveShow::findOrFail(E2EContext::$liveShowId);

        $winner = User::create([
            'name' => 'Shopify Code Probe',
            'email' => 'shopify.probe@example.com',
            'password' => bcrypt('secret-password'),
        ]);

        $show->users()->attach($winner->id, [
            'score' => 999,
            'status' => 'registered',
            'is_online' => 0,
            'is_winner' => true,
            'prize_won' => '50.00',
            'discount_code' => null,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        Http::fake([
            '*/discount_codes/lookup.json*' => Http::response(['discount_code' => ['id' => null]], 200),
            '*/discount_codes.json' => Http::response([
                'discount_code' => ['id' => 555, 'code' => 'E2EVOUCHERCODE'],
            ], 201),
        ]);

        (new GenerateWinnerDiscountCodeJob(
            $winner->id,
            $show->id,
            E2EContext::$voucherShopifyPriceRuleId
        ))->handle();

        $pivot = UserLiveShow::where('live_show_id', $show->id)
            ->where('user_id', $winner->id)
            ->firstOrFail();

        $this->assertSame('E2EVOUCHERCODE', $pivot->discount_code);

        // Remove the probe player so only the three join-step players compete.
        $show->users()->detach($winner->id);
        $winner->delete();
    }

    /**
     * @test
     *
     * @depends test_voucher_discount_code_is_generated_with_shopify_credentials
     */
    public function test_admin_updates_the_e2e_show_to_live(): void
    {
        $show = LiveShow::findOrFail(E2EContext::$liveShowId);
        $admin = User::findOrFail(E2EContext::$adminUserId);

        $response = $this->actingAs($admin, 'admin')->put(route('admin.live-shows.update', $show->id), [
            'title' => E2EContext::LIVE_SHOW_TITLE,
            'description' => 'E2E show, now live for join tests.',
            'scheduled_at' => $show->scheduled_at->format('Y-m-d H:i:s'),
            'status' => 'live',
            'max_players' => 100,
            'chat_enabled' => 1,
            'is_test_show' => 0,
        ]);

        $response->assertRedirect(route('admin.live-shows.edit', $show->id));

        $this->assertDatabaseHas('live_shows', [
            'id' => $show->id,
            'status' => 'live',
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    private function fakePriceRule(int $id): array
    {
        return [
            'price_rule' => [
                'id' => $id,
                'title' => "E2E Rule {$id}",
                'value_type' => 'fixed_amount',
                'value' => '-50.0',
                'usage_limit' => 1,
                'starts_at' => Carbon::now()->toIso8601String(),
                'ends_at' => Carbon::now()->addDays(31)->toIso8601String(),
            ],
        ];
    }
}
