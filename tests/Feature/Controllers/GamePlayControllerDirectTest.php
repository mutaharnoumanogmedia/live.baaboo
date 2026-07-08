<?php

namespace Tests\Feature\Controllers;

use App\Http\Controllers\GamePlayController;
use App\Models\LiveShow;
use App\Models\User;
use Carbon\Carbon;
use Database\Seeders\PermissionSeeder;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Tests\Feature\Controllers\Concerns\AssertsDirectControllerResponses;
use Tests\TestCase;

/**
 * Direct controller calls for live-show join flows.
 *
 * @see \App\Http\Controllers\GamePlayController::registerUser
 */
class GamePlayControllerDirectTest extends TestCase
{
    use AssertsDirectControllerResponses;
    use RefreshDatabase;

    private GamePlayController $controller;

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

        $this->startSession();
        $this->controller = new GamePlayController;
    }

    /** @test */
    public function register_user_returns_success_json_for_new_visitor(): void
    {
        $show = $this->liveShow();

        $request = Request::create('/live-show/'.$show->id.'/user/register', 'POST', [
            'email' => 'newcomer@example.com',
        ]);

        $data = $this->assertJsonResponse($this->controller->registerUser($request, $show->id), 200, [
            'success' => true,
            'message' => 'User registered successfully.',
            'authStatus' => true,
        ]);

        $this->assertArrayHasKey('user', $data);
        $this->assertDatabaseHas('users', ['email' => 'newcomer@example.com']);
        $this->assertDatabaseHas('user_live_shows', [
            'live_show_id' => $show->id,
            'status' => 'registered',
            'is_online' => 1,
        ]);
        $this->assertTrue(Auth::guard('web')->check());
    }

    /** @test */
    public function register_user_returns_success_json_for_existing_user(): void
    {
        $show = $this->liveShow();

        $existing = User::create([
            'name' => 'Existing User',
            'email' => 'existing@example.com',
            'user_name' => 'existing',
            'password' => bcrypt('secret-password'),
        ]);
        $existing->assignRole('user');

        $request = Request::create('/live-show/'.$show->id.'/user/register', 'POST', [
            'email' => 'existing@example.com',
        ]);

        $data = $this->assertJsonResponse($this->controller->registerUser($request, $show->id), 200, [
            'success' => true,
            'message' => 'User logged in successfully.',
            'authStatus' => true,
        ]);

        $this->assertSame($existing->id, $data['user']['id'] ?? $data['user']->id ?? null);
        $this->assertSame(1, User::where('email', 'existing@example.com')->count());
        $this->assertDatabaseHas('user_live_shows', [
            'live_show_id' => $show->id,
            'user_id' => $existing->id,
            'status' => 'registered',
            'is_online' => 1,
        ]);
        $this->assertTrue(Auth::guard('web')->check());
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
}
