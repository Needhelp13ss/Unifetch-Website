<?php

namespace App\Http\Controllers;

use App\Models\Order;
use Illuminate\Http\Request;
use RuntimeException;

class RunnerController extends Controller
{
    /** A customer asks to become a runner. The admin approves it. */
    public function apply(Request $request)
    {
        $user = $request->user();

        if ($user->isRunner() || $user->hasPendingRunnerApplication()) {
            return back()->with('error', 'You already applied or you are already a runner.');
        }

        $user->forceFill(['runner_status' => 'pending', 'runner_applied_at' => now()])->save();

        return back()->with('success', 'Application sent! The admin will review it, and you will see Errand Services once you are approved.');
    }

    /** Runner Jobs: the job board (runners only). */
    public function board(Request $request)
    {
        $runner = $request->user();

        $available = Order::with('items', 'user')
            ->where('status', 'pending')
            ->whereNull('runner_id')
            ->where('user_id', '!=', $runner->id)
            ->oldest()
            ->get();

        $mine = $runner->runnerOrders()->with('items', 'user')
            ->whereIn('status', ['preparing', 'on_the_way'])->oldest('accepted_at')->get();

        $done = $runner->runnerOrders()->with('items')
            ->where('status', 'delivered')->latest('delivered_at')->limit(10)->get();

        $earned = (float) $runner->runnerOrders()->where('status', 'delivered')->sum('delivery_fee');

        return view('jobs.board', [
            'available' => $available,
            'mine' => $mine,
            'done' => $done,
            'earned' => $earned,
            'deliveredCount' => $runner->runnerOrders()->where('status', 'delivered')->count(),
            'balance' => (float) $runner->wallet_balance,
            'committed' => $runner->walletCommitted(),
        ]);
    }

    public function accept(Request $request, Order $order)
    {
        return $this->act($request, $order, fn () => $order->acceptBy($request->user()),
            "You took {$order->order_number}. The customer can now see you're on it.");
    }

    public function release(Request $request, Order $order)
    {
        $this->mine($request, $order);

        return $this->act($request, $order, fn () => $order->releaseRunner(),
            "You gave back {$order->order_number}. It's open for other runners again.");
    }

    public function start(Request $request, Order $order)
    {
        $this->mine($request, $order);

        return $this->act($request, $order, fn () => $order->startDelivery(), 'Nice! The customer sees that you are on the way.');
    }

    public function complete(Request $request, Order $order)
    {
        $this->mine($request, $order);

        $note = $order->payment_method === 'wallet'
            ? 'Delivered! Your delivery fee was added to your wallet.'
            : 'Delivered! The cash you owe UniFetch was taken from your wallet.';

        return $this->act($request, $order, fn () => $order->complete(), $note);
    }

    private function mine(Request $request, Order $order): void
    {
        abort_unless($order->runner_id === $request->user()->id, 403);
    }

    private function act(Request $request, Order $order, \Closure $do, string $success)
    {
        try {
            $do();
        } catch (RuntimeException $e) {
            return back()->with('error', $e->getMessage());
        }

        return back()->with('success', $success);
    }
}
