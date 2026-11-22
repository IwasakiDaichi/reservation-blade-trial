<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthenticationTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_screen_can_be_rendered(): void
    {
        $this->get('/login')->assertStatus(200)->assertSee('CPDO staff sign in');
    }

    public function test_cpdo_staff_can_authenticate_using_the_login_screen(): void
    {
        $user = User::factory()->create(['role' => 'cpdo_admin']);

        $response = $this->post('/login', [
            'email' => $user->email,
            'password' => 'password',
        ]);

        $this->assertAuthenticated();
        $response->assertRedirect(route('dashboard', absolute: false));
    }

    public function test_staff_login_ignores_an_old_intended_page(): void
    {
        $admin = User::factory()->create(['role' => 'cpdo_admin']);

        $this->withSession(['url.intended' => url('/reservations/create')])
            ->post('/login', ['email' => $admin->email, 'password' => 'password'])
            ->assertRedirect(route('dashboard'));

        $this->get(route('dashboard'))->assertRedirect(route('admin.dashboard'));
    }

    public function test_students_cannot_use_the_staff_login(): void
    {
        $student = User::factory()->create(['role' => 'student']);

        $this->post('/login', [
            'email' => $student->email,
            'password' => 'password',
        ])->assertSessionHasErrors('email');

        $this->assertGuest();
    }

    public function test_users_can_not_authenticate_with_invalid_password(): void
    {
        $user = User::factory()->create(['role' => 'cpdo_admin']);

        $this->post('/login', [
            'email' => $user->email,
            'password' => 'wrong-password',
        ]);

        $this->assertGuest();
    }

    public function test_users_can_logout(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post('/logout');

        $this->assertGuest();
        $response->assertRedirect('/');
    }
}
