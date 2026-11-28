<?php

namespace App\Http\Controllers;

use App\Models\Order;
use Illuminate\Http\Request;

/** Chat between the customer and the runner who took the order. */
class OrderChatController extends Controller
{
    public function index(Request $request, Order $order)
    {
        $this->authorizeChat($request, $order);

        $after = (int) $request->query('after', 0);
        $me = $request->user()->id;

        $messages = $order->messages()->with('user:id,name')->where('id', '>', $after)->orderBy('id')->limit(100)->get()
            ->map(fn ($m) => [
                'id' => $m->id,
                'mine' => $m->user_id === $me,
                'name' => $m->user->name,
                'body' => $m->body,
                'time' => $m->created_at->format('g:i A'),
                'day' => $m->created_at->format('M j'),
            ]);

        return response()->json(['messages' => $messages, 'open' => $order->chatOpen()]);
    }

    public function store(Request $request, Order $order)
    {
        $this->authorizeChat($request, $order);

        if (!$order->chatOpen()) {
            return response()->json(['ok' => false, 'message' => 'This chat is closed because the order is finished.'], 422);
        }

        $data = $request->validate(['body' => ['required', 'string', 'max:500']]);
        $order->messages()->create(['user_id' => $request->user()->id, 'body' => trim($data['body'])]);

        return response()->json(['ok' => true]);
    }

    /** Only the customer and the runner of an order talk here. */
    private function authorizeChat(Request $request, Order $order): void
    {
        $id = $request->user()->id;
        abort_unless($order->hasChat() && ($id === $order->user_id || $id === $order->runner_id), 403);
    }
}
