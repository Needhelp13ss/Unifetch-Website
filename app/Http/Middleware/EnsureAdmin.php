<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

/** Only the admin account can open the admin panel. */
class EnsureAdmin
{
    public function handle(Request $request, Closure $next)
    {
        abort_unless($request->user()?->isAdmin(), 403);

        return $next($request);
    }
}
