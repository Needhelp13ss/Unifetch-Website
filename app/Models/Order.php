<?php

namespace App\Models;

use App\Support\Wallet;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class Order extends Model
{
    /** Flat delivery fee (pesos) charged once per food order. Change it here. */
    public const DELIVERY_FEE = 20;

    /** Custom orders and errands: the fee the customer offers must be inside this range. */
    public const ERRAND_MIN_FEE = 20;
    public const ERRAND_MAX_FEE = 500;

    /** A runner can carry this many unfinished jobs at the same time. */
    public const MAX_ACTIVE_JOBS = 3;

    public const STATUSES = [
        'pending' => 'Looking for runners',
        'preparing' => 'Runner is on it',
        'on_the_way' => 'On the way',
        'delivered' => 'Delivered',
        'cancelled' => 'Cancelled',
    ];

    /** Methods a customer can choose today. */
    public const PAYMENT_METHODS = [
        'cod' => 'Cash on Delivery',
        'wallet' => 'UniFetch Wallet',
    ];

    /** Older orders may still carry the previous GCash/Maya label. */
    private const PAYMENT_LABELS = [
        'cod' => 'Cash on Delivery',
        'wallet' => 'UniFetch Wallet',
        'ewallet' => 'E-wallet (GCash / Maya)',
    ];

    /** Ideas shown while typing a custom order (items that are not on the menu). */
    public const CUSTOM_ORDER_IDEAS = ['Snacks from a store outside', 'School supplies', 'Print documents', 'Medicine from a drugstore', 'Other'];

    /** Errand Services: things a runner can do for you that are not about buying food. */
    public const ERRAND_CATEGORIES = [
        'Pick up a parcel' => ['📦', 'A courier or a package is waiting for you.'],
        'Wait in line' => ['🧍', 'Queue at the cashier, registrar or any long line for you.'],
        'Submit or claim documents' => ['📄', 'Drop off or collect forms, clearances, IDs and the like.'],
        'Get something for me' => ['🎒', 'Pick up an item from a person or place and bring it to you.'],
        'Other' => ['✨', 'Anything else a runner can do on campus.'],
    ];

    protected $fillable = [
        'user_id', 'runner_id', 'order_number', 'type', 'status', 'errand_category', 'subtotal', 'delivery_fee',
        'total', 'payment_method', 'delivery_location', 'notes',
        'errand_title', 'errand_details', 'pickup_location', 'budget',
        'accepted_at', 'delivered_at', 'remitted',
    ];

    protected function casts(): array
    {
        return [
            'subtotal' => 'decimal:2',
            'delivery_fee' => 'decimal:2',
            'total' => 'decimal:2',
            'budget' => 'decimal:2',
            'remitted' => 'decimal:2',
            'accepted_at' => 'datetime',
            'delivered_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function runner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'runner_id');
    }

    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public static function newNumber(): string
    {
        do {
            $number = 'UF-' . now()->format('ymd') . '-' . strtoupper(\Illuminate\Support\Str::random(4));
        } while (self::where('order_number', $number)->exists());

        return $number;
    }

    /** A custom order: an item that is not on the menu. */
    public function isCustom(): bool
    {
        return $this->type === 'custom';
    }

    /** An errand service: parcel, waiting in line, documents... */
    public function isErrand(): bool
    {
        return $this->type === 'errand';
    }

    /** Custom orders and errands have no menu items, only a written request. */
    public function isRequest(): bool
    {
        return $this->type !== 'food';
    }

    public function getTypeLabelAttribute(): string
    {
        return match ($this->type) {
            'custom' => 'Custom order',
            'errand' => 'Errand',
            default => 'Food order',
        };
    }

    public function getStatusLabelAttribute(): string
    {
        return self::STATUSES[$this->status] ?? ucfirst($this->status);
    }

    public function getPaymentLabelAttribute(): string
    {
        return self::PAYMENT_LABELS[$this->payment_method] ?? $this->payment_method;
    }

    /** Short line used in lists: the errand title or the first items. */
    public function getSummaryAttribute(): string
    {
        if ($this->isRequest()) {
            return ($this->isErrand() ? '🏃 ' : '📝 ') . $this->errand_title;
        }

        $items = $this->relationLoaded('items') ? $this->items : $this->items()->get();
        $text = $items->take(3)->map(fn ($i) => $i->quantity . '× ' . $i->name)->implode(', ');

        return $items->count() > 3 ? $text . ' and ' . ($items->count() - 3) . ' more' : $text;
    }

    public function canBeCancelled(): bool
    {
        return $this->status === 'pending';
    }

    public function isActive(): bool
    {
        return in_array($this->status, ['pending', 'preparing', 'on_the_way'], true);
    }

    /**
     * What the runner earns for this job.
     * UniFetch keeps the rest (the item cost) of a wallet-paid order.
     */
    public function runnerEarning(): float
    {
        return (float) $this->delivery_fee;
    }

    /**
     * Cash-on-delivery: the runner collects the full total from the customer,
     * keeps the delivery fee, and owes UniFetch the rest. It is taken from the
     * runner's wallet when the order is marked delivered.
     * Wallet-paid orders: the customer already paid UniFetch, so nothing is owed.
     */
    public function cashToRemit(): float
    {
        if ($this->payment_method !== 'cod') {
            return 0.0;
        }

        return max(0.0, (float) $this->total - $this->runnerEarning());
    }

    // ------------------------------------------------------------------
    // Workflow: every status change goes through these methods so that
    // wallet money always follows the order.
    // ------------------------------------------------------------------

    /** A runner takes the job. */
    public function acceptBy(User $runner): void
    {
        DB::transaction(function () use ($runner) {
            $order = self::whereKey($this->id)->lockForUpdate()->firstOrFail();

            if ($order->status !== 'pending' || $order->runner_id !== null) {
                throw new RuntimeException('Someone else already took this job.');
            }
            if ($order->user_id === $runner->id) {
                throw new RuntimeException("You can't run your own order.");
            }
            if ($runner->runnerOrders()->whereIn('status', ['preparing', 'on_the_way'])->count() >= self::MAX_ACTIVE_JOBS) {
                throw new RuntimeException('You already have ' . self::MAX_ACTIVE_JOBS . ' active jobs. Finish one first.');
            }

            $needed = $runner->walletCommitted() + $order->cashToRemit();
            if ($order->cashToRemit() > 0 && Wallet::cents($runner->wallet_balance) < Wallet::cents($needed)) {
                throw new RuntimeException(
                    'This is a cash job: your wallet must cover the ₱' . number_format($needed, 2)
                    . ' you will hand over to UniFetch. Top up your wallet first.'
                );
            }

            $order->update(['runner_id' => $runner->id, 'status' => 'preparing', 'accepted_at' => now()]);
            $this->refresh();
        });
    }

    /** Admin picks a runner directly (no wallet/limit checks, the admin decides). */
    public function assignTo(User $runner): void
    {
        if ($this->status !== 'pending' && $this->status !== 'preparing') {
            throw new RuntimeException('Only orders that are waiting or being prepared can be reassigned.');
        }
        if ($this->user_id === $runner->id) {
            throw new RuntimeException("A runner can't run their own order.");
        }

        $this->update(['runner_id' => $runner->id, 'status' => 'preparing', 'accepted_at' => now()]);
    }

    /** Runner gives the job back; it goes back to "Looking for runners". */
    public function releaseRunner(): void
    {
        if ($this->status !== 'preparing') {
            throw new RuntimeException('You can only give a job back before you set off.');
        }

        $this->update(['runner_id' => null, 'status' => 'pending', 'accepted_at' => null]);
    }

    public function startDelivery(): void
    {
        if ($this->status !== 'preparing') {
            throw new RuntimeException('This job is not ready to set off.');
        }

        $this->update(['status' => 'on_the_way']);
    }

    /** Delivered: pay the runner (wallet orders) or collect the cash share (cod orders). */
    public function complete(): void
    {
        DB::transaction(function () {
            $order = self::whereKey($this->id)->lockForUpdate()->firstOrFail();

            if ($order->status !== 'on_the_way' || !$order->runner) {
                throw new RuntimeException('Only an order that is on the way can be marked delivered.');
            }

            $runner = $order->runner;

            if ($order->payment_method === 'wallet') {
                Wallet::credit($runner, $order->runnerEarning(), 'earning',
                    "Delivery fee for {$order->order_number}", $order);
            } elseif ($order->cashToRemit() > 0) {
                // may leave a small negative balance if the runner spent the wallet meanwhile
                Wallet::debit($runner, $order->cashToRemit(), 'remittance',
                    "Cash collected for {$order->order_number} handed to UniFetch", $order, true);
                $order->remitted = $order->cashToRemit();
            }

            $order->status = 'delivered';
            $order->delivered_at = now();
            $order->save();
            $this->refresh();
        });
    }

    /** Cancel and give the customer's wallet payment back. */
    public function cancelAndRefund(bool $onlyIfPending = false): void
    {
        DB::transaction(function () use ($onlyIfPending) {
            $order = self::whereKey($this->id)->lockForUpdate()->firstOrFail();

            if (in_array($order->status, ['delivered', 'cancelled'], true)) {
                throw new RuntimeException("This order is already {$order->status}.");
            }
            if ($onlyIfPending && $order->status !== 'pending') {
                throw new RuntimeException('A runner already took this order, so it can no longer be cancelled.');
            }

            if ($order->payment_method === 'wallet') {
                Wallet::credit($order->user, $order->total, 'refund', "Refund for {$order->order_number}", $order);
            }

            $order->update(['status' => 'cancelled']);
            $this->refresh();
        });
    }
}
