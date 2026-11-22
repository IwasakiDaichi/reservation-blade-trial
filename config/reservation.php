<?php

return [
    // Reservations must be made at least this many days before the event (CPDO rule: two weeks).
    'lead_days' => 14,

    // Students must cancel at least this many days before the reserved date (CPDO rule: three days).
    'cancellation_notice_days' => 3,

    // Students log in with a University of Baguio Google account. Comma-separated list in .env,
    // e.g. UB_EMAIL_DOMAINS=ubaguio.edu. Subdomains (s.ubaguio.edu) are included. Leave empty to allow any Google account.
    'ub_email_domains' => array_values(array_filter(array_map('trim', explode(',', (string) env('UB_EMAIL_DOMAINS', 'ubaguio.edu'))))),

    // Also e-mail students when CPDO affects their reservation. Needs working mail settings in .env.
    'notify_by_email' => (bool) env('RESERVATION_NOTIFY_EMAIL', false),
];
