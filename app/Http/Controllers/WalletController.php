<?php

namespace App\Http\Controllers;

use App\Models\WalletTransaction;
use App\Support\Wallet;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class WalletController extends Controller
{
    public const TOPUP_PRESETS = [50, 100, 200, 500, 1000];
    public const TOPUP_MIN = 20;
    public const TOPUP_MAX = 5000;
    public const CASHOUT_MIN = 50;

    public function index(Request $request)
    {
        $user = $request->user();
        $balance = (float) $user->wallet_balance;
        $committed = $user->isRunner() ? (float) $user->walletCommitted() : 0;

        return view('wallet', [
            'balance' => $balance,
            'transactions' => $user->walletTransactions()->with('order')->limit(50)->get(),
            'presets' => self::TOPUP_PRESETS,
            'committed' => $committed,
            'withdrawable' => max(0, round($balance - $committed, 2)),
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
            'payment_method' => ['required', 'in:qr_ph,gcash'],
        ], [
            'amount.required' => 'Enter how much you want to add.',
            'amount.min' => 'The minimum top-up is ₱' . self::TOPUP_MIN . '.',
            'amount.max' => 'The maximum top-up at once is ₱' . number_format(self::TOPUP_MAX) . '.',
            'payment_method.required' => 'Pick what kind of payment method.',
            'payment_method.in' => 'Pick what kind of payment method.',
        ]);

        $amount = round((float) $data['amount'], 2);
        $methodLabel = $data['payment_method'] === 'gcash' ? 'GCash' : 'QR PH';

        Wallet::credit($request->user(), $amount, 'topup', "Wallet top-up ({$methodLabel})");

        return back()->with('success', 'Added ' . \App\Support\Money::format($amount) . " to your wallet via {$methodLabel}.");
    }

    /**
     * Demo cash out: deducts from the user's available withdrawable balance
     * and logs a transaction in Recent activity.
     */
    public function cashout(Request $request)
    {
        $user = $request->user();
        $balance = (float) $user->wallet_balance;
        $committed = $user->isRunner() ? (float) $user->walletCommitted() : 0;
        $maxCashout = max(0, round($balance - $committed, 2));

        $data = $request->validate([
            'cashout_amount' => ['required', 'numeric', 'min:' . self::CASHOUT_MIN, 'max:' . $maxCashout],
            'cashout_method' => ['required', 'in:qr_ph,gcash'],
            'account_details' => ['required', 'string', 'max:100'],
        ], [
            'cashout_amount.required' => 'Enter how much you want to cash out.',
            'cashout_amount.min' => 'The minimum cash out is ₱' . self::CASHOUT_MIN . '.',
            'cashout_amount.max' => 'You can only cash out up to ' . \App\Support\Money::format($maxCashout) . '.',
            'cashout_method.required' => 'Pick what kind of payment method.',
            'cashout_method.in' => 'Pick what kind of payment method.',
            'account_details.required' => 'Enter your GCash number or account name.',
        ]);

        $amount = round((float) $data['cashout_amount'], 2);
        $methodLabel = $data['cashout_method'] === 'gcash' ? 'GCash' : 'QR PH';
        $account = trim($data['account_details']);

        DB::transaction(function () use ($user, $amount, $methodLabel, $account) {
            $user->decrement('wallet_balance', $amount);
            $user->refresh();

            WalletTransaction::create([
                'user_id' => $user->id,
                'type' => 'remittance',
                'amount' => -$amount,
                'balance_after' => $user->wallet_balance,
                'description' => "Cash out via {$methodLabel} ({$account})",
            ]);
        });

        return back()->with('success', 'Cashed out ' . \App\Support\Money::format($amount) . " to your {$methodLabel} ({$account}).");
    }
}