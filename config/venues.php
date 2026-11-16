<?php

// Keys must match the venue values saved by the reservation form.
// Colors come from the CPDO "Calendar of Activities" sheet.
// A row is colored ONLY when the reservation is approved.
// Pending reservations, and any venue not listed here, stay white.
return [
    'venues' => [
        'Campus Gym'        => ['label' => 'UB GYM',          'bg' => '#FF00FF', 'text' => '#111827'],
        'Alumni AVR'        => ['label' => 'ALUMNI AVR',      'bg' => '#00FF00', 'text' => '#111827'],
        'RCB Dome'          => ['label' => 'RCB DOME',        'bg' => '#00FFFF', 'text' => '#111827'],
        'AMS Hall'          => ['label' => 'AMS HALL',        'bg' => '#4A86E8', 'text' => '#FFFFFF'],
        'Centennial Hall'   => ['label' => 'CENTENNIAL HALL', 'bg' => '#FF9900', 'text' => '#111827'],
        'FGB Hall'          => ['label' => 'FGB HALL',        'bg' => '#93C47D', 'text' => '#111827'],
        'UB Student Center' => ['label' => 'STUDENT CENTER',  'bg' => '#FFFF00', 'text' => '#111827'],
        'Legacy'            => ['label' => 'LEGACY HALL',     'bg' => '#C27BA0', 'text' => '#111827'],
        'Centennial AVR'    => ['label' => 'CENTENNIAL AVR',  'bg' => '#FF9900', 'text' => '#111827'],
    ],

    // Used for pending reservations and venues with no color.
    'default' => ['bg' => '#FFFFFF', 'text' => '#111827'],
];