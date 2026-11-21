<?php

namespace Tests\Feature;

use App\Models\Department;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProfileTest extends TestCase
{
    use RefreshDatabase;

    private function student(array $attributes = []): User
    {
        return User::factory()->create(array_merge(['role' => 'student'], $attributes));
    }

    public function test_profile_page_is_displayed(): void
    {
        $this->actingAs($this->student())->get('/profile')->assertOk();
    }

    public function test_student_can_update_department_and_mobile_number(): void
    {
        $user = $this->student();
        $department = Department::factory()->create();

        $this->actingAs($user)
            ->patch('/profile', [
                'department' => $department->name,
                'mobile_number' => '09171234567',
            ])
            ->assertSessionHasNoErrors()
            ->assertRedirect('/profile');

        $user->refresh();

        $this->assertSame($department->id, $user->department_id);
        $this->assertSame('09171234567', $user->mobile_number);
    }

    public function test_a_department_typed_under_others_is_saved_once_and_reused(): void
    {
        $first = $this->student();
        $second = $this->student();
        $before = Department::count();

        foreach ([$first, $second] as $user) {
            $this->actingAs($user)
                ->patch('/profile', [
                    'department' => 'Others',
                    'department_other' => 'Student Council',
                    'mobile_number' => '09171234567',
                ])
                ->assertSessionHasNoErrors();
        }

        $this->assertSame($before + 1, Department::count());
        $this->assertSame('Student Council', $first->refresh()->department->name);
        $this->assertSame($first->department_id, $second->refresh()->department_id);
        $this->assertFalse($first->department->is_listed);
    }

    public function test_name_and_email_cannot_be_changed_from_the_profile(): void
    {
        $user = $this->student();
        $name = $user->name;
        $email = $user->email;

        $this->actingAs($user)
            ->patch('/profile', [
                'name' => 'Someone Else',
                'email' => 'someone@example.com',
                'department' => Department::factory()->create()->name,
                'mobile_number' => '09171234567',
            ])
            ->assertSessionHasNoErrors()
            ->assertRedirect('/profile');

        $user->refresh();

        $this->assertSame($name, $user->name);
        $this->assertSame($email, $user->email);
        $this->assertNotNull($user->email_verified_at);
    }

    public function test_user_can_delete_their_account(): void
    {
        $user = $this->student();

        $this->actingAs($user)
            ->delete('/profile', ['password' => 'password'])
            ->assertSessionHasNoErrors()
            ->assertRedirect('/');

        $this->assertGuest();
        $this->assertNull($user->fresh());
    }

    public function test_correct_password_must_be_provided_to_delete_account(): void
    {
        $user = $this->student();

        $this->actingAs($user)
            ->from('/profile')
            ->delete('/profile', ['password' => 'wrong-password'])
            ->assertSessionHasErrorsIn('userDeletion', 'password')
            ->assertRedirect('/profile');

        $this->assertNotNull($user->fresh());
    }
}
