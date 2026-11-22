<?php

namespace Database\Factories;

use App\Enums\VenueStatus;
use App\Models\Venue;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Venue>
 */
class VenueFactory extends Factory
{
    public function definition(): array
    {
        return [
            'name' => fake()->unique()->words(2, true).' Hall',
            'description' => fake()->sentence(),
            'location' => fake()->words(3, true),
            'capacity_min' => null,
            'capacity_max' => fake()->numberBetween(20, 300),
            'status' => VenueStatus::Active,
        ];
    }

    public function maintenance(): static
    {
        return $this->state(fn () => [
            'status' => VenueStatus::Maintenance,
            'status_note' => 'Roof repair',
        ]);
    }

    public function deactivated(): static
    {
        return $this->state(fn () => ['status' => VenueStatus::Deactivated]);
    }
}
