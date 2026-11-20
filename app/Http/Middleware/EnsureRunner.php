<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

/** The runner job board belongs to approved runners only. */
class EnsureRunner
{
    public function handle(Request $request, Closure $next)
    {
        if (!$request->user()?->isRunner()) {
            return redirect()->route('dashboard')
                ->with('error', 'The Runner Jobs board is only for approved UniFetch runners.');
        }

        return $next($request);
    }
}
