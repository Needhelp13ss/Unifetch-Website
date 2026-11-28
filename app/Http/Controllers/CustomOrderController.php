<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\PricesDeliveries;
use App\Models\CampusLocation;
use App\Models\Order;
use App\Support\Pricing;
use App\Support\Wallet;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use RuntimeException;

/** Custom order: the customer wants an item that is not on the menu. */
class CustomOrderController extends Controller
{
    use PricesDeliveries;

    public function create(Request $request)
    {
        return view('custom-order', [
            'places' => $this->places(),
            'ideas' => Order::CUSTOM_ORDER_IDEAS,
            'paymentMethods' => Order::PAYMENT_METHODS,
            'walletBalance' => (float) $request->user()->wallet_balance,
            'defaultPlace' => $this->places()->firstWhere('name', $request->user()->campus_location)?->id,
            'minFee' => Pricing::BASE_FEE,
            'maxFee' => Order::ERRAND_MAX_FEE,
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'errand_title' => ['required', 'string', 'max:120'],
            'errand_details' => ['required', 'string', 'max:1000'],
            'pickup_location' => ['nullable', 'string', 'max:120'],
            'location_id' => ['required', 'integer', 'exists:campus_locations,id'],
            'delivery_details' => ['nullable', 'string', 'max:80'],
            'pickup_id' => ['nullable', 'integer', 'exists:campus_locations,id'],
            'budget' => ['nullable', 'numeric', 'min:0', 'max:5000'],
            'fee' => ['required', 'numeric', 'min:' . Pricing::BASE_FEE, 'max:' . Order::ERRAND_MAX_FEE],
            'payment_method' => ['required', Rule::in(array_keys(Order::PAYMENT_METHODS))],
        ], [
            'errand_title.required' => 'Tell us what you want in a few words.',
            'errand_details.required' => 'Add the details so the runner knows exactly what to buy.',
            'location_id.required' => 'Pick where the runner should bring it.',
            'fee.min' => 'The runner fee must be at least ₱' . Pricing::BASE_FEE . '.',
            'fee.max' => 'The runner fee can be at most ₱' . Order::ERRAND_MAX_FEE . '.',
        ]);

        $fee = round((float) $data['fee'], 2);
        $to = CampusLocation::findOrFail($data['location_id']);
        $from = isset($data['pickup_id']) ? CampusLocation::find($data['pickup_id']) : null;
        $quote = Pricing::quote([$from], $to);

        if ($fee < $quote['runner_fee']) {
            return back()->withInput()->withErrors(['fee' => 'For this distance (' . $quote['meters'] . ' m) the runner fee must be at least ₱' . $quote['runner_fee'] . '.']);
        }

        $total = $fee + $quote['service_fee'];

        if ($data['payment_method'] === 'wallet' && Wallet::cents($request->user()->wallet_balance) < Wallet::cents($total)) {
            return back()->withInput()->with('error', 'Your wallet balance is too low for this fee. Top up your wallet or pay with cash.');
        }

        try {
            $order = DB::transaction(function () use ($request, $data, $fee, $total, $quote, $to, $from) {
                $order = Order::create([
                    'user_id' => $request->user()->id,
                    'order_number' => Order::newNumber(),
                    'type' => 'custom',
                    'status' => 'pending',
                    'subtotal' => 0,
                    'delivery_fee' => $fee,
                    'service_fee' => $quote['service_fee'],
                    'distance_m' => $quote['meters'],
                    'total' => $total,
                    'payment_method' => $data['payment_method'],
                    'delivery_location' => $this->deliveryLabel($to, $data['delivery_details'] ?? null),
                    'pickup_location' => ($data['pickup_location'] ?? null) ?: $from?->name,
                    'errand_title' => $data['errand_title'],
                    'errand_details' => $data['errand_details'],
                    'budget' => $data['budget'] ?? null,
                ]);

                if ($data['payment_method'] === 'wallet') {
                    Wallet::debit($request->user(), $total, 'payment', "Payment for {$order->order_number}", $order);
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
