@extends('layouts.shop')

@section('title', 'Wallet')
@section('nav', 'wallet')
@section('inline-errors', '1')

@section('content')
@php
    $icons = ['topup' => '➕', 'payment' => '🛍️', 'refund' => '↩️', 'earning' => '🛵', 'remittance' => '💸', 'adjustment' => '🛠️'];
    $isRunner = Auth::user()->isRunner();
    $withdrawable = $withdrawable ?? max(0, $balance - ($committed ?? 0));
    $activeTab = ($errors->has('cashout_amount') || $errors->has('cashout_method') || $errors->has('account_details')) ? 'cashout' : 'topup';
@endphp
<main class="wrap page">
    <div class="page-head">
        <div>
            <div class="eyebrow">UniFetch Wallet</div>
            <h1>My Wallet</h1>
            <p>Pay for orders without cash{{ $isRunner ? ', and cash out your runner earnings anytime' : '' }}.</p>
        </div>
    </div>

    <section class="wallet-hero">
        <div>
            <small>Available balance</small>
            <div class="amt">{{ \App\Support\Money::format($balance) }}</div>
            @if($isRunner && $committed > 0)
                <p>₱{{ number_format($committed, 2) }} is reserved for active cash jobs ({{ \App\Support\Money::format($withdrawable) }} available to cash out).</p>
            @endif
        </div>
        <div style="display:flex;gap:10px;flex-wrap:wrap">
            <a class="btn btn-primary" href="#walletActionCard" onclick="switchWalletTab('topup')">Add money</a>
            <a class="btn" href="#walletActionCard" onclick="switchWalletTab('cashout')" style="background:rgba(255,255,255,.14);color:#fff;border:1px solid rgba(255,255,255,.25)">Cash out</a>
        </div>
    </section>

    <div class="wallet-grid">
        <section class="panel">
            <div class="panel-head"><h2>Recent activity</h2></div>
            @forelse($transactions as $t)
                <div class="tx">
                    <div class="dot-ico">{{ $icons[$t->type] ?? '💳' }}</div>
                    <div class="grow">
                        <b>{{ $t->description }}</b>
                        <small>{{ $t->type_label }} · {{ $t->created_at->format('M j, g:i A') }}</small>
                    </div>
                    <div>
                        <span class="{{ $t->amount >= 0 ? 'amt-in' : 'amt-out' }}">{{ $t->amount >= 0 ? '+' : '−' }}{{ \App\Support\Money::format(abs($t->amount)) }}</span>
                        <span class="bal">Balance {{ \App\Support\Money::format($t->balance_after) }}</span>
                    </div>
                </div>
            @empty
                <div class="empty" style="border:0">
                    <div class="big">👛</div>
                    <h3>No activity yet</h3>
                    <p>Add some money to get started.</p>
                </div>
            @endforelse
        </section>

        <aside class="panel" id="walletActionCard" style="padding:22px">
            {{-- Top-up / Cash Out Switcher --}}
            <div class="wallet-tabs">
                <button type="button" class="wallet-tab {{ $activeTab === 'topup' ? 'on' : '' }}" id="tabTopupBtn" onclick="switchWalletTab('topup')">Add money</button>
                <button type="button" class="wallet-tab {{ $activeTab === 'cashout' ? 'on' : '' }}" id="tabCashoutBtn" onclick="switchWalletTab('cashout')">Cash out</button>
            </div>

            {{-- PANEL 1: ADD MONEY --}}
            <div id="topupPanel" style="{{ $activeTab === 'topup' ? '' : 'display:none;' }}">
                <p style="margin:0 0 14px;color:var(--muted);font-size:.9rem">Pick an amount or type your own.</p>

                <form method="POST" action="{{ route('wallet.topup') }}" id="topupForm" novalidate>
                    @csrf
                    <input type="hidden" name="payment_method" id="payment_method" value="{{ old('payment_method') }}">

                    <div class="chips">
                        @foreach($presets as $p)
                            <button class="chip" type="button" data-amt="{{ $p }}">₱{{ number_format($p) }}</button>
                        @endforeach
                    </div>

                    <div class="field">
                        <label class="lbl" for="amount">Amount (₱)</label>
                        <input class="input" id="amount" name="amount" type="number" min="20" max="5000" step="1" inputmode="numeric" value="{{ old('amount') }}" placeholder="e.g. 200" required>
                        @error('amount')<div class="err">{{ $message }}</div>@enderror
                    </div>

                    <div class="pay-method-box" role="radiogroup" aria-label="Top-up payment method">
                        <button type="button" class="pay-method-btn topup-method {{ old('payment_method') === 'qr_ph' ? 'on' : '' }}" data-method="qr_ph">
                            <svg width="22" height="16" viewBox="0 0 24 18" fill="none" aria-hidden="true">
                                <rect x="1" y="1" width="6" height="6" rx="1" stroke="#dc2626" stroke-width="1.8"/>
                                <rect x="10" y="1" width="6" height="6" rx="1" stroke="#2563eb" stroke-width="1.8"/>
                                <rect x="1" y="10" width="6" height="6" rx="1" stroke="#f59e0b" stroke-width="1.8"/>
                                <path d="M11 11h5v5h-5z" fill="#16182c"/>
                            </svg>
                            <span>QR PH</span>
                        </button>

                        <button type="button" class="pay-method-btn topup-method {{ old('payment_method') === 'gcash' ? 'on' : '' }}" data-method="gcash">
                            <svg width="19" height="19" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                                <rect width="24" height="24" rx="5" fill="#007dfe"/>
                                <path d="M13.2 12.2H17c-.3 2.5-2.2 4.3-4.9 4.3-2.9 0-5.1-2.1-5.1-4.8s2.2-4.8 5.1-4.8c1.5 0 2.8.6 3.7 1.5l-1.5 1.4c-.5-.5-1.3-.9-2.2-.9-1.7 0-2.9 1.2-2.9 2.8s1.2 2.8 2.9 2.8c1.4 0 2.4-.7 2.7-1.9h-1.6v-1.4z" fill="#ffffff"/>
                            </svg>
                            <span class="gcash-label">GCash</span>
                        </button>
                    </div>

                    {{-- QR Ph Preview Box (appears when QR PH is clicked) --}}
<div
    id="qrPreviewBox"
    style="{{ old('payment_method') === 'qr_ph' ? '' : 'display:none;' }} text-align:center; background:#f8fafc; border:1px solid #e2e8f0; border-radius:14px; padding:16px; margin-bottom:12px;"
>
    <p style="margin:0 0 10px; font-size:.82rem; font-weight:700; color:#16182c;">
        Scan with any QR Ph-enabled app
    </p>
    <img
        src="{{ asset('images/qr-ph.jpg') }}"
        alt="UniFetch QR Ph Code"
        style="display:block; margin:0 auto; width:180px; height:180px; object-fit:contain; border-radius:10px; background:#fff; padding:6px; border:1px solid #e5e7eb;"
    >
    <p style="margin:10px 0 0; font-size:.75rem; color:var(--muted);">
        Amount to pay: <b id="qrDisplayAmount">₱{{ old('amount') ?: '0' }}</b><br>
        Click <b>Pay</b> below after scanning.
    </p>
</div>

                    <div class="err" id="paymentMethodError" style="{{ $errors->has('payment_method') ? '' : 'display:none;' }}margin-bottom:10px;">
                        {{ $errors->first('payment_method') ?: 'Pick what kind of payment method.' }}
                    </div>

                    <button class="btn btn-primary btn-block" type="submit" style="border-radius:999px">Pay</button>
                </form>
            </div>

            {{-- PANEL 2: CASH OUT --}}
            <div id="cashoutPanel" style="{{ $activeTab === 'cashout' ? '' : 'display:none;' }}">
                <p style="margin:0 0 14px;color:var(--muted);font-size:.9rem">
                    Withdrawable balance: <b>{{ \App\Support\Money::format($withdrawable) }}</b>
                </p>

                <form method="POST" action="{{ route('wallet.cashout') }}" id="cashoutForm" novalidate>
                    @csrf
                    <input type="hidden" name="cashout_method" id="cashout_method" value="{{ old('cashout_method') }}">

                    <div class="field">
                        <label class="lbl" for="cashout_amount">Cash out amount (₱)</label>
                        <input class="input" id="cashout_amount" name="cashout_amount" type="number" min="50" max="{{ $withdrawable }}" step="1" inputmode="numeric" value="{{ old('cashout_amount') }}" placeholder="e.g. 500" required>
                        @error('cashout_amount')<div class="err">{{ $message }}</div>@enderror
                    </div>

                    <div class="field">
                        <label class="lbl" for="account_details">GCash Number / Account Name</label>
                        <input class="input" id="account_details" name="account_details" type="text" value="{{ old('account_details') }}" placeholder="e.g. 0917 123 4567 - Juan Dela Cruz" required>
                        @error('account_details')<div class="err">{{ $message }}</div>@enderror
                    </div>

                    <div class="pay-method-box" role="radiogroup" aria-label="Cash out method">
                        <button type="button" class="pay-method-btn cashout-method {{ old('cashout_method') === 'qr_ph' ? 'on' : '' }}" data-method="qr_ph">
                            <svg width="22" height="16" viewBox="0 0 24 18" fill="none" aria-hidden="true">
                                <rect x="1" y="1" width="6" height="6" rx="1" stroke="#dc2626" stroke-width="1.8"/>
                                <rect x="10" y="1" width="6" height="6" rx="1" stroke="#2563eb" stroke-width="1.8"/>
                                <rect x="1" y="10" width="6" height="6" rx="1" stroke="#f59e0b" stroke-width="1.8"/>
                                <path d="M11 11h5v5h-5z" fill="#16182c"/>
                            </svg>
                            <span>QR PH</span>
                        </button>

                        <button type="button" class="pay-method-btn cashout-method {{ old('cashout_method') === 'gcash' ? 'on' : '' }}" data-method="gcash">
                            <svg width="19" height="19" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                                <rect width="24" height="24" rx="5" fill="#007dfe"/>
                                <path d="M13.2 12.2H17c-.3 2.5-2.2 4.3-4.9 4.3-2.9 0-5.1-2.1-5.1-4.8s2.2-4.8 5.1-4.8c1.5 0 2.8.6 3.7 1.5l-1.5 1.4c-.5-.5-1.3-.9-2.2-.9-1.7 0-2.9 1.2-2.9 2.8s1.2 2.8 2.9 2.8c1.4 0 2.4-.7 2.7-1.9h-1.6v-1.4z" fill="#ffffff"/>
                            </svg>
                            <span class="gcash-label">GCash</span>
                        </button>
                    </div>

                    <div class="err" id="cashoutMethodError" style="{{ $errors->has('cashout_method') ? '' : 'display:none;' }}margin-bottom:10px;">
                        {{ $errors->first('cashout_method') ?: 'Pick what kind of payment method.' }}
                    </div>

                    <button class="btn btn-primary btn-block" type="submit" style="border-radius:999px">Cash out</button>
                </form>
            </div>

            <div style="display:flex;align-items:flex-start;gap:6px;margin-top:12px;font-size:.75rem;color:var(--muted);line-height:1.4">
                <span aria-hidden="true">🔒</span>
                <span>Payment data is encrypted. Your transaction is secure</span>
            </div>

            <p style="margin:24px 0 0;font-size:.74rem;color:var(--muted);line-height:1.45">
                By proceeding, I agree to UniFetch's <a href="#terms" style="color:#0284c7;font-weight:700;text-decoration:none">T&amp;C</a> and <a href="#refund" style="color:#0284c7;font-weight:700;text-decoration:none">Refund Policy</a>
            </p>
        </aside>
    </div>
</main>

<style>
    .wallet-tabs {
        display: grid;
        grid-template-columns: 1fr 1fr;
        background: #f3f4f6;
        padding: 4px;
        border-radius: 12px;
        margin-bottom: 12px;
    }
    .wallet-tab {
        border: 0;
        background: transparent;
        padding: 8px 12px;
        border-radius: 9px;
        font-size: .9rem;
        font-weight: 800;
        color: var(--muted);
        cursor: pointer;
        transition: all .15s ease;
    }
    .wallet-tab.on {
        background: #ffffff;
        color: #16182c;
        box-shadow: 0 2px 6px rgba(0, 0, 0, .06);
    }
    .pay-method-box {
        background: #f3f4f6;
        border-radius: 14px;
        padding: 10px;
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 10px;
        margin: 14px 0 10px;
    }
    .pay-method-btn {
        background: #ffffff;
        border: 2px solid #e5e7eb;
        border-radius: 10px;
        padding: 10px 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 7px;
        font-size: .85rem;
        font-weight: 800;
        color: #16182c;
        cursor: pointer;
        box-shadow: 0 2px 5px rgba(0, 0, 0, .05);
        transition: border-color .15s ease, box-shadow .15s ease, background .15s ease;
    }
    .pay-method-btn.on {
        border-color: var(--brand, #ff6b35);
        background: #fff8f5;
        box-shadow: 0 0 0 3px rgba(255, 107, 53, .16);
    }
    .pay-method-btn .gcash-label {
        color: #007dfe;
    }
</style>
@endsection

@push('scripts')
<script>
    function switchWalletTab(tab) {
        const isTopup = tab === 'topup';
        document.getElementById('topupPanel').style.display = isTopup ? 'block' : 'none';
        document.getElementById('cashoutPanel').style.display = isTopup ? 'none' : 'block';
        document.getElementById('tabTopupBtn').classList.toggle('on', isTopup);
        document.getElementById('tabCashoutBtn').classList.toggle('on', !isTopup);
    }

    // Preset top-up chips & live QR amount sync
    const amountInput = document.getElementById('amount');
    const qrPreviewBox = document.getElementById('qrPreviewBox');
    const qrDisplayAmount = document.getElementById('qrDisplayAmount');

    function syncQrAmount() {
        const val = Number(amountInput.value || 0);
        qrDisplayAmount.textContent = '₱' + val.toLocaleString();
    }

    document.querySelectorAll('.chip').forEach(function (c) {
        c.addEventListener('click', function () {
            amountInput.value = c.dataset.amt;
            document.querySelectorAll('.chip').forEach(function (x) { x.classList.toggle('on', x === c); });
            syncQrAmount();
        });
    });

    amountInput.addEventListener('input', syncQrAmount);

    // Top-up method selector
    const methodInput = document.getElementById('payment_method');
    const methodError = document.getElementById('paymentMethodError');
    document.querySelectorAll('.topup-method').forEach(function (btn) {
        btn.addEventListener('click', function () {
            methodInput.value = btn.dataset.method;
            document.querySelectorAll('.topup-method').forEach(function (x) { x.classList.toggle('on', x === btn); });
            methodError.style.display = 'none';
            qrPreviewBox.style.display = btn.dataset.method === 'qr_ph' ? 'block' : 'none';
            syncQrAmount();
        });
    });

    document.getElementById('topupForm').addEventListener('submit', function (e) {
        if (!methodInput.value) {
            e.preventDefault();
            methodError.textContent = 'Pick what kind of payment method.';
            methodError.style.display = 'block';
        }
    });

    // Cash out method selector
    const cashoutMethodInput = document.getElementById('cashout_method');
    const cashoutMethodError = document.getElementById('cashoutMethodError');
    document.querySelectorAll('.cashout-method').forEach(function (btn) {
        btn.addEventListener('click', function () {
            cashoutMethodInput.value = btn.dataset.method;
            document.querySelectorAll('.cashout-method').forEach(function (x) { x.classList.toggle('on', x === btn); });
            cashoutMethodError.style.display = 'none';
        });
    });

    document.getElementById('cashoutForm').addEventListener('submit', function (e) {
        if (!cashoutMethodInput.value) {
            e.preventDefault();
            cashoutMethodError.textContent = 'Pick what kind of payment method.';
            cashoutMethodError.style.display = 'block';
        }
    });
</script>
@endpush