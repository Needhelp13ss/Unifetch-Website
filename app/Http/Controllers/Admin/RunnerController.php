<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;

class RunnerController extends Controller
{
    public function index()
    {
        return view('admin.runners', [
            'pending' => User::where('role', 'customer')->where('runner_status', 'pending')->oldest('runner_applied_at')->get(),
            'runners' => User::where('role', 'runner')->withCount([
                'runnerOrders as delivered_count' => fn ($q) => $q->where('status', 'delivered'),
                'runnerOrders as active_count' => fn ($q) => $q->whereIn('status', ['preparing', 'on_the_way']),
            ])->orderBy('name')->get(),
            'rejected' => User::where('role', 'customer')->where('runner_status', 'rejected')->orderBy('name')->get(),
        ]);
    }

    public function approve(User $user)
    {
        abort_if($user->isAdmin(), 403);
        $user->forceFill(['role' => 'runner', 'runner_status' => 'approved'])->save();

        return back()->with('success', "{$user->name} is now an approved runner.");
    }

    public function reject(User $user)
    {
        abort_if($user->isAdmin(), 403);
        $user->forceFill(['role' => 'customer', 'runner_status' => 'rejected'])->save();

        return back()->with('success', "{$user->name}'s application was rejected.");
    }

    /** Take runner access away. They go back to being a normal customer. */
    public function revoke(User $user)
    {
        abort_if($user->isAdmin(), 403);

        if ($user->runnerOrders()->whereIn('status', ['preparing', 'on_the_way'])->exists()) {
            return back()->with('error', "{$user->name} still has active jobs. Reassign or finish them first.");
        }

        $user->forceFill(['role' => 'customer', 'runner_status' => 'rejected'])->save();

        return back()->with('success', "{$user->name} is no longer a runner.");
    }
}
