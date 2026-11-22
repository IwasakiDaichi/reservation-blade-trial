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

class CpdoAlertsTest extends TestCase
{
    use PicksEventDates;
    use RefreshDatabase;

    private function admin(): User
    {
        return User::factory()->create(['role' => 'cpdo_admin']);
    }

    private function student(): User
    {
        return User::factory()->create([
            'role' => 'student',
            'department_id' => Department::factory()->create()->id,
            'mobile_number' => '09171234567',
        ]);
    }

    private function booking(User $user, Venue $venue, string $status, string $start = '09:00', string $end = '11:00'): Reservation
    {
        $date = $this->eventDate();

        return Reservation::create([
            'user_id' => $user->id, 'department_id' => $user->department_id, 'mobile_number' => '0917',
            'purpose' => 'Event '.$status, 'venue_id' => $venue->id, 'reserved_date' => $date, 'time' => $start.' - '.$end,
            'start_date' => $date, 'end_date' => $date, 'start_time' => $start, 'end_time' => $end,
            'agreed' => true, 'status' => $status,
        ]);
    }

    public function test_every_cpdo_account_is_told_about_a_new_request(): void
    {
        Storage::fake('local');
        $first = $this->admin();
        $second = $this->admin();
        $student = $this->student();
        $venue = Venue::factory()->create();

        $this->actingAs($student)->post(route('reservations.store'), [
            'purpose' => 'Org event', 'venue_id' => $venue->id, 'reservationType' => 'single',
            'reservedDate' => $this->eventDate(), 'startTime' => '09:00', 'endTime' => '10:00',
            'agreed' => '1', 'applicant_signature' => UploadedFile::fake()->image('s.png'),
        ])->assertSessionHasNoErrors();

        foreach ([$first, $second] as $admin) {
            $this->assertSame(1, $admin->notifications()->count());
            $this->assertSame('New reservation request', $admin->notifications()->first()->data['title']);
        }

        $this->assertSame(0, $student->notifications()->count());
    }

    public function test_walk_in_requests_also_alert_cpdo(): void
    {
        Storage::fake('local');
        $admin = $this->admin();

        $this->post(route('walkin.store'), [
            'applicant_name' => 'Rosa Dela Cruz', 'local_number' => '1234',
            'purpose' => 'Homecoming', 'venue_id' => Venue::factory()->create()->id, 'reservationType' => 'single',
            'reservedDate' => $this->eventDate(), 'startTime' => '09:00', 'endTime' => '10:00',
            'agreed' => '1', 'applicant_signature' => UploadedFile::fake()->image('s.png'),
        ])->assertSessionHasNoErrors();

        $this->assertStringContainsString('Rosa Dela Cruz', $admin->notifications()->first()->data['message']);
    }

    public function test_student_cancelling_alerts_cpdo(): void
    {
        $admin = $this->admin();
        $student = $this->student();
        $reservation = $this->booking($student, Venue::factory()->create(), 'pending');

        $this->actingAs($student)->patch(route('reservations.cancel', $reservation))->assertSessionHas('success');

        $this->assertSame('Reservation cancelled by the student', $admin->notifications()->first()->data['title']);
    }

    public function test_admin_menu_shows_the_pending_count_and_the_notifications_page_opens(): void
    {
        $admin = $this->admin();
        $venue = Venue::factory()->create();
        $this->booking($this->student(), $venue, 'pending', '08:00', '09:00');
        $this->booking($this->student(), $venue, 'pending', '12:00', '13:00');

        $this->actingAs($admin)->get(route('admin.reservations.index'))
            ->assertOk()
            ->assertSee('title="Pending requests">2', false);

        $this->actingAs($admin)->get(route('notifications.index'))->assertOk();
    }

    public function test_cpdo_cannot_approve_a_request_that_overlaps_an_approved_one(): void
    {
        $venue = Venue::factory()->create();
        $approved = $this->booking($this->student(), $venue, 'approved', '09:00', '11:00');
        $pending = $this->booking($this->student(), $venue, 'pending', '10:00', '12:00');

        $this->actingAs($this->admin())
            ->patch(route('admin.reservations.updateStatus', [$pending, 'approved']))
            ->assertSessionHas('error');

        $this->assertSame('pending', $pending->fresh()->status);
        $this->assertSame('approved', $approved->fresh()->status);
    }

    public function test_cpdo_can_approve_back_to_back_requests(): void
    {
        $venue = Venue::factory()->create();
        $this->booking($this->student(), $venue, 'approved', '09:00', '11:00');
        $pending = $this->booking($this->student(), $venue, 'pending', '11:00', '12:00');

        $this->actingAs($this->admin())
            ->patch(route('admin.reservations.updateStatus', [$pending, 'approved']))
            ->assertSessionHas('success');

        $this->assertSame('approved', $pending->fresh()->status);
    }

    public function test_overlapping_pending_requests_are_flagged_in_the_list(): void
    {
        $venue = Venue::factory()->create();
        $a = $this->booking($this->student(), $venue, 'pending', '09:00', '11:00');
        $b = $this->booking($this->student(), $venue, 'pending', '10:00', '12:00');

        $this->actingAs($this->admin())->get(route('admin.reservations.index'))
            ->assertOk()
            ->assertSee('Overlaps with')
            ->assertSee('#'.$a->id.' (pending)')
            ->assertSee('#'.$b->id.' (pending)');
    }
}
