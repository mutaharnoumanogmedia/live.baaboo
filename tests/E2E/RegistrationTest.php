<?php

namespace Tests\E2E;

use App\Mail\RegistrationWelcomeMail;
use App\Models\User;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;

/**
 * Step 1 of the E2E chain: user registration on the badabing website.
 */
class RegistrationTest extends E2ETestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Http::fake([
            '*' => Http::response([
                'status' => false,
                'affiliated' => false,
            ], 200),
        ]);

        Mail::fake();
    }

    /** @test */
    public function test_visitor_registers_directly_on_the_website(): void
    {
        $response = $this->post(route('register-user-via-form-submit'), [
            'name' => 'Max Mustermann',
            'email' => E2EContext::DIRECT_USER_EMAIL,
            'agree_for_terms' => '1',
            'agree_for_email' => '1',
        ]);

        $response->assertRedirect(route('thank-you-for-your-participation', ['user_name' => 'max']));

        $user = User::where('email', E2EContext::DIRECT_USER_EMAIL)->firstOrFail();
        E2EContext::$directUserId = $user->id;

        $this->assertTrue($user->hasRole('user'));
        $this->assertNull($user->referred_by);
        $this->assertNotNull($user->referral_link);
        $this->assertNotNull($user->magic_link);

        Mail::assertSent(RegistrationWelcomeMail::class, fn (RegistrationWelcomeMail $mail) => $mail->hasTo(E2EContext::DIRECT_USER_EMAIL));
    }

    /**
     * @test
     *
     * @depends test_visitor_registers_directly_on_the_website
     */
    public function test_visitor_registers_through_a_referral_link(): void
    {
        $referrer = User::create([
            'name' => 'Referrer',
            'email' => E2EContext::REFERRER_EMAIL,
            'user_name' => E2EContext::$referrerUserName,
            'password' => bcrypt('secret-password'),
            'is_affiliate' => 1,
        ]);
        $referrer->assignRole('user');
        E2EContext::$referrerUserId = $referrer->id;

        $this->get(route('register-user-via-form', ['name' => E2EContext::$referrerUserName]))
            ->assertOk()
            ->assertViewHas('referredByUser', fn ($u) => $u !== null && $u->id === $referrer->id);

        $response = $this->post(route('register-user-via-form-submit'), [
            'name' => 'Anna Referred',
            'email' => E2EContext::REFERRED_USER_EMAIL,
            'agree_for_terms' => '1',
            'referred_by' => $referrer->id,
        ]);

        $response->assertRedirect(route('thank-you-for-your-participation', ['user_name' => 'anna']));

        $referred = User::where('email', E2EContext::REFERRED_USER_EMAIL)->firstOrFail();
        E2EContext::$referredUserId = $referred->id;

        $this->assertSame($referrer->id, $referred->referred_by);
        $this->assertTrue($referred->hasRole('user'));
    }
}
