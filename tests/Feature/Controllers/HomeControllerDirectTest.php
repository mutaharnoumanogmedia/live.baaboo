<?php

namespace Tests\Feature\Controllers;

use App\Http\Controllers\HomeController;
use App\Mail\RegistrationWelcomeMail;
use App\Models\LiveShow;
use App\Models\User;
use Carbon\Carbon;
use Database\Seeders\PermissionSeeder;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Tests\Feature\Controllers\Concerns\AssertsDirectControllerResponses;
use Tests\TestCase;

/**
 * Direct controller calls for registration and magic-link join flows.
 *
 * @see \App\Http\Controllers\HomeController::registerUserViaFormSubmit
 * @see \App\Http\Controllers\HomeController::registerUserViaForm
 * @see \App\Http\Controllers\HomeController::liveShowMagicLink
 */
class HomeControllerDirectTest extends TestCase
{
    use AssertsDirectControllerResponses;
    use RefreshDatabase;

    private HomeController $controller;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(PermissionSeeder::class);
        $this->seed(RoleSeeder::class);

        Http::fake([
            '*' => Http::response([
                'status' => false,
                'affiliated' => false,
                'contacts' => [],
            ], 200),
        ]);

        Mail::fake();

        $this->controller = new HomeController;
    }

    /** @test */
    public function register_user_via_form_submit_returns_thank_you_redirect_for_direct_registration(): void
    {
        $request = Request::create('/register', 'POST', [
            'name' => 'Max Mustermann',
            'email' => 'max@example.com',
            'agree_for_terms' => '1',
            'agree_for_email' => '1',
        ]);

        $response = $this->controller->registerUserViaFormSubmit($request);

        $this->assertRedirectResponse(
            $response,
            route('thank-you-for-your-participation', ['user_name' => 'max'])
        );

        $this->assertDatabaseHas('users', [
            'email' => 'max@example.com',
            'user_name' => 'max',
            'referred_by' => null,
        ]);

        Mail::assertSent(RegistrationWelcomeMail::class, fn (RegistrationWelcomeMail $mail) => $mail->hasTo('max@example.com'));
    }

    /** @test */
    public function register_user_via_form_submit_returns_thank_you_redirect_for_referral_registration(): void
    {
        $referrer = User::create([
            'name' => 'Referrer',
            'email' => 'partner@example.com',
            'user_name' => 'partner1',
            'password' => bcrypt('secret-password'),
            'is_affiliate' => 1,
        ]);
        $referrer->assignRole('user');

        $request = Request::create('/register', 'POST', [
            'name' => 'Anna Referred',
            'email' => 'anna@example.com',
            'agree_for_terms' => '1',
            'referred_by' => $referrer->id,
        ]);

        $response = $this->controller->registerUserViaFormSubmit($request);

        $this->assertRedirectResponse(
            $response,
            route('thank-you-for-your-participation', ['user_name' => 'anna'])
        );

        $this->assertDatabaseHas('users', [
            'email' => 'anna@example.com',
            'referred_by' => $referrer->id,
        ]);
    }

    /** @test */
    public function register_user_via_form_returns_index_view_with_referrer(): void
    {
        $referrer = User::create([
            'name' => 'Referrer',
            'email' => 'partner@example.com',
            'user_name' => 'partner1',
            'password' => bcrypt('secret-password'),
            'is_affiliate' => 1,
        ]);
        $referrer->assignRole('user');

        $response = $this->controller->registerUserViaForm('partner1');

        $view = $this->assertViewResponse($response, 'index');
        $this->assertSame($referrer->id, $view->getData()['referredByUser']->id);
    }

    /** @test */
    public function live_show_magic_link_returns_live_show_redirect_and_attaches_player(): void
    {
        $show = $this->joinableLiveShow();

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
            'magic_link' => 'magic-link',
            'referral_link' => 'referred-player',
        ]);
        $player->assignRole('user');

        $response = $this->controller->liveShowMagicLink('referred-player');

        $this->assertRedirectResponse($response, route('live-show', ['id' => $show->id]));

        $this->assertDatabaseHas('user_live_shows', [
            'live_show_id' => $show->id,
            'user_id' => $player->id,
            'status' => 'registered',
            'is_online' => 1,
        ]);

        $this->assertTrue(Auth::guard('web')->check());
        $this->assertSame($player->id, Auth::guard('web')->id());
    }

    /**
     * @param  array<string, mixed>  $overrides
     */
    private function joinableLiveShow(array $overrides = []): LiveShow
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
}
