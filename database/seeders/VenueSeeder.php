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
            ['UB Cardinals Gym',        2000, 2500, null,                      'UB GYM',          '#FF00FF', '#111827'],
            ['RCB Dome',                null, 200,  'Seating: 200 armchairs.', 'RCB DOME',        '#00FFFF', '#111827'],
            ['FGB Hall',                150,  200,  null,                      'FGB HALL',        '#93C47D', '#111827'],
            ['Centennial Hall',         100,  150,  null,                      'CENTENNIAL HALL', '#FF9900', '#111827'],
            ['AMS Hall',                null, 120,  'Seating: 120 armchairs.', 'AMS HALL',        '#4A86E8', '#FFFFFF'],
            ['Legacy Multipurpose Hall', 150, 300,  null,                      'LEGACY HALL',     '#C27BA0', '#111827'],
            ['UB Student Center',       null, 100,  null,                      'STUDENT CENTER',  '#FFFF00', '#111827'],
            ['Alumni AVR',              null, 55,   null,                      'ALUMNI AVR',      '#00FF00', '#111827'],
            ['Centennial AVR',          null, 35,   null,                      'CENTENNIAL AVR',  '#FF9900', '#111827'],
        ];

        foreach ($venues as [$name, $min, $max, $description, $short, $bg, $text]) {
            Venue::firstOrCreate(
                ['name' => $name],
                [
                    'capacity_min' => $min,
                    'capacity_max' => $max,
                    'description' => $description,
                    'short_name' => $short,
                    'calendar_color' => $bg,
                    'calendar_text_color' => $text,
                    'status' => VenueStatus::Active,
                ]
            );
        }
    }
}
