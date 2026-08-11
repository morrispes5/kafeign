<?php

namespace App\Support;

use Carbon\CarbonImmutable;
use DateTimeInterface;

/**
 * Turns a wall-clock moment into the business day it belongs to, per
 * config('kafeign.business_day.start_hour') — see that config's own doc
 * comment for the 04:00-cutoff rationale ("pelunasan jam 00:10 masuk ke
 * hari sebelumnya"). Only ever consulted at the moment of payment, to
 * stamp Order::business_date exactly once — never re-derived for an
 * already-paid order, so changing the cutoff hour later cannot
 * retroactively move an already-settled order into a different day.
 *
 * APP_TIMEZONE is already Asia/Jakarta, so `now()` is already correct
 * local wall-clock time — nothing here does any timezone conversion of
 * its own.
 */
class BusinessDate
{
    public static function forMoment(DateTimeInterface $moment): CarbonImmutable
    {
        $startHour = (int) config('kafeign.business_day.start_hour');
        $date = CarbonImmutable::instance($moment);

        // Closed lower bound to match the config comment's own
        // [D 04:00, D+1 04:00) notation: exactly 04:00:00 belongs to the
        // day starting there, not the one before it.
        return ($date->hour < $startHour ? $date->subDay() : $date)->startOfDay();
    }
}
