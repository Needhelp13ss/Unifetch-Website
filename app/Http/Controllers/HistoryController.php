<?php

namespace App\Http\Controllers;

use App\Models\Order;
use Illuminate\Http\Request;

/** Transaction history: old and new orders of the person (customer, runner or shop). */
class HistoryController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();

        if ($user->isAdmin()) {
            return redirect()->route('admin.orders');
        }

        $status = $request->query('status');
        $type = $request->query('type');
        $q = trim((string) $request->query('q', ''));

        $query = Order::with('user', 'runner', 'items');

        if ($user->isShop()) {
            $name = $user->vendor?->name;
            $query->whereHas('items', fn ($i) => $i->where('vendor_name', $name ?? '-'));
        } else {
            $query->where(fn ($w) => $w->where('user_id', $user->id)->orWhere('runner_id', $user->id));
        }

        $orders = $query
            ->when(array_key_exists((string) $status, Order::STATUSES), fn ($w) => $w->where('status', $status))
            ->when(in_array($type, ['food', 'custom', 'errand'], true), fn ($w) => $w->where('type', $type))
            ->when($q !== '', fn ($w) => $w->where('order_number', 'like', "%{$q}%"))
            ->latest()->paginate(15)->withQueryString();

        return view('history', compact('orders', 'status', 'type', 'q', 'user'));
    }
}
