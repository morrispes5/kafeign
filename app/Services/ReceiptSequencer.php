<?php

namespace App\Services;

use Carbon\CarbonImmutable;
use DateTimeInterface;
use Illuminate\Support\Facades\DB;

/**
 * The only place in the codebase that writes `receipt_counters`.
 *
 * Mints and returns the next receipt number for a business day, in the
 * format {business_date:Ymd}-{sequence:04d}, e.g. "20260812-0001" —
 * sortable, printable, and self-describing about which day it belongs to.
 *
 * Must be called from inside the same DB::transaction() as the
 * settlement update that will store the result (see
 * Admin\OrderController::clear()): if that update later finds the order
 * no longer `ongoing` and throws, the whole transaction rolls back,
 * undoing this mint too, so no number is skipped for a payment that
 * didn't actually happen.
 *
 * Check-and-write here must be a SINGLE statement, same rule as
 * App\Services\StockLedger::take() and for the same reason:
 * lockForUpdate() is a no-op on this app's SQLite connection. A plain
 * increment()-then-separate-read was considered and rejected: two
 * concurrent payments could each increment the row and then each read
 * back whichever value happened to be there at read time — not
 * necessarily the value their own increment produced — handing out the
 * same number to two different orders. That would only surface later as
 * a confusing unique-constraint failure on `orders.receipt_number`,
 * instead of being impossible by construction.
 *
 * The upsert below closes that gap: SQLite's `RETURNING` hands back the
 * post-write value of THIS statement, atomically, so there is no window
 * between "advance the counter" and "read what I advanced it to" for
 * another writer to land in. Requires SQLite >= 3.35 for RETURNING and
 * >= 3.24 for the upsert clause — confirmed 3.49.2 in this environment;
 * re-check `sqlite_version()` before deploying anywhere else.
 */
class ReceiptSequencer
{
    private const FORMAT = '%s-%04d';

    public function next(DateTimeInterface $businessDate): string
    {
        $date = CarbonImmutable::instance($businessDate);
        $now = now();

        $row = DB::selectOne(
            <<<'SQL'
                insert into receipt_counters (business_date, last_number, created_at, updated_at)
                values (?, 1, ?, ?)
                on conflict (business_date) do update set
                    last_number = last_number + 1,
                    updated_at = excluded.updated_at
                returning last_number
            SQL,
            [$date->toDateString(), $now, $now]
        );

        return sprintf(self::FORMAT, $date->format('Ymd'), (int) $row->last_number);
    }
}
