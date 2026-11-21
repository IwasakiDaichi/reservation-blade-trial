<?php

namespace Tests\Feature;

use App\Models\Reservation;
use App\Models\Department;
use App\Models\User;
use App\Models\Venue;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\Concerns\PicksEventDates;
use Tests\TestCase;

class ReservationVenueTest extends TestCase
{
    use PicksEventDates;
    use RefreshDatabase;

    private function payload(array $override = []): array
    {
        return array_merge([
            'purpose' => 'Org event',
            'reservationType' => 'single',
            'reservedDate' => $this->eventDate(),
            'startTime' => '09:00',
            'endTime' => '10:00',
            'agreed' => '1',
            'applicant_signature' => UploadedFile::fake()->image('sig.png'),
        ], $override);
    }

    private function student(): User
    {
        return User::factory()->create([
            'role' => 'student',
            'department_id' => Department::factory()->create()->id,
            'mobile_number' => '09171234567',
        ]);
    }

    public function test_active_venue_can_be_reserved(): void
    {
        Storage::fake('local');
        $venue = Venue::factory()->create();

        $this->actingAs($this->student())
            ->post(route('reservations.store'), $this->payload(['venue_id' => $venue->id]))
            ->assertRedirect(route('reservations.index'));

        $this->assertDatabaseHas('reservations', ['venue_id' => $venue->id]);
    }

    public function test_maintenance_and_deactivated_venues_cannot_be_reserved(): void
    {
        Storage::fake('local');
        $student = $this->student();

        foreach ([Venue::factory()->maintenance()->create(), Venue::factory()->deactivated()->create()] as $venue) {
            $this->actingAs($student)
                ->post(route('reservations.store'), $this->payload(['venue_id' => $venue->id]))
                ->assertSessionHasErrors('venue_id');
        }

        $this->assertDatabaseCount('reservations', 0);
    }

    public function test_other_venue_needs_a_name(): void
    {
        Storage::fake('local');
        $student = $this->student();

        $this->actingAs($student)
            ->post(route('reservations.store'), $this->payload(['venue_id' => 'other']))
            ->assertSessionHasErrors('otherVenue');

        $this->actingAs($student)
            ->post(route('reservations.store'), $this->payload(['venue_id' => 'other', 'otherVenue' => 'Quadrangle']))
            ->assertRedirect(route('reservations.index'));

        $this->assertDatabaseHas('reservations', ['venue_id' => null, 'other_venue' => 'Quadrangle']);
    }

    public function test_chairs_cannot_exceed_venue_capacity(): void
    {
        Storage::fake('local');
        $venue = Venue::factory()->create(['capacity_max' => 50]);

        $this->actingAs($this->student())
            ->post(route('reservations.store'), $this->payload(['venue_id' => $venue->id, 'chairs' => 80]))
            ->assertSessionHasErrors('chairs');
    }

    public function test_admin_cannot_approve_a_reservation_for_a_venue_under_maintenance(): void
    {
        $admin = User::factory()->create(['role' => 'cpdo_admin']);
        $venue = Venue::factory()->maintenance()->create();
        $student = $this->student();
        $date = $this->eventDate();

        $reservation = Reservation::create([
            'user_id' => $student->id, 'department_id' => $student->department_id, 'mobile_number' => '09171234567',
            'purpose' => 'Org event', 'venue_id' => $venue->id, 'reserved_date' => $date,
            'time' => '09:00 - 10:00', 'start_date' => $date, 'end_date' => $date,
            'start_time' => '09:00', 'end_time' => '10:00', 'agreed' => true, 'status' => 'pending',
        ]);

        $this->actingAs($admin)
            ->patch(route('admin.reservations.updateStatus', [$reservation, 'approved']))
            ->assertSessionHas('error');

        $this->assertSame('pending', $reservation->fresh()->status);
    }
}
