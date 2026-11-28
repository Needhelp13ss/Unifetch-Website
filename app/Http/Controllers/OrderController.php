<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\PricesDeliveries;
use App\Models\CampusLocation;
use App\Models\Order;
use App\Support\Pricing;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Support\Wallet;
use RuntimeException;
use Illuminate\Validation\Rule;

class OrderController extends Controller
{
    use PricesDeliveries;

    /** Step 2: review the ticked items, then choose delivery spot and payment. */
    public function checkout(Request $request)
    {
        $items = $this->selectedItems($request, $request->session()->get('checkout_items', []));

        if ($items->isEmpty()) {
            return redirect()->route('cart')->with('error', 'Tick the items you want to check out first.');
        }

        $subtotal = $items->sum(fn ($i) => $i->line_total);
        $places = $this->places();
        $default = $places->firstWhere('name', $request->user()->campus_location);

        return view('checkout', [
            'items' => $items,
            'subtotal' => $subtotal,
            'quote' => Pricing::quote($this->shopPlaces($items), $default),
            'fromIds' => $this->shopPlaces($items)->filter()->pluck('id')->unique()->implode(','),
            'paymentMethods' => Order::PAYMENT_METHODS,
            'walletBalance' => (float) $request->user()->wallet_balance,
            'places' => $places,
            'defaultPlace' => $default?->id,
        ]);
    }

    /** Step 3: save the order. Only the ticked items leave the cart. */
    public function store(Request $request)
    {
        $data = $request->validate([
            'items' => ['required', 'array', 'min:1'],
            'items.*' => ['integer'],
            'location_id' => ['required', 'integer', 'exists:campus_locations,id'],
            'delivery_details' => ['nullable', 'string', 'max:80'],
            'notes' => ['nullable', 'string', 'max:255'],
            'payment_method' => ['required', Rule::in(array_keys(Order::PAYMENT_METHODS))],
        ], [
            'location_id.required' => 'Please pick where to deliver your order.',
            'location_id.exists' => 'Please pick one of the campus places.',
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

        $subtotal = $items->sum(fn ($i) => $i->line_total);
        $to = CampusLocation::findOrFail($data['location_id']);
        $quote = Pricing::quote($this->shopPlaces($items), $to);
        $total = $subtotal + $quote['total_fees'];

        if ($data['payment_method'] === 'wallet'
            && Wallet::cents($request->user()->wallet_balance) < Wallet::cents($total)) {
            return back()->withInput()->with('error', 'Your wallet balance is too low for this order. Top up your wallet or pay with cash.');
        }

        try {
            $order = DB::transaction(function () use ($request, $data, $items, $subtotal, $quote, $total, $to) {
                $order = Order::create([
                    'user_id' => $request->user()->id,
                    'order_number' => Order::newNumber(),
                    'status' => 'pending',
                    'subtotal' => $subtotal,
                    'delivery_fee' => $quote['runner_fee'],
                    'service_fee' => $quote['service_fee'],
                    'distance_m' => $quote['meters'],
                    'total' => $total,
                    'payment_method' => $data['payment_method'],
                    'delivery_location' => $this->deliveryLabel($to, $data['delivery_details'] ?? null),
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

                if ($data['payment_method'] === 'wallet') {
                    Wallet::debit($request->user(), $order->total, 'payment',
                        "Payment for {$order->order_number}", $order);
                }

                $request->user()->cartItems()->whereIn('id', $items->pluck('id'))->delete();

                return $order;
            });
        } catch (RuntimeException $e) {
            return back()->withInput()->with('error', $e->getMessage());
        }

        $request->session()->forget('checkout_items');

        return redirect()->route('orders.show', $order)
            ->with('success', "Order {$order->order_number} placed! We're looking for a runner now.");
    }

    public function index(Request $request)
    {
        $orders = $request->user()->orders()->with('items')->latest()->get();

        return view('orders.index', ['orders' => $orders]);
    }

    public function show(Request $request, Order $order)
    {
        $order->load('items', 'runner', 'user', 'photos.user');
        abort_unless($order->canBeSeenBy($request->user()), 403);

        return view('orders.show', ['order' => $order, 'me' => $request->user()]);
    }

    public function cancel(Request $request, Order $order)
    {
        abort_unless($order->user_id === $request->user()->id, 403);

        if (!$order->canBeCancelled()) {
            return back()->with('error', 'You can only cancel while we are still looking for a runner.');
        }

        try {
            $order->cancelAndRefund(true);
        } catch (RuntimeException $e) {
            return back()->with('error', $e->getMessage());
        }

        $refund = $order->payment_method === 'wallet' ? ' Your payment was returned to your wallet.' : '';

        return back()->with('success', "Order {$order->order_number} was cancelled.{$refund}");
    }

    /** The campus place of every shop in the selected items. */
    private function shopPlaces($items)
    {
        return $items->map(fn ($i) => $i->product->vendor->place)->unique('id')->values();
    }

    /** Cart items of the current user, limited to the given ids. */
    private function selectedItems(Request $request, array $ids)
    {
        return $request->user()->cartItems()
            ->whereIn('id', $ids)
            ->with('product.vendor')
            ->get();
    }
}
