<?php

return [
    // Reservations must be made at least this many days before the event (CPDO rule: two weeks).
    'lead_days' => 14,

    // Students must cancel at least this many days before the reserved date (CPDO rule: three days).
    'cancellation_notice_days' => 3,

    // Also e-mail students when CPDO affects their reservation. Needs working mail settings in .env.
    'notify_by_email' => (bool) env('RESERVATION_NOTIFY_EMAIL', false),
];
