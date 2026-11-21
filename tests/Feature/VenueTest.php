<?php

namespace Tests\Feature;

use App\Enums\VenueStatus;
use App\Models\User;
use App\Models\Venue;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class VenueTest extends TestCase
{
    use RefreshDatabase;

    public function test_students_see_active_and_maintenance_venues_but_not_deactivated_ones(): void
    {
        $active = Venue::factory()->create(['name' => 'Sunrise Hall']);
        $maintenance = Venue::factory()->maintenance()->create(['name' => 'Moonlight Hall']);
        $deactivated = Venue::factory()->deactivated()->create(['name' => 'Hidden Hall']);

        $response = $this->actingAs($this->student())->get(route('venues.index'));

        $response->assertOk();
        $response->assertSee($active->name);
        $response->assertSee($maintenance->name);
        $response->assertDontSee($deactivated->name);
    }

    public function test_students_cannot_open_a_deactivated_venue(): void
    {
        $venue = Venue::factory()->deactivated()->create();

        $this->actingAs($this->student())
            ->get(route('venues.show', $venue))
            ->assertNotFound();
    }

    public function test_students_can_open_venue_details(): void
    {
        $venue = Venue::factory()->create(['amenities' => "Projector\nSound system"]);

        $this->actingAs($this->student())
            ->get(route('venues.show', $venue))
            ->assertOk()
            ->assertSee('Projector')
            ->assertSee('Reserve this venue');
    }

    public function test_maintenance_venues_cannot_be_reserved_from_the_details_page(): void
    {
        $venue = Venue::factory()->maintenance()->create();

        $this->actingAs($this->student())
            ->get(route('venues.show', $venue))
            ->assertOk()
            ->assertDontSee('Reserve this venue')
            ->assertSee('Roof repair');
    }

    public function test_students_cannot_open_the_admin_venue_pages(): void
    {
        $this->actingAs($this->student())
            ->get(route('admin.venues.index'))
            ->assertForbidden();
    }

    public function test_admin_can_add_a_venue(): void
    {
        $response = $this->actingAs($this->admin())->post(route('admin.venues.store'), [
            'name' => 'New Auditorium',
            'capacity_min' => 100,
            'capacity_max' => 150,
            'status' => 'active',
        ]);

        $response->assertRedirect(route('admin.venues.index'));
        $this->assertDatabaseHas('venues', ['name' => 'New Auditorium', 'capacity_max' => 150]);
    }

    public function test_admin_cannot_save_a_maximum_capacity_below_the_minimum(): void
    {
        $this->actingAs($this->admin())->post(route('admin.venues.store'), [
            'name' => 'Broken Hall',
            'capacity_min' => 200,
            'capacity_max' => 100,
            'status' => 'active',
        ])->assertSessionHasErrors('capacity_max');

        $this->assertDatabaseMissing('venues', ['name' => 'Broken Hall']);
    }

    public function test_admin_can_set_a_venue_under_maintenance_and_back_to_active(): void
    {
        $venue = Venue::factory()->create();

        $this->actingAs($this->admin())
            ->patch(route('admin.venues.status', $venue), ['status' => 'maintenance']);
        $this->assertSame(VenueStatus::Maintenance, $venue->fresh()->status);

        $venue->update(['status_note' => 'Painting']);

        $this->actingAs($this->admin())
            ->patch(route('admin.venues.status', $venue), ['status' => 'active']);

        $fresh = $venue->fresh();
        $this->assertSame(VenueStatus::Active, $fresh->status);
        $this->assertNull($fresh->status_note);
    }

    public function test_admin_can_edit_venue_details(): void
    {
        $venue = Venue::factory()->create(['name' => 'Old Name']);

        $this->actingAs($this->admin())->put(route('admin.venues.update', $venue), [
            'name' => 'New Name',
            'capacity_max' => 80,
            'status' => 'deactivated',
        ])->assertRedirect(route('admin.venues.index'));

        $fresh = $venue->fresh();
        $this->assertSame('New Name', $fresh->name);
        $this->assertSame(VenueStatus::Deactivated, $fresh->status);
    }

    private function student(): User
    {
        return User::factory()->create([
            'role' => 'student',
            'department' => 'Engineering',
            'mobile_number' => '555-0134',
        ]);
    }

    private function admin(): User
    {
        return User::factory()->create(['role' => 'cpdo_admin']);
    }
}
