<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\User;
use App\Models\WalletTransaction;

class DashboardController extends Controller
{
    public function index()
    {
        return view('admin.dashboard', [
            'stats' => [
                'customers' => User::where('role', 'customer')->count(),
                'runners' => User::where('role', 'runner')->count(),
                'pending' => User::where('role', 'customer')->where('runner_status', 'pending')->count(),
                'orders_today' => Order::whereDate('created_at', today())->count(),
                'active' => Order::whereIn('status', ['pending', 'preparing', 'on_the_way'])->count(),
                'waiting' => Order::where('status', 'pending')->count(),
                'sales' => (float) Order::where('status', 'delivered')->sum('total'),
                'wallets' => (float) User::sum('wallet_balance'),
            ],
            'applications' => User::where('role', 'customer')->where('runner_status', 'pending')->oldest('runner_applied_at')->limit(5)->get(),
            'recent' => Order::with('user', 'runner')->latest()->limit(8)->get(),
            'transactions' => WalletTransaction::with('user')->latest('id')->limit(6)->get(),
        ]);
    }
}
