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

/**
 * Errand Services: a customer asks a runner to do something for them
 * (get a parcel, wait in a line, submit documents, ...). A runner picks it up
 * from the Runner Jobs board.
 */
class ErrandServiceController extends Controller
{
    use PricesDeliveries;

    public function create(Request $request)
    {
        $recent = $request->user()->orders()->where('type', 'errand')->latest()->limit(3)->get();

        return view('errand-service', [
            'categories' => Order::ERRAND_CATEGORIES,
            'places' => $this->places(),
            'paymentMethods' => Order::PAYMENT_METHODS,
            'walletBalance' => (float) $request->user()->wallet_balance,
            'defaultPlace' => $this->places()->firstWhere('name', $request->user()->campus_location)?->id,
            'minFee' => Pricing::BASE_FEE,
            'maxFee' => Order::ERRAND_MAX_FEE,
            'recent' => $recent,
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'errand_category' => ['required', Rule::in(array_keys(Order::ERRAND_CATEGORIES))],
            'errand_title' => ['required', 'string', 'max:120'],
            'errand_details' => ['required', 'string', 'max:1000'],
            'pickup_location' => ['required', 'string', 'max:120'],
            'location_id' => ['required', 'integer', 'exists:campus_locations,id'],
            'delivery_details' => ['nullable', 'string', 'max:80'],
            'pickup_id' => ['nullable', 'integer', 'exists:campus_locations,id'],
            'budget' => ['nullable', 'numeric', 'min:0', 'max:5000'],
            'fee' => ['required', 'numeric', 'min:' . Pricing::BASE_FEE, 'max:' . Order::ERRAND_MAX_FEE],
            'payment_method' => ['required', Rule::in(array_keys(Order::PAYMENT_METHODS))],
        ], [
            'errand_category.required' => 'Pick the kind of errand first.',
            'errand_title.required' => 'Give your errand a short title.',
            'errand_details.required' => 'Add the details so the runner knows exactly what to do.',
            'pickup_location.required' => 'Tell the runner where to go.',
            'location_id.required' => 'Pick where the runner should meet you.',
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
                    'type' => 'errand',
                    'status' => 'pending',
                    'subtotal' => 0,
                    'delivery_fee' => $fee,
                    'service_fee' => $quote['service_fee'],
                    'distance_m' => $quote['meters'],
                    'total' => $total,
                    'payment_method' => $data['payment_method'],
                    'errand_category' => $data['errand_category'],
                    'errand_title' => $data['errand_title'],
                    'errand_details' => $data['errand_details'],
                    'pickup_location' => $data['pickup_location'],
                    'delivery_location' => $this->deliveryLabel($to, $data['delivery_details'] ?? null),
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
            ->with('success', "Errand {$order->order_number} sent! We're looking for a runner now.");
    }
}
