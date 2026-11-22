<?php

namespace Tests\Feature;

use App\Models\Department;
use App\Models\Reservation;
use App\Models\User;
use App\Models\Venue;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\PicksEventDates;
use Tests\TestCase;

class CalendarFilterTest extends TestCase
{
    use PicksEventDates;
    use RefreshDatabase;

    private function book(Venue $venue, string $purpose): Reservation
    {
        $student = User::factory()->create([
            'role' => 'student',
            'department_id' => Department::factory()->create()->id,
            'mobile_number' => '09171234567',
        ]);
        $date = $this->eventDate();

        return Reservation::create([
            'user_id' => $student->id,
            'department_id' => $student->department_id,
            'mobile_number' => $student->mobile_number,
            'purpose' => $purpose,
            'venue_id' => $venue->id,
            'reserved_date' => $date,
            'time' => '09:00 - 11:00',
            'start_date' => $date,
            'end_date' => $date,
            'start_time' => '09:00',
            'end_time' => '11:00',
            'agreed' => true,
            'status' => 'approved',
        ]);
    }

    private function purposesShown(array $query = []): array
    {
        $admin = User::factory()->create(['role' => 'cpdo_admin']);
        $month = substr($this->eventDate(), 0, 7);

        return collect(
            $this->actingAs($admin)->get(route('admin.calendar', ['month' => $month] + $query))->viewData('days')
        )->flatten(1)->pluck('activity')->sort()->values()->all();
    }

    public function test_calendar_shows_every_venue_by_default(): void
    {
        $this->book(Venue::factory()->create(['calendar_color' => '#111111']), 'Gym event');
        $this->book(Venue::factory()->create(['calendar_color' => '#222222']), 'Hall event');

        $this->assertSame(['Gym event', 'Hall event'], $this->purposesShown());
    }

    public function test_calendar_can_be_filtered_by_ticked_venues(): void
    {
        $gym = Venue::factory()->create(['calendar_color' => '#111111']);
        $hall = Venue::factory()->create(['calendar_color' => '#222222']);
        $avr = Venue::factory()->create(['calendar_color' => '#333333']);
        $this->book($gym, 'Gym event');
        $this->book($hall, 'Hall event');
        $this->book($avr, 'AVR event');

        $this->assertSame(['Gym event'], $this->purposesShown(['filtered' => 1, 'venues' => [$gym->id]]));
        $this->assertSame(['Gym event', 'Hall event'], $this->purposesShown(['filtered' => 1, 'venues' => [$gym->id, $hall->id]]));
    }

    public function test_unticking_every_venue_shows_nothing(): void
    {
        $this->book(Venue::factory()->create(['calendar_color' => '#111111']), 'Gym event');

        $this->assertSame([], $this->purposesShown(['filtered' => 1]));
    }

    public function test_filter_checkboxes_are_on_the_page_and_calendar_is_in_the_top_navigation(): void
    {
        $venue = Venue::factory()->create(['calendar_color' => '#111111']);
        $admin = User::factory()->create(['role' => 'cpdo_admin']);

        $this->actingAs($admin)->get(route('admin.calendar'))
            ->assertOk()
            ->assertSee('Filter by venue')
            ->assertSee('name="venues[]" value="'.$venue->id.'"', false)
            ->assertSee(route('admin.calendar'), false)
            ->assertSee('Calendar of Activities');

        $this->actingAs($admin)->get(route('admin.dashboard'))
            ->assertOk()
            ->assertSee(route('admin.calendar'), false);
    }

    public function test_venues_sharing_a_color_get_one_checkbox(): void
    {
        // The migration already seeds both venues with the same orange.
        $hall = Venue::where('name', 'Centennial Hall')->firstOrFail();
        $avr = Venue::where('name', 'Centennial AVR')->firstOrFail();
        $gym = Venue::factory()->create(['calendar_color' => '#111111']);
        $this->book($hall, 'Hall event');
        $this->book($avr, 'AVR event');
        $this->book($gym, 'Gym event');
        $admin = User::factory()->create(['role' => 'cpdo_admin']);

        $page = $this->actingAs($admin)->get(route('admin.calendar'))
            ->assertOk()
            ->assertSee('CENTENNIAL HALL / AVR')
            ->assertSee('OTHER VENUES');

        $groups = $page->viewData('groups');
        $this->assertSame(1, $groups->filter(fn ($g) => str_contains($g['label'], 'CENTENNIAL'))->count());

        $value = $groups->first(fn ($g) => str_contains($g['label'], 'CENTENNIAL'))['value'];
        $this->assertSame(['AVR event', 'Hall event'], $this->purposesShown(['filtered' => 1, 'venues' => [$value]]));
    }

    public function test_other_venues_box_controls_venues_that_are_not_in_the_list(): void
    {
        $gym = Venue::factory()->create(['calendar_color' => '#111111']);
        $this->book($gym, 'Gym event');
        $student = User::factory()->create([
            'role' => 'student',
            'department_id' => Department::factory()->create()->id,
            'mobile_number' => '09171234567',
        ]);
        $date = $this->eventDate();
        Reservation::create([
            'user_id' => $student->id, 'department_id' => $student->department_id, 'mobile_number' => '09171234567',
            'purpose' => 'Plaza event', 'venue_id' => null, 'other_venue' => 'School Plaza',
            'reserved_date' => $date, 'time' => '09:00 - 11:00', 'start_date' => $date, 'end_date' => $date,
            'start_time' => '09:00', 'end_time' => '11:00', 'agreed' => true, 'status' => 'approved',
        ]);

        $this->assertSame(['Gym event', 'Plaza event'], $this->purposesShown());
        $this->assertSame(['Gym event'], $this->purposesShown(['filtered' => 1, 'venues' => [$gym->id]]));
        $this->assertSame(['Plaza event'], $this->purposesShown(['filtered' => 1, 'venues' => ['other']]));
    }
}
