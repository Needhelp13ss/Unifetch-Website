<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

/** Remembers when someone last used the system (for the active / inactive users chart). */
class TrackActivity
{
    /** Someone who used UniFetch in the last 30 days is "active". */
    public const ACTIVE_DAYS = 30;

    public function handle(Request $request, Closure $next)
    {
        $user = $request->user();

        if ($user && (!$user->last_active_at || $user->last_active_at->diffInMinutes(now()) >= 5)) {
            $user->forceFill(['last_active_at' => now()])->saveQuietly();
        }

        return $next($request);
    }
}
