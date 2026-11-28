<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

/** The shop panel belongs to shop accounts that are linked to a shop. */
class EnsureShop
{
    public function handle(Request $request, Closure $next)
    {
        $user = $request->user();
        abort_unless($user?->isShop() && $user->vendor_id, 403);

        return $next($request);
    }
}
