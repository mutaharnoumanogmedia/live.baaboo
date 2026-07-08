<?php

namespace Tests\Feature;

use App\Models\LiveShow;
use App\Models\User;
use Carbon\Carbon;
use Database\Seeders\PermissionSeeder;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

/**
 * Checklist: Join
 *
 *  1. Join directly without any referral / registered (new visitor)
 *  2. Join directly without any referral but already registered
 *  3. Join registered user on referral link (magic link)
 *
 * Cases 1–2 hit GamePlayController::registerUser. Case 3 hits
 * HomeController::liveShowMagicLink, which attaches the player to the live show.
 *
 * @see \App\Http\Controllers\GamePlayController::registerUser
 * @see \App\Http\Controllers\HomeController::liveShowMagicLink
 */
class LiveShowJoinTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(PermissionSeeder::class);
        $this->seed(RoleSeeder::class);

        Http::fake([
            '*' => Http::response([
                'contacts' => [],
                'status' => false,
                'affiliated' => false,
            ], 200),
        ]);
    }

    /**
     * @param  array<string, mixed>  $overrides
     */
    private function liveShow(array $overrides = []): LiveShow
    {
        $airedAt = Carbon::now()->setTime(18, 0);

        $owner = User::create([
            'name' => 'Show Owner',
            'email' => 'owner'.uniqid().'@example.com',
            'password' => bcrypt('secret-password'),
        ]);

        return LiveShow::create(array_merge([
            'title' => 'Joinable Show',
            'description' => 'A show currently on air.',
            'scheduled_at' => $airedAt,
            'status' => 'live',
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

    /** @test */
    public function able_to_join_directly_without_referral_as_new_visitor(): void
    {
        $show = $this->liveShow();

        $response = $this->postJson(route('live-show.user.register', $show->id), [
            'email' => 'newcomer@example.com',
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'message' => 'User registered successfully.',
            'authStatus' => true,
        ]);

        $this->assertDatabaseHas('users', ['email' => 'newcomer@example.com']);
        $user = User::where('email', 'newcomer@example.com')->firstOrFail();
        $this->assertTrue($user->hasRole('user'));

        $this->assertDatabaseHas('user_live_shows', [
            'live_show_id' => $show->id,
            'user_id' => $user->id,
            'status' => 'registered',
            'is_online' => 1,
        ]);

        $this->assertAuthenticatedAs($user, 'web');
    }

    /** @test */
    public function able_to_join_directly_without_referral_as_already_registered_user(): void
    {
        $show = $this->liveShow();

        $existing = User::create([
            'name' => 'Existing User',
            'email' => 'existing@example.com',
            'user_name' => 'existing',
            'password' => bcrypt('secret-password'),
        ]);
        $existing->assignRole('user');

        $response = $this->postJson(route('live-show.user.register', $show->id), [
            'email' => 'existing@example.com',
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'message' => 'User logged in successfully.',
            'authStatus' => true,
        ]);

        $this->assertSame(1, User::where('email', 'existing@example.com')->count());

        $this->assertDatabaseHas('user_live_shows', [
            'live_show_id' => $show->id,
            'user_id' => $existing->id,
            'status' => 'registered',
            'is_online' => 1,
        ]);

        $this->assertAuthenticatedAs($existing, 'web');
    }

    /** @test */
    public function able_to_join_registered_user_on_referral_magic_link(): void
    {
        $show = $this->liveShow();

        $referrer = User::create([
            'name' => 'Referrer',
            'email' => 'referrer@example.com',
            'user_name' => 'referrer',
            'password' => bcrypt('secret-password'),
            'is_affiliate' => 1,
        ]);
        $referrer->assignRole('user');

        $player = User::create([
            'name' => 'Referred Player',
            'email' => 'referred.player@example.com',
            'user_name' => 'referred-player',
            'password' => bcrypt('secret-password'),
            'referred_by' => $referrer->id,
        ]);
        $player->assignRole('user');
        $player->forceFill([
            'referral_link' => $player->referralLink(),
            'magic_link' => $player->magicLink(),
        ])->save();

        $response = $this->get(route('live-show-magic-link', 'referred-player'));

        $response->assertRedirect(route('live-show', ['id' => $show->id]));

        $this->assertDatabaseHas('user_live_shows', [
            'live_show_id' => $show->id,
            'user_id' => $player->id,
            'status' => 'registered',
            'is_online' => 1,
        ]);

        $this->assertAuthenticatedAs($player, 'web');
    }
}
