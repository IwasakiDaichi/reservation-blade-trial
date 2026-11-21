<?php

namespace Database\Seeders;

use App\Enums\VenueStatus;
use App\Models\Venue;
use Illuminate\Database\Seeder;

class VenueSeeder extends Seeder
{
    /**
     * Adds the venues already used by the reservation form.
     * Safe to run again: venues that exist (and any edits the CPDO made) are left alone.
     */
    public function run(): void
    {
        $venues = [
            ['UB Cardinals Gym',        2000, 2500, null],
            ['RCB Dome',                null, 200,  'Seating: 200 armchairs.'],
            ['FGB Hall',                150,  200,  null],
            ['Centennial Hall',         100,  150,  null],
            ['AMS Hall',                null, 120,  'Seating: 120 armchairs.'],
            ['Legacy Multipurpose Hall', 150, 300,  null],
            ['UB Student Center',       null, 100,  null],
            ['Alumni AVR',              null, 55,   null],
            ['Centennial AVR',          null, 35,   null],
        ];

        foreach ($venues as [$name, $min, $max, $description]) {
            Venue::firstOrCreate(
                ['name' => $name],
                [
                    'capacity_min' => $min,
                    'capacity_max' => $max,
                    'description' => $description,
                    'status' => VenueStatus::Active,
                ]
            );
        }
    }
}
