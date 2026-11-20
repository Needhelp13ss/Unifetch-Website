<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\User;
use Illuminate\Http\Request;
use RuntimeException;

class OrderController extends Controller
{
    public function index(Request $request)
    {
        $status = $request->query('status');
        $type = $request->query('type');
        $q = trim((string) $request->query('q', ''));

        $orders = Order::with('user', 'runner')
            ->when(array_key_exists((string) $status, Order::STATUSES), fn ($w) => $w->where('status', $status))
            ->when(in_array($type, ['food', 'custom', 'errand'], true), fn ($w) => $w->where('type', $type))
            ->when($q !== '', fn ($w) => $w->where(fn ($x) => $x
                ->where('order_number', 'like', "%{$q}%")
                ->orWhereHas('user', fn ($u) => $u->where('name', 'like', "%{$q}%"))))
            ->latest()->paginate(20)->withQueryString();

        return view('admin.orders', ['orders' => $orders, 'status' => $status, 'type' => $type, 'q' => $q]);
    }

    public function show(Order $order)
    {
        return view('admin.order', [
            'order' => $order->load('items', 'user', 'runner'),
            'runners' => User::where('role', 'runner')->orderBy('name')->get(),
        ]);
    }

    public function assign(Request $request, Order $order)
    {
        $data = $request->validate(['runner_id' => ['required', 'integer']]);
        $runner = User::where('role', 'runner')->find($data['runner_id']);

        if (!$runner) {
            return back()->with('error', 'Pick an approved runner.');
        }

        return $this->act(fn () => $order->assignTo($runner), "{$order->order_number} now belongs to {$runner->name}.");
    }

    public function release(Order $order)
    {
        return $this->act(fn () => $order->releaseRunner(), 'The order is back to "Looking for runners".');
    }

    public function start(Order $order)
    {
        return $this->act(fn () => $order->startDelivery(), 'Marked as on the way.');
    }

    public function complete(Order $order)
    {
        return $this->act(fn () => $order->complete(), 'Marked as delivered. Wallet money was settled.');
    }

    public function cancel(Order $order)
    {
        $note = $order->payment_method === 'wallet' ? ' The customer was refunded to their wallet.' : '';

        return $this->act(fn () => $order->cancelAndRefund(), "{$order->order_number} was cancelled.{$note}");
    }

    public function destroy(Order $order)
    {
        if ($order->isActive()) {
            return back()->with('error', 'Cancel the order first, then you can delete it.');
        }

        $number = $order->order_number;
        $order->delete();

        return redirect()->route('admin.orders')->with('success', "{$number} was deleted.");
    }

    private function act(\Closure $do, string $success)
    {
        try {
            $do();
        } catch (RuntimeException $e) {
            return back()->with('error', $e->getMessage());
        }

        return back()->with('success', $success);
    }
}
