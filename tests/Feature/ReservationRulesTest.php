<?php

namespace Tests\Feature;

use App\Models\Department;
use App\Models\Reservation;
use App\Models\User;
use App\Models\Venue;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\Concerns\PicksEventDates;
use Tests\TestCase;

class ReservationRulesTest extends TestCase
{
    use PicksEventDates;
    use RefreshDatabase;

    private function student(string $name = 'Student'): User
    {
        return User::factory()->create([
            'name' => $name,
            'role' => 'student',
            'department_id' => Department::factory()->create()->id,
            'mobile_number' => '09171234567',
        ]);
    }

    private function admin(): User
    {
        return User::factory()->create(['role' => 'cpdo_admin']);
    }

    private function payload(Venue $venue, array $override = []): array
    {
        return array_merge([
            'purpose' => 'Org event',
            'venue_id' => $venue->id,
            'reservationType' => 'single',
            'reservedDate' => $this->eventDate(),
            'startTime' => '09:00',
            'endTime' => '11:00',
            'agreed' => '1',
            'applicant_signature' => UploadedFile::fake()->image('sig.png'),
        ], $override);
    }

    private function bookedBy(User $user, Venue $venue, array $override = []): Reservation
    {
        $date = $override['date'] ?? $this->eventDate();

        return Reservation::create(array_merge([
            'user_id' => $user->id,
            'department_id' => $user->department_id,
            'mobile_number' => $user->mobile_number,
            'purpose' => 'Existing event',
            'venue_id' => $venue->id,
            'reserved_date' => $date,
            'time' => '09:00 - 11:00',
            'start_date' => $date,
            'end_date' => $date,
            'start_time' => '09:00',
            'end_time' => '11:00',
            'agreed' => true,
            'status' => 'approved',
        ], array_diff_key($override, ['date' => 1])));
    }

    public function test_reservation_less_than_two_weeks_ahead_is_rejected(): void
    {
        Storage::fake('local');
        $venue = Venue::factory()->create();

        $this->actingAs($this->student())
            ->post(route('reservations.store'), $this->payload($venue, [
                'reservedDate' => now()->addDays(10)->toDateString(),
            ]))
            ->assertSessionHasErrors('reservedDate');

        $this->assertDatabaseCount('reservations', 0);
    }

    public function test_reservation_exactly_two_weeks_ahead_is_accepted(): void
    {
        Storage::fake('local');
        $venue = Venue::factory()->create();
        $date = now()->addDays(14);

        if ($date->isSunday()) {
            $this->markTestSkipped('Two weeks from today is a Sunday.');
        }

        $this->actingAs($this->student())
            ->post(route('reservations.store'), $this->payload($venue, ['reservedDate' => $date->toDateString()]))
            ->assertSessionHasNoErrors();

        $this->assertDatabaseCount('reservations', 1);
    }

    public function test_sundays_are_rejected(): void
    {
        Storage::fake('local');
        $venue = Venue::factory()->create();
        $sunday = now()->addDays(20)->next('Sunday');

        $this->actingAs($this->student())
            ->post(route('reservations.store'), $this->payload($venue, ['reservedDate' => $sunday->toDateString()]))
            ->assertSessionHasErrors('reservedDate');
    }

    public function test_double_booking_is_blocked_and_the_student_is_told_who_has_the_venue(): void
    {
        Storage::fake('local');
        $venue = Venue::factory()->create(['name' => 'Sunrise Hall']);
        $owner = $this->student('Maria Santos');
        $this->bookedBy($owner, $venue, ['purpose' => 'Science Fair']);

        $response = $this->actingAs($this->student('Juan Cruz'))
            ->from(route('reservations.create'))
            ->post(route('reservations.store'), $this->payload($venue, ['startTime' => '10:00', 'endTime' => '12:00']));

        $response->assertSessionHas('conflicts');
        $this->assertDatabaseCount('reservations', 1);

        $this->followRedirects($response)
            ->assertSee('Unfortunately')
            ->assertSee('Sunrise Hall')
            ->assertSee('Science Fair')
            ->assertSee('Would you like to have a conversation with them');
    }

    public function test_back_to_back_bookings_are_not_a_double_booking(): void
    {
        Storage::fake('local');
        $venue = Venue::factory()->create();
        $this->bookedBy($this->student(), $venue); // 09:00 - 11:00

        $this->actingAs($this->student())
            ->post(route('reservations.store'), $this->payload($venue, ['startTime' => '11:00', 'endTime' => '12:00']))
            ->assertSessionMissing('conflicts')
            ->assertSessionHasNoErrors();

        $this->assertDatabaseCount('reservations', 2);
    }

    public function test_cancelled_reservations_do_not_block_the_venue(): void
    {
        Storage::fake('local');
        $venue = Venue::factory()->create();
        $this->bookedBy($this->student(), $venue, ['status' => 'cancelled']);

        $this->actingAs($this->student())
            ->post(route('reservations.store'), $this->payload($venue))
            ->assertSessionMissing('conflicts');

        $this->assertDatabaseCount('reservations', 2);
    }

    public function test_saying_yes_shows_the_other_email_and_tells_the_other_student(): void
    {
        Storage::fake('local');
        $venue = Venue::factory()->create();
        $owner = $this->student('Maria Santos');
        $existing = $this->bookedBy($owner, $venue);
        $asker = $this->student('Juan Cruz');

        $this->actingAs($asker)->post(route('reservations.store'), $this->payload($venue));

        $response = $this->actingAs($asker)
            ->withSession(['conflicts' => [[
                'id' => $existing->id, 'venue' => $venue->name, 'when' => 'x', 'purpose' => 'Existing event',
            ]]])
            ->post(route('reservations.contact'), ['reservation_id' => $existing->id]);

        $response->assertRedirect(route('reservations.create'));
        $response->assertSessionHas('contact_info.'.$existing->id.'.email', $owner->email);

        $this->assertSame(1, $owner->notifications()->count());
        $this->assertStringContainsString($asker->email, $owner->notifications()->first()->data['message']);
    }

    public function test_email_of_an_unrelated_reservation_is_not_given_out(): void
    {
        $venue = Venue::factory()->create();
        $existing = $this->bookedBy($this->student('Maria Santos'), $venue);

        $this->actingAs($this->student())
            ->post(route('reservations.contact'), ['reservation_id' => $existing->id])
            ->assertSessionMissing('contact_info');
    }

    public function test_student_can_cancel_three_or_more_days_ahead(): void
    {
        $venue = Venue::factory()->create();
        $student = $this->student();
        $reservation = $this->bookedBy($student, $venue, ['status' => 'pending']);

        $this->actingAs($student)
            ->patch(route('reservations.cancel', $reservation))
            ->assertSessionHas('success');

        $this->assertSame('cancelled', $reservation->fresh()->status);
        $this->assertSame('student', $reservation->fresh()->cancelled_by);
    }

    public function test_student_cannot_cancel_inside_the_three_day_notice(): void
    {
        $venue = Venue::factory()->create();
        $student = $this->student();
        $reservation = $this->bookedBy($student, $venue, [
            'date' => now()->addDay()->toDateString(),
        ]);

        $this->actingAs($student)
            ->patch(route('reservations.cancel', $reservation))
            ->assertSessionHas('error');

        $this->assertSame('approved', $reservation->fresh()->status);
    }

    public function test_student_cannot_cancel_someone_elses_reservation(): void
    {
        $venue = Venue::factory()->create();
        $reservation = $this->bookedBy($this->student(), $venue);

        $this->actingAs($this->student())
            ->patch(route('reservations.cancel', $reservation))
            ->assertForbidden();
    }

    public function test_cpdo_cancelling_tells_the_student_why(): void
    {
        $venue = Venue::factory()->create();
        $student = $this->student();
        $reservation = $this->bookedBy($student, $venue);

        $this->actingAs($this->admin())
            ->patch(route('admin.reservations.cancel', $reservation), ['cancellation_reason' => 'University event'])
            ->assertSessionHas('success');

        $this->assertSame('cancelled', $reservation->fresh()->status);
        $this->assertSame('cpdo', $reservation->fresh()->cancelled_by);
        $this->assertStringContainsString('University event', $student->notifications()->first()->data['message']);
    }

    public function test_maintenance_notifies_affected_students_and_turns_their_calendar_row_white(): void
    {
        $venue = Venue::factory()->create(['calendar_color' => '#FF00FF']);
        $affected = $this->student('Maria Santos');
        $other = $this->student('Other Venue Student');
        $this->bookedBy($affected, $venue);
        $this->bookedBy($other, Venue::factory()->create());
        $date = $this->eventDate();

        $entry = fn () => collect(
            $this->actingAs($this->admin())
                ->get(route('admin.calendar', ['month' => substr($date, 0, 7)]))
                ->viewData('days')
        )->flatten(1)->first(fn ($e) => $e['venue'] === $venue->calendarLabel());

        $this->assertSame('#FF00FF', $entry()['color']['bg']);

        $this->actingAs($this->admin())
            ->patch(route('admin.venues.status', $venue), ['status' => 'maintenance'])
            ->assertSessionHas('success');

        $this->assertSame(1, $affected->notifications()->count());
        $this->assertSame(0, $other->notifications()->count());

        $this->assertNull($entry()['color']);
        $this->assertStringContainsString('VENUE UNDER MAINTENANCE', $entry()['remarks']);
    }

    public function test_affected_student_can_cancel_inside_the_notice_period(): void
    {
        $venue = Venue::factory()->maintenance()->create();
        $student = $this->student();
        $reservation = $this->bookedBy($student, $venue, ['date' => now()->addDay()->toDateString()]);

        $this->actingAs($student)
            ->patch(route('reservations.cancel', $reservation))
            ->assertSessionHas('success');
    }

    public function test_student_sees_notifications_page(): void
    {
        $venue = Venue::factory()->create();
        $student = $this->student();
        $this->bookedBy($student, $venue);

        $this->actingAs($this->admin())->patch(route('admin.venues.status', $venue), ['status' => 'maintenance']);

        $this->actingAs($student)
            ->get(route('notifications.index'))
            ->assertOk()
            ->assertSee($venue->name.' is under maintenance');

        $this->assertSame(0, $student->unreadNotifications()->count());
    }

    public function test_reservation_form_shows_the_terms_before_the_reservation_details(): void
    {
        $student = $this->student();

        $this->actingAs($student)
            ->get(route('reservations.create'))
            ->assertOk()
            ->assertSeeInOrder(['Terms and Conditions', 'Reservation Details'])
            ->assertSee('Please read');
    }

    public function test_login_page_uses_the_welcome_layout(): void
    {
        $this->get('/login')
            ->assertOk()
            ->assertSee('Sign in to your account')
            ->assertSee('GET STARTED');
    }
}
