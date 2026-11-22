<?php

namespace Tests\Feature;

use App\Models\Department;
use App\Models\Reservation;
use App\Models\User;
use App\Models\Venue;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\URL;
use Tests\Concerns\PicksEventDates;
use Tests\TestCase;

class WalkInReservationTest extends TestCase
{
    use PicksEventDates;
    use RefreshDatabase;

    private function payload(Venue $venue, array $override = []): array
    {
        return array_merge([
            'applicant_name' => 'Rosa Dela Cruz',
            'local_number' => '1234',
            'purpose' => 'Alumni homecoming',
            'venue_id' => $venue->id,
            'reservationType' => 'single',
            'reservedDate' => $this->eventDate(),
            'startTime' => '13:00',
            'endTime' => '15:00',
            'agreed' => '1',
            'applicant_signature' => UploadedFile::fake()->image('sig.png'),
        ], $override);
    }

    private function walkInReservation(): Reservation
    {
        Storage::fake('local');
        $this->post(route('walkin.store'), $this->payload(Venue::factory()->create()));

        return Reservation::firstOrFail();
    }

    public function test_walk_in_form_is_open_without_an_account_and_follows_the_paper_form(): void
    {
        $this->get(route('walkin.create'))
            ->assertOk()
            ->assertSee('Name of Applicant')
            ->assertSee('Local No.')
            ->assertSee('(optional)');
    }

    public function test_walk_in_can_reserve_without_an_account_and_department_is_optional(): void
    {
        Storage::fake('local');
        $venue = Venue::factory()->create();

        $response = $this->post(route('walkin.store'), $this->payload($venue));

        $reservation = Reservation::firstOrFail();

        $this->assertNull($reservation->user_id);
        $this->assertNull($reservation->department_id);
        $this->assertSame('Rosa Dela Cruz', $reservation->applicantName());
        $this->assertSame('1234', $reservation->mobile_number);
        $this->assertSame('pending', $reservation->status);
        $this->assertTrue($reservation->isWalkIn());

        $location = $response->headers->get('Location');
        $this->assertStringContainsString('/walk-in/'.$reservation->id.'/thanks', $location);

        $this->get($location)->assertOk()->assertSee('Reservation submitted')->assertSee('#'.$reservation->id);
    }

    public function test_walk_in_department_is_saved_when_given(): void
    {
        Storage::fake('local');

        $this->post(route('walkin.store'), $this->payload(Venue::factory()->create(), ['department' => 'Rotary Club of Baguio']));

        $this->assertSame('Rotary Club of Baguio', Reservation::firstOrFail()->department->name);
    }

    public function test_walk_in_must_give_a_name_and_local_number(): void
    {
        Storage::fake('local');
        $venue = Venue::factory()->create();

        $this->post(route('walkin.store'), $this->payload($venue, ['applicant_name' => '', 'local_number' => '']))
            ->assertSessionHasErrors(['applicant_name', 'local_number']);

        $this->assertDatabaseCount('reservations', 0);
    }

    public function test_walk_in_follows_the_same_business_rules(): void
    {
        Storage::fake('local');
        $venue = Venue::factory()->create();

        $this->post(route('walkin.store'), $this->payload($venue, ['reservedDate' => now()->addDays(5)->toDateString()]))
            ->assertSessionHasErrors('reservedDate');

        $this->post(route('walkin.store'), $this->payload(Venue::factory()->maintenance()->create()))
            ->assertSessionHasErrors('venue_id');
    }

    public function test_walk_in_can_print_the_form_only_with_the_signed_link(): void
    {
        $reservation = $this->walkInReservation();

        $this->get(route('walkin.print', $reservation))->assertForbidden();

        $signed = URL::temporarySignedRoute('walkin.print', now()->addHour(), ['reservation' => $reservation->id]);

        $this->get($signed)->assertOk()->assertHeader('Content-Type', 'application/pdf');
    }

    public function test_walk_in_link_does_not_work_for_a_student_reservation(): void
    {
        $student = User::factory()->create([
            'role' => 'student',
            'department_id' => Department::factory()->create()->id,
            'mobile_number' => '09171234567',
        ]);
        $date = $this->eventDate();
        $reservation = Reservation::create([
            'user_id' => $student->id, 'department_id' => $student->department_id, 'mobile_number' => '0917',
            'purpose' => 'x', 'venue_id' => Venue::factory()->create()->id, 'reserved_date' => $date, 'time' => '09:00 - 10:00',
            'start_date' => $date, 'end_date' => $date, 'start_time' => '09:00', 'end_time' => '10:00',
            'agreed' => true, 'status' => 'pending',
        ]);

        $signed = URL::temporarySignedRoute('walkin.print', now()->addHour(), ['reservation' => $reservation->id]);

        $this->get($signed)->assertNotFound();
    }

    public function test_walk_in_double_booking_shows_the_other_students_email_after_yes(): void
    {
        Storage::fake('local');
        $venue = Venue::factory()->create(['name' => 'Sunrise Hall']);
        $student = User::factory()->create([
            'name' => 'Maria Santos',
            'role' => 'student',
            'department_id' => Department::factory()->create()->id,
            'mobile_number' => '09171234567',
        ]);
        $date = $this->eventDate();
        $existing = Reservation::create([
            'user_id' => $student->id, 'department_id' => $student->department_id, 'mobile_number' => '0917',
            'purpose' => 'Science Fair', 'venue_id' => $venue->id, 'reserved_date' => $date, 'time' => '13:00 - 15:00',
            'start_date' => $date, 'end_date' => $date, 'start_time' => '13:00', 'end_time' => '15:00',
            'agreed' => true, 'status' => 'approved',
        ]);

        $response = $this->from(route('walkin.create'))->post(route('walkin.store'), $this->payload($venue));
        $response->assertSessionHas('conflicts');
        $this->assertDatabaseCount('reservations', 1);

        $this->followRedirects($response)->assertSee('Unfortunately')->assertSee('Science Fair');

        $this->withSession(['conflicts' => [[
                'id' => $existing->id, 'venue' => 'Sunrise Hall', 'when' => 'x', 'purpose' => 'Science Fair',
            ], ], '_old_input' => ['applicant_name' => 'Rosa Dela Cruz', 'local_number' => '1234']])
            ->post(route('walkin.contact'), ['reservation_id' => $existing->id])
            ->assertRedirect(route('walkin.create'))
            ->assertSessionHas('contact_info.'.$existing->id.'.email', $student->email);

        $this->assertStringContainsString('Rosa Dela Cruz', $student->notifications()->first()->data['message']);
    }

    public function test_admin_sees_the_walk_in_applicant(): void
    {
        $this->walkInReservation();

        $this->actingAs(User::factory()->create(['role' => 'cpdo_admin']))
            ->get(route('admin.reservations.index'))
            ->assertOk()
            ->assertSee('Rosa Dela Cruz')
            ->assertSee('Walk-in / external');
    }
}
