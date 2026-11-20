<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

/** The admin only checks things; they cannot order, shop or use a wallet. */
class EnsureNotAdmin
{
    public function handle(Request $request, Closure $next)
    {
        if ($request->user()?->isAdmin()) {
            if ($request->expectsJson()) {
                return response()->json(['ok' => false, 'message' => 'Admins cannot place orders.'], 403);
            }

            return redirect()->route('admin.dashboard')
                ->with('error', 'The admin account can’t place orders. Use a customer account to order.');
        }

        return $next($request);
    }
}
