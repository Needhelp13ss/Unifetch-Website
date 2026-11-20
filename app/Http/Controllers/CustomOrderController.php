<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Support\Wallet;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use RuntimeException;

/** Custom order: the customer wants an item that is not on the menu. */
class CustomOrderController extends Controller
{
    public function create(Request $request)
    {
        return view('custom-order', [
            'locations' => OrderController::LOCATION_SUGGESTIONS,
            'ideas' => Order::CUSTOM_ORDER_IDEAS,
            'paymentMethods' => Order::PAYMENT_METHODS,
            'walletBalance' => (float) $request->user()->wallet_balance,
            'defaultLocation' => $request->user()->campus_location,
            'minFee' => Order::ERRAND_MIN_FEE,
            'maxFee' => Order::ERRAND_MAX_FEE,
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'errand_title' => ['required', 'string', 'max:120'],
            'errand_details' => ['required', 'string', 'max:1000'],
            'pickup_location' => ['nullable', 'string', 'max:120'],
            'delivery_location' => ['required', 'string', 'max:120'],
            'budget' => ['nullable', 'numeric', 'min:0', 'max:5000'],
            'fee' => ['required', 'numeric', 'min:' . Order::ERRAND_MIN_FEE, 'max:' . Order::ERRAND_MAX_FEE],
            'payment_method' => ['required', Rule::in(array_keys(Order::PAYMENT_METHODS))],
        ], [
            'errand_title.required' => 'Tell us what you want in a few words.',
            'errand_details.required' => 'Add the details so the runner knows exactly what to buy.',
            'delivery_location.required' => 'Where should the runner bring it?',
            'fee.min' => 'The runner fee must be at least ₱' . Order::ERRAND_MIN_FEE . '.',
            'fee.max' => 'The runner fee can be at most ₱' . Order::ERRAND_MAX_FEE . '.',
        ]);

        $fee = round((float) $data['fee'], 2);

        if ($data['payment_method'] === 'wallet' && Wallet::cents($request->user()->wallet_balance) < Wallet::cents($fee)) {
            return back()->withInput()->with('error', 'Your wallet balance is too low for this fee. Top up your wallet or pay with cash.');
        }

        try {
            $order = DB::transaction(function () use ($request, $data, $fee) {
                $order = Order::create([
                    'user_id' => $request->user()->id,
                    'order_number' => Order::newNumber(),
                    'type' => 'custom',
                    'status' => 'pending',
                    'subtotal' => 0,
                    'delivery_fee' => $fee,
                    'total' => $fee,
                    'payment_method' => $data['payment_method'],
                    'delivery_location' => $data['delivery_location'],
                    'pickup_location' => $data['pickup_location'] ?? null,
                    'errand_title' => $data['errand_title'],
                    'errand_details' => $data['errand_details'],
                    'budget' => $data['budget'] ?? null,
                ]);

                if ($data['payment_method'] === 'wallet') {
                    Wallet::debit($request->user(), $fee, 'payment', "Payment for {$order->order_number}", $order);
                }

                return $order;
            });
        } catch (RuntimeException $e) {
            return back()->withInput()->with('error', $e->getMessage());
        }

        return redirect()->route('orders.show', $order)
            ->with('success', "Custom order {$order->order_number} sent! We're looking for a runner now.");
    }
}
