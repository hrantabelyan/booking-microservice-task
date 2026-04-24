<?php

declare(strict_types=1);

return [
    /*
    | Maximum allowed booking duration in minutes.
    | Requests where (ends_at - starts_at) exceeds this value are rejected.
    */
    'max_duration_minutes' => (int) env('BOOKING_MAX_DURATION_MINUTES', 480),
];
