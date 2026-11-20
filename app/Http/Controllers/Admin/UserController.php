<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Support\Wallet;
use Illuminate\Http\Request;
use RuntimeException;

class UserController extends Controller
{
    public function index(Request $request)
    {
        $q = trim((string) $request->query('q', ''));
        $role = $request->query('role');

        $users = User::query()
            ->when(array_key_exists((string) $role, User::ROLES), fn ($w) => $w->where('role', $role))
            ->when($q !== '', fn ($w) => $w->where(fn ($x) => $x
                ->where('name', 'like', "%{$q}%")->orWhere('email', 'like', "%{$q}%")->orWhere('student_id', 'like', "%{$q}%")))
            ->withCount('orders')
            ->orderBy('name')
            ->paginate(20)->withQueryString();

        return view('admin.users', ['users' => $users, 'q' => $q, 'role' => $role]);
    }

    public function show(User $user)
    {
        return view('admin.user', [
            'person' => $user,
            'orders' => $user->orders()->latest()->limit(15)->get(),
            'jobs' => $user->runnerOrders()->latest()->limit(15)->get(),
            'transactions' => $user->walletTransactions()->limit(20)->get(),
        ]);
    }

    /** Add or take away wallet money (corrections, cash-in received by the admin). */
    public function adjustWallet(Request $request, User $user)
    {
        abort_if($user->isAdmin(), 403);

        $data = $request->validate([
            'direction' => ['required', 'in:add,deduct'],
            'amount' => ['required', 'numeric', 'min:1', 'max:50000'],
            'reason' => ['required', 'string', 'max:120'],
        ]);

        try {
            $data['direction'] === 'add'
                ? Wallet::credit($user, $data['amount'], 'adjustment', 'Admin: ' . $data['reason'])
                : Wallet::debit($user, $data['amount'], 'adjustment', 'Admin: ' . $data['reason'], null, true);
        } catch (RuntimeException $e) {
            return back()->with('error', $e->getMessage());
        }

        return back()->with('success', 'Wallet updated.');
    }

    public function destroy(Request $request, User $user)
    {
        if ($user->isAdmin() || $user->id === $request->user()->id) {
            return back()->with('error', 'Admin accounts can’t be deleted here.');
        }

        if ($user->runnerOrders()->whereIn('status', ['preparing', 'on_the_way'])->exists()) {
            return back()->with('error', 'This runner still has active jobs. Reassign them first.');
        }

        $name = $user->name;
        $user->delete();

        return redirect()->route('admin.users')->with('success', "{$name} was deleted.");
    }
}
