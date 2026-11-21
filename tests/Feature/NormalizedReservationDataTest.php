<?php

namespace Tests\Feature;

use App\Enums\SignatureRole;
use App\Models\Department;
use App\Models\Reservation;
use App\Models\User;
use App\Models\Venue;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\Concerns\PicksEventDates;
use Tests\TestCase;

class NormalizedReservationDataTest extends TestCase
{
    use PicksEventDates;
    use RefreshDatabase;

    public function test_equipment_is_saved_as_rows_not_columns(): void
    {
        Storage::fake('local');
        $student = User::factory()->create([
            'role' => 'student',
            'department_id' => Department::factory()->create()->id,
            'mobile_number' => '09171234567',
        ]);
        $venue = Venue::factory()->create(['capacity_max' => 500]);

        $this->actingAs($student)->post(route('reservations.store'), [
            'purpose' => 'Org event',
            'venue_id' => $venue->id,
            'reservationType' => 'single',
            'reservedDate' => $this->eventDate(),
            'startTime' => '09:00',
            'endTime' => '10:00',
            'soundSystem' => 'Two speakers',
            'podium' => 'Yes',
            'tables' => 5,
            'chairs' => 100,
            'agreed' => '1',
            'applicant_signature' => UploadedFile::fake()->image('sig.png'),
        ])->assertRedirect(route('reservations.index'));

        $reservation = Reservation::with(['equipment', 'signatures'])->firstOrFail();

        $this->assertSame('Two speakers', $reservation->equipmentDetails('sound-system'));
        $this->assertTrue($reservation->hasEquipment('podium'));
        $this->assertFalse($reservation->hasEquipment('flags'));
        $this->assertSame(5, $reservation->equipmentQuantity('tables'));
        $this->assertSame(100, $reservation->equipmentQuantity('chairs'));
        $this->assertSame($student->department_id, $reservation->department_id);
        $this->assertNotNull($reservation->signaturePath(SignatureRole::Applicant));
    }

    public function test_equipment_catalogue_is_seeded_by_the_migration(): void
    {
        $this->assertDatabaseHas('equipment', ['slug' => 'sound-system', 'input_type' => 'details']);
        $this->assertDatabaseHas('equipment', ['slug' => 'chairs', 'input_type' => 'quantity']);
        $this->assertDatabaseHas('departments', ['name' => 'School of Nursing', 'is_listed' => true]);
    }
}
