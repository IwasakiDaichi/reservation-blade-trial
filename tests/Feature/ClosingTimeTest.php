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

class ClosingTimeTest extends TestCase
{
    use PicksEventDates;
    use RefreshDatabase;

    private function submit(array $override): \Illuminate\Testing\TestResponse
    {
        Storage::fake('local');
        $student = User::factory()->create([
            'role' => 'student',
            'department_id' => Department::factory()->create()->id,
            'mobile_number' => '09171234567',
        ]);

        return $this->actingAs($student)->post(route('reservations.store'), array_merge([
            'purpose' => 'Org event',
            'venue_id' => Venue::factory()->create()->id,
            'reservationType' => 'single',
            'reservedDate' => $this->eventDate(),
            'startTime' => '18:00',
            'endTime' => '20:00',
            'agreed' => '1',
            'applicant_signature' => UploadedFile::fake()->image('sig.png'),
        ], $override));
    }

    public function test_activity_must_end_by_nine_pm(): void
    {
        $this->submit(['startTime' => '19:00', 'endTime' => '21:30'])
            ->assertSessionHasErrors(['endTime' => 'Activities must end by 9:00 PM.']);

        $this->assertDatabaseCount('reservations', 0);
    }

    public function test_activity_ending_exactly_at_nine_pm_is_allowed(): void
    {
        $this->submit(['startTime' => '19:00', 'endTime' => '21:00'])->assertSessionHasNoErrors();

        $this->assertDatabaseCount('reservations', 1);
    }

    public function test_form_stops_late_times_and_sundays_on_the_page(): void
    {
        $student = User::factory()->create([
            'role' => 'student',
            'department_id' => Department::factory()->create()->id,
            'mobile_number' => '09171234567',
        ]);

        $this->actingAs($student)->get(route('reservations.create'))
            ->assertOk()
            ->assertSee('max="21:00"', false)
            ->assertSee('id="dateWarning"', false)
            ->assertSee('id="timeWarning"', false);
    }
}
