<?php

namespace App\Support;

use App\Models\Order;
use App\Models\User;
use App\Models\WalletTransaction;
use Illuminate\Support\Facades\DB;
use RuntimeException;

/**
 * All wallet money moves through here, so the balance and the
 * transaction history can never disagree.
 */
class Wallet
{
    public static function credit(User $user, float|int|string $amount, string $type, string $description, ?Order $order = null): WalletTransaction
    {
        return self::move($user, self::cents($amount), $type, $description, $order, true);
    }

    /** @throws RuntimeException when the balance is too low (unless $allowNegative). */
    public static function debit(User $user, float|int|string $amount, string $type, string $description, ?Order $order = null, bool $allowNegative = false): WalletTransaction
    {
        return self::move($user, -self::cents($amount), $type, $description, $order, $allowNegative);
    }

    private static function move(User $user, int $cents, string $type, string $description, ?Order $order, bool $allowNegative): WalletTransaction
    {
        if ($cents === 0) {
            throw new RuntimeException('Amount must be more than zero.');
        }

        return DB::transaction(function () use ($user, $cents, $type, $description, $order, $allowNegative) {
            $fresh = User::whereKey($user->id)->lockForUpdate()->firstOrFail();
            $new = self::cents($fresh->wallet_balance) + $cents;

            if ($new < 0 && !$allowNegative) {
                throw new RuntimeException('Not enough wallet balance.');
            }

            $fresh->wallet_balance = $new / 100;
            $fresh->save();
            $user->wallet_balance = $fresh->wallet_balance;

            return WalletTransaction::create([
                'user_id' => $fresh->id,
                'order_id' => $order?->id,
                'type' => $type,
                'amount' => $cents / 100,
                'balance_after' => $new / 100,
                'description' => $description,
            ]);
        });
    }

    public static function cents(float|int|string|null $amount): int
    {
        return (int) round(((float) $amount) * 100);
    }
}
