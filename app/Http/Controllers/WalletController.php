<?php

namespace App\Http\Controllers;

use App\Support\Wallet;
use Illuminate\Http\Request;

class WalletController extends Controller
{
    public const TOPUP_PRESETS = [50, 100, 200, 500, 1000];
    public const TOPUP_MIN = 20;
    public const TOPUP_MAX = 5000;

    public function index(Request $request)
    {
        $user = $request->user();

        return view('wallet', [
            'balance' => (float) $user->wallet_balance,
            'transactions' => $user->walletTransactions()->with('order')->limit(50)->get(),
            'presets' => self::TOPUP_PRESETS,
            'committed' => $user->isRunner() ? $user->walletCommitted() : 0,
        ]);
    }

    /**
     * Demo top-up: the money is added straight away. In a real deployment
     * this is the one place to connect GCash / Maya / a payment gateway.
     */
    public function topup(Request $request)
    {
        $data = $request->validate([
            'amount' => ['required', 'numeric', 'min:' . self::TOPUP_MIN, 'max:' . self::TOPUP_MAX],
        ], [
            'amount.required' => 'Enter how much you want to add.',
            'amount.min' => 'The minimum top-up is ₱' . self::TOPUP_MIN . '.',
            'amount.max' => 'The maximum top-up at once is ₱' . number_format(self::TOPUP_MAX) . '.',
        ]);

        $amount = round((float) $data['amount'], 2);
        Wallet::credit($request->user(), $amount, 'topup', 'Wallet top-up');

        return back()->with('success', 'Added ' . \App\Support\Money::format($amount) . ' to your wallet.');
    }
}
