<?php

namespace Tests\Feature;

use App\Models\Reservation;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ReservationPrintableFormTest extends TestCase
{
    use RefreshDatabase;

    public function test_owner_can_open_a_printable_copy_of_the_official_form(): void
    {
        $user = $this->student();
        $reservation = $this->reservationFor($user);

        $response = $this->actingAs($user)->get(route('reservations.print', $reservation));

        $response->assertOk();
        $response->assertHeader('Content-Type', 'application/pdf');
        $this->assertStringStartsWith('%PDF-', $response->getContent());
        $this->assertStringContainsString('/MediaBox [0 0 612.00 936.00]', $response->getContent());
        $this->assertStringContainsString('/Subtype /Image', $response->getContent());
        $this->assertStringContainsString('Student organization event', $response->getContent());
        $this->assertStringContainsString('UB Cardinals Gym', $response->getContent());
        $this->assertStringContainsString('[ ] Approved', $response->getContent());
        $this->assertStringContainsString('[ ] Disapproved', $response->getContent());
    }

    public function test_printable_form_marks_the_current_approval_status(): void
    {
        $admin = User::factory()->create([
            'role' => 'cpdo_admin',
        ]);
        $reservation = $this->reservationFor($this->student());

        $reservation->update([
            'status' => 'approved',
        ]);
        $approvedPdf = $this->actingAs($admin)
            ->get(route('reservations.print', $reservation))
            ->getContent();

        $this->assertStringContainsString('[X] Approved', $approvedPdf);
        $this->assertStringContainsString('[ ] Disapproved', $approvedPdf);

        $reservation->update([
            'status' => 'rejected',
        ]);
        $disapprovedPdf = $this->actingAs($admin)
            ->get(route('reservations.print', $reservation))
            ->getContent();

        $this->assertStringContainsString('[ ] Approved', $disapprovedPdf);
        $this->assertStringContainsString('[X] Disapproved', $disapprovedPdf);
    }

    public function test_student_cannot_print_another_students_reservation(): void
    {
        $owner = $this->student();
        $otherStudent = $this->student();
        $reservation = $this->reservationFor($owner);

        $this->actingAs($otherStudent)
            ->get(route('reservations.print', $reservation))
            ->assertForbidden();
    }

    public function test_student_cannot_upload_staff_signatures(): void
    {
        $student = $this->student();
        $reservation = $this->reservationFor($student);

        $this->actingAs($student)
            ->put(route('admin.reservations.signatures.update', $reservation), [
                'approval_signature' => UploadedFile::fake()->image('approval.png'),
            ])
            ->assertForbidden();
    }

    public function test_applicant_and_admin_signatures_are_saved_privately(): void
    {
        Storage::fake('local');
        $student = $this->student();

        $this->actingAs($student)->post(route('reservations.store'), [
            'purpose' => 'Student organization event',
            'venue' => 'Campus Gym',
            'reservationType' => 'single',
            'reservedDate' => now()->addDays(3)->toDateString(),
            'startTime' => '09:00',
            'endTime' => '10:00',
            'soundSystem' => 'Two sets',
            'agreed' => '1',
            'applicant_signature' => UploadedFile::fake()->image('applicant.png')->size(10 * 1024),
        ])->assertRedirect(route('reservations.index'));

        $reservation = Reservation::query()->where('user_id', $student->id)->firstOrFail();
        $this->assertSame('Two sets', $reservation->sound_system_details);
        Storage::disk('local')->assertExists($reservation->applicant_signature_path);

        $admin = User::factory()->create([
            'role' => 'cpdo_admin',
        ]);

        $this->actingAs($admin)->put(route('admin.reservations.signatures.update', $reservation), [
            'security_signature' => UploadedFile::fake()->image('security.png'),
            'endorsement_signature' => UploadedFile::fake()->image('endorsement.png'),
            'reservation_officer_signature' => UploadedFile::fake()->image('officer.png'),
            'approval_signature' => UploadedFile::fake()->image('approval.png'),
        ])->assertRedirect(route('admin.reservations.signatures.edit', $reservation));

        $reservation->refresh();
        Storage::disk('local')->assertExists($reservation->security_signature_path);
        Storage::disk('local')->assertExists($reservation->endorsement_signature_path);
        Storage::disk('local')->assertExists($reservation->reservation_officer_signature_path);
        Storage::disk('local')->assertExists($reservation->approval_signature_path);

        $this->actingAs($admin)
            ->get(route('reservations.print', $reservation))
            ->assertOk()
            ->assertHeader('Content-Type', 'application/pdf');
    }

    public function test_applicant_signature_over_ten_megabytes_is_rejected_with_a_clear_message(): void
    {
        $student = $this->student();

        $response = $this->actingAs($student)->from(route('reservations.create'))->post(route('reservations.store'), [
            'purpose' => 'Student organization event',
            'venue' => 'Campus Gym',
            'reservationType' => 'single',
            'reservedDate' => now()->addDays(3)->toDateString(),
            'startTime' => '09:00',
            'endTime' => '10:00',
            'agreed' => '1',
            'applicant_signature' => UploadedFile::fake()->image('applicant.png')->size(10 * 1024 + 1),
        ]);

        $response->assertRedirect(route('reservations.create'));
        $response->assertSessionHasErrors([
            'applicant_signature' => 'The applicant signature must be 10 MB or smaller.',
        ]);
        $this->assertDatabaseCount('reservations', 0);
    }

    private function student(): User
    {
        return User::factory()->create([
            'role' => 'student',
            'department' => 'Engineering',
            'mobile_number' => '555-0134',
        ]);
    }

    private function reservationFor(User $user): Reservation
    {
        $date = now()->addDays(3)->toDateString();

        return Reservation::create([
            'user_id' => $user->id,
            'department' => $user->department,
            'mobile_number' => $user->mobile_number,
            'purpose' => 'Student organization event',
            'venue' => 'Campus Gym',
            'reserved_date' => $date,
            'time' => '09:00 - 10:00',
            'start_date' => $date,
            'end_date' => $date,
            'start_time' => '09:00',
            'end_time' => '10:00',
            'status' => 'pending',
        ]);
    }
}
