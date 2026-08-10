<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Ties order-changing actions to a browser session that actually opened
 * the table in question.
 *
 * Customers never log in — a table number is the only identity this app
 * has — so before this existed, anything that could reach the site could
 * POST to /table/{any}/order-items and pile food onto a stranger's bill,
 * or delete items off it. Now a session has to have opened that table's
 * page first, and its session is the only one allowed to change that
 * table's tab.
 *
 * Reading a table's page is what grants the session access, and that is
 * a deliberate trade-off: several people sharing a table each need to
 * order from their own phone, and there is no shared secret to
 * distinguish them from anyone else who can load the same URL. So this
 * stops drive-by and scripted tampering, not a determined attacker who
 * is willing to load the page first. Closing that gap properly needs a
 * per-table secret printed on the table (encoded in its QR code), which
 * is a product decision, not something to smuggle in here — see
 * docs/ROADMAP.md.
 */
class EnsureTableSession
{
    public const SESSION_KEY = 'kafeign.tables_opened';

    public function handle(Request $request, Closure $next): Response
    {
        $table = $request->route('table');

        if (! $table) {
            return $next($request);
        }

        $opened = $request->session()->get(self::SESSION_KEY, []);

        // Safe, idempotent requests are how a customer "sits down" at a
        // table: viewing it registers this browser as being at it.
        if ($request->isMethodSafe()) {
            if (! in_array($table->number, $opened, true)) {
                $opened[] = $table->number;
                $request->session()->put(self::SESSION_KEY, $opened);
            }

            return $next($request);
        }

        // Anything that changes the tab must come from a session that
        // opened this table.
        if (! in_array($table->number, $opened, true)) {
            abort(403, 'Buka dulu halaman meja ini sebelum memesan.');
        }

        return $next($request);
    }
}
