<?php

namespace Tests\Feature;

use App\Mail\RegistrationWelcomeMail;
use App\Models\User;
use Database\Seeders\PermissionSeeder;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

/**
 * Checklist: Register
 *
 *  1. Able to Register directly in the badabing website
 *  2. Able to Register through Referral Link
 *
 * Each case hits the HomeController registration endpoints over HTTP
 * (middleware + validation + controller body), not by calling the class
 * directly.
 *
 * @see \App\Http\Controllers\HomeController::registerUserViaFormSubmit
 * @see \App\Http\Controllers\HomeController::registerUserViaForm
 */
class UserRegistrationFlowTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(PermissionSeeder::class);
        $this->seed(RoleSeeder::class);

        // Affiliate username lookup + lead generation must stay local.
        Http::fake([
            '*' => Http::response([
                'status' => false,
                'affiliated' => false,
            ], 200),
        ]);

        Mail::fake();
    }

    /** @test */
    public function able_to_register_directly_on_the_badabing_website(): void
    {
        $response = $this->post(route('register-user-via-form-submit'), [
            'name' => 'Max Mustermann',
            'email' => 'max@example.com',
            'agree_for_terms' => '1',
            'agree_for_email' => '1',
        ]);

        $response->assertRedirect(route('thank-you-for-your-participation', ['user_name' => 'max']));

        $this->assertDatabaseHas('users', [
            'email' => 'max@example.com',
            'user_name' => 'max',
            'referred_by' => null,
            'agree_for_terms' => 1,
            'agree_for_email' => 1,
        ]);

        $user = User::where('email', 'max@example.com')->firstOrFail();
        $this->assertTrue($user->hasRole('user'));
        $this->assertNotNull($user->referral_link);
        $this->assertNotNull($user->magic_link);

        Mail::assertSent(RegistrationWelcomeMail::class, fn (RegistrationWelcomeMail $mail) => $mail->hasTo('max@example.com'));
    }

    /** @test */
    public function able_to_register_through_a_referral_link(): void
    {
        $referrer = User::create([
            'name' => 'Referrer',
            'email' => 'partner@example.com',
            'user_name' => 'partner1',
            'password' => bcrypt('secret-password'),
            'is_affiliate' => 1,
        ]);
        $referrer->assignRole('user');

        // Referral landing page resolves the referrer by user_name in the URL.
        $this->get(route('register-user-via-form', ['name' => 'partner1']))
            ->assertOk()
            ->assertViewHas('referredByUser', fn ($u) => $u !== null && $u->id === $referrer->id);

        $response = $this->post(route('register-user-via-form-submit'), [
            'name' => 'Anna Referred',
            'email' => 'anna@example.com',
            'agree_for_terms' => '1',
            'referred_by' => $referrer->id,
        ]);

        $response->assertRedirect(route('thank-you-for-your-participation', ['user_name' => 'anna']));

        $this->assertDatabaseHas('users', [
            'email' => 'anna@example.com',
            'referred_by' => $referrer->id,
        ]);

        $newUser = User::where('email', 'anna@example.com')->firstOrFail();
        $this->assertTrue($newUser->hasRole('user'));
        $this->assertSame($referrer->id, $newUser->referredBy->id);
    }
}
