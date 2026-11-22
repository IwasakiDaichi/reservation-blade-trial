<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Laravel\Socialite\Facades\Socialite;
use Laravel\Socialite\Two\User as SocialiteUser;
use Mockery;
use Tests\TestCase;

class UbAccountLoginTest extends TestCase
{
    use RefreshDatabase;

    private function fakeGoogleUser(string $email): void
    {
        $googleUser = new SocialiteUser();
        $googleUser->map(['id' => '123', 'name' => 'Juan Cruz', 'email' => $email]);
        $googleUser->user = ['email_verified' => true];
        $googleUser->token = 'token';

        $provider = Mockery::mock();
        $provider->shouldReceive('user')->andReturn($googleUser);

        Socialite::shouldReceive('driver')->with('google')->andReturn($provider);
        Http::fake();
    }

    public function test_home_page_offers_both_ways_in(): void
    {
        $this->get('/')
            ->assertOk()
            ->assertSee('Log in with your student account')
            ->assertSee('CPDO sign in');
    }

    public function test_ub_email_can_log_in_and_becomes_a_student(): void
    {
        $this->fakeGoogleUser('20162499@s.ubaguio.edu');

        $this->get(route('google.callback'))->assertRedirect(route('dashboard'));

        $this->assertAuthenticated();
        $this->assertDatabaseHas('users', ['email' => '20162499@s.ubaguio.edu', 'role' => 'student']);
    }

    public function test_a_cpdo_staff_email_is_sent_back_to_the_staff_sign_in(): void
    {
        User::factory()->create(['email' => 'staff@ubaguio.edu', 'role' => 'cpdo_admin']);
        $this->fakeGoogleUser('staff@ubaguio.edu');

        $this->get(route('google.callback'))
            ->assertRedirect('/')
            ->assertSessionHas('login_error');

        $this->assertGuest();
    }

    public function test_non_ub_google_account_is_turned_away(): void
    {
        $this->fakeGoogleUser('someone@gmail.com');

        $this->get(route('google.callback'))
            ->assertRedirect('/')
            ->assertSessionHas('login_error');

        $this->assertGuest();
        $this->assertDatabaseMissing('users', ['email' => 'someone@gmail.com']);
    }

    public function test_lookalike_domain_is_turned_away(): void
    {
        $this->fakeGoogleUser('someone@notubaguio.edu');

        $this->get(route('google.callback'))->assertRedirect('/');

        $this->assertGuest();
    }
}
