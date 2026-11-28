<?php

namespace App\Http\Controllers;

use App\Models\CampusLocation;
use App\Support\Money;
use App\Support\Pricing;
use Illuminate\Http\Request;

/** Live price quote: distance between places -> runner fee + service fee. */
class QuoteController extends Controller
{
    public function __invoke(Request $request)
    {
        $to = CampusLocation::find((int) $request->query('to'));
        $from = CampusLocation::whereIn('id', array_filter((array) $request->query('from', [])))->get();

        $q = Pricing::quote($from, $to);

        return response()->json($q + [
            'ready' => $to !== null,
            'runner_fee_text' => Money::format($q['runner_fee']),
            'service_fee_text' => Money::format($q['service_fee']),
        ]);
    }
}
