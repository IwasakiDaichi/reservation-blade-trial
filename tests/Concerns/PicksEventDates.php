<?php

namespace Tests\Concerns;

trait PicksEventDates
{
    /** A valid event date: past the two-week lead time and never a Sunday. */
    protected function eventDate(int $daysAhead = 20): string
    {
        $date = now()->addDays($daysAhead);

        if ($date->isSunday()) {
            $date = $date->addDay();
        }

        return $date->toDateString();
    }
}
