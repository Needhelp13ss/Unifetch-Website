<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    /** Same defaults as the database, so a brand-new model behaves the same before it is reloaded. */
    protected $attributes = [
        'role' => 'customer',
        'wallet_balance' => 0,
    ];

    protected $fillable = [
        'name',
        'student_id',
        'email',
        'password',
        'profile_image',
        'bio',
        'gender',
        'show_gender',
        'birthday',
        'phone',
        'course',
        'year_level',
        'campus_location',
        'favorite_food',
        'interests',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    public const ROLES = ['customer' => 'Customer', 'runner' => 'Runner', 'admin' => 'Admin'];

    /** Role and wallet are never mass-assigned from forms; code sets them on purpose. */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'birthday' => 'date',
            'show_gender' => 'boolean',
            'runner_applied_at' => 'datetime',
            'wallet_balance' => 'decimal:2',
        ];
    }

    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    /** An approved runner (the only people who can see Errand Services). */
    public function isRunner(): bool
    {
        return $this->role === 'runner';
    }

    public function hasPendingRunnerApplication(): bool
    {
        return $this->role === 'customer' && $this->runner_status === 'pending';
    }

    public function runnerOrders(): HasMany
    {
        return $this->hasMany(Order::class, 'runner_id');
    }

    public function walletTransactions(): HasMany
    {
        return $this->hasMany(WalletTransaction::class)->latest('id');
    }

    /** Cash this runner still has to hand over for active cash-on-delivery jobs. */
    public function walletCommitted(): float
    {
        return (float) $this->runnerOrders()
            ->whereIn('status', ['preparing', 'on_the_way'])
            ->where('payment_method', 'cod')
            ->get()
            ->sum(fn (Order $o) => $o->cashToRemit());
    }

    public function cartItems(): HasMany
    {
        return $this->hasMany(CartItem::class);
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    public function getGenderLabelAttribute(): ?string
    {
        return match ($this->gender) {
            'male' => 'Male',
            'female' => 'Female',
            'other' => 'Other',
            'prefer_not_to_say' => 'Prefer not to say',
            default => null,
        };
    }

    public function getInterestsListAttribute(): array
    {
        if (!$this->interests) {
            return [];
        }

        return array_values(array_filter(array_map('trim', explode(',', $this->interests))));
    }

    public function getProfileCompletionAttribute(): int
    {
        $fields = [
            'profile_image', 'bio', 'gender', 'birthday', 'phone',
            'course', 'year_level', 'campus_location', 'favorite_food', 'interests',
        ];

        $filled = collect($fields)->filter(fn ($f) => !empty($this->{$f}))->count();

        return (int) round(($filled / count($fields)) * 100);
    }
}