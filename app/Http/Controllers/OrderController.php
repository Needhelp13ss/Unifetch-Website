<?php

namespace App\Http\Controllers;

use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class OrderController extends Controller
{
    /** Campus places suggested in the delivery location box (users can still type their own). */
    public const LOCATION_SUGGESTIONS = [
        'Main Building', 'Annex Building', 'Library', 'Canteen',
        'Gymnasium', 'Main Gate', 'Clinic', 'Parking Area',
    ];

    /** Step 2: review the ticked items, then choose delivery spot and payment. */
    public function checkout(Request $request)
    {
        $items = $this->selectedItems($request, $request->session()->get('checkout_items', []));

        if ($items->isEmpty()) {
            return redirect()->route('cart')->with('error', 'Tick the items you want to check out first.');
        }

        $subtotal = $items->sum(fn ($i) => $i->line_total);

        return view('checkout', [
            'items' => $items,
            'subtotal' => $subtotal,
            'deliveryFee' => Order::DELIVERY_FEE,
            'total' => $subtotal + Order::DELIVERY_FEE,
            'paymentMethods' => Order::PAYMENT_METHODS,
            'locations' => self::LOCATION_SUGGESTIONS,
            'defaultLocation' => $request->user()->campus_location,
        ]);
    }

    /** Step 3: save the order. Only the ticked items leave the cart. */
    public function store(Request $request)
    {
        $data = $request->validate([
            'items' => ['required', 'array', 'min:1'],
            'items.*' => ['integer'],
            'delivery_location' => ['required', 'string', 'max:120'],
            'notes' => ['nullable', 'string', 'max:255'],
            'payment_method' => ['required', Rule::in(array_keys(Order::PAYMENT_METHODS))],
        ], [
            'delivery_location.required' => 'Please tell us where to deliver your order.',
            'payment_method.required' => 'Please choose a payment method.',
        ]);

        $items = $this->selectedItems($request, $data['items']);

        if ($items->isEmpty()) {
            return redirect()->route('cart')->with('error', 'Those items are no longer in your cart.');
        }

        if ($items->contains(fn ($i) => !$i->product->is_available)) {
            $request->session()->put('checkout_items', $items->pluck('id')->all());

            return redirect()->route('cart')
                ->with('error', 'One of the selected items is no longer available. Please remove it and try again.');
        }

        $order = DB::transaction(function () use ($request, $data, $items) {
            $subtotal = $items->sum(fn ($i) => $i->line_total);

            $order = Order::create([
                'user_id' => $request->user()->id,
                'order_number' => $this->newOrderNumber(),
                'status' => 'pending',
                'subtotal' => $subtotal,
                'delivery_fee' => Order::DELIVERY_FEE,
                'total' => $subtotal + Order::DELIVERY_FEE,
                'payment_method' => $data['payment_method'],
                'delivery_location' => $data['delivery_location'],
                'notes' => $data['notes'] ?? null,
            ]);

            foreach ($items as $item) {
                $order->items()->create([
                    'product_id' => $item->product_id,
                    'vendor_name' => $item->product->vendor->name,
                    'name' => $item->product->name,
                    'emoji' => $item->product->emoji,
                    'price' => $item->product->price,
                    'quantity' => $item->quantity,
                ]);
            }

            $request->user()->cartItems()->whereIn('id', $items->pluck('id'))->delete();

            return $order;
        });

        $request->session()->forget('checkout_items');

        return redirect()->route('orders.show', $order)
            ->with('success', "Order {$order->order_number} placed! We'll notify you when a runner picks it up.");
    }

    public function index(Request $request)
    {
        $orders = $request->user()->orders()->with('items')->latest()->get();

        return view('orders.index', ['orders' => $orders]);
    }

    public function show(Request $request, Order $order)
    {
        abort_unless($order->user_id === $request->user()->id, 403);

        return view('orders.show', ['order' => $order->load('items')]);
    }

    public function cancel(Request $request, Order $order)
    {
        abort_unless($order->user_id === $request->user()->id, 403);

        if (!$order->canBeCancelled()) {
            return back()->with('error', 'Only pending orders can be cancelled.');
        }

        $order->update(['status' => 'cancelled']);

        return back()->with('success', "Order {$order->order_number} was cancelled.");
    }

    /** Cart items of the current user, limited to the given ids. */
    private function selectedItems(Request $request, array $ids)
    {
        return $request->user()->cartItems()
            ->whereIn('id', $ids)
            ->with('product.vendor')
            ->get();
    }

    private function newOrderNumber(): string
    {
        do {
            $number = 'UF-' . now()->format('ymd') . '-' . strtoupper(Str::random(4));
        } while (Order::where('order_number', $number)->exists());

        return $number;
    }
}
