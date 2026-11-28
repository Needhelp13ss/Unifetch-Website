<?php

namespace App\Http\Controllers\Shop;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\Request;

/** The seller's home: orders for the shop's products. */
class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $vendor = $request->user()->vendor;

        $orders = Order::with('user', 'runner', 'items', 'photos')
            ->whereHas('items', fn ($i) => $i->where('vendor_name', $vendor->name));

        $active = (clone $orders)->whereIn('status', ['pending', 'preparing', 'on_the_way'])->latest()->get();
        $delivered = (clone $orders)->where('status', 'delivered');

        $sales = (float) $delivered->get()->sum(
            fn ($o) => $o->items->where('vendor_name', $vendor->name)->sum(fn ($i) => $i->line_total)
        );

        return view('shop.dashboard', [
            'vendor' => $vendor,
            'active' => $active,
            'stats' => [
                'active' => $active->count(),
                'delivered' => (clone $orders)->where('status', 'delivered')->count(),
                'sales' => $sales,
                'products' => $vendor->products()->count(),
            ],
        ]);
    }
}
