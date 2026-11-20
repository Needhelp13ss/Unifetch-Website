@extends('layouts.shop')

@section('title', 'Wallet')
@section('nav', 'wallet')
@section('inline-errors', '1')

@section('content')
@php
    $icons = ['topup' => '➕', 'payment' => '🛍️', 'refund' => '↩️', 'earning' => '🛵', 'remittance' => '🤝', 'adjustment' => '🛠️'];
    $isRunner = Auth::user()->isRunner();
@endphp
<main class="wrap page">
    <div class="page-head">
        <div>
            <div class="eyebrow">UniFetch Wallet</div>
            <h1>My Wallet</h1>
            <p>Pay for orders without cash{{ $isRunner ? ', and settle the cash you collect as a runner' : '' }}.</p>
        </div>
    </div>

    <section class="wallet-hero">
        <div>
            <small>Available balance</small>
            <div class="amt">{{ \App\Support\Money::format($balance) }}</div>
            @if($isRunner && $committed > 0)
                <p>₱{{ number_format($committed, 2) }} of it is reserved for your active cash jobs.</p>
            @endif
        </div>
        <a class="btn btn-primary" href="#topup">Add money</a>
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

        <aside class="panel" id="topup" style="padding:22px">
            <h2 style="margin:0 0 4px;font-size:1.15rem">Add money</h2>
            <p style="margin:0 0 14px;color:var(--muted);font-size:.9rem">Pick an amount or type your own.</p>

            <form method="POST" action="{{ route('wallet.topup') }}">
                @csrf
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
                <button class="btn btn-primary btn-block" type="submit">Add to wallet</button>
            </form>

            <div class="note-box">
                <b>Demo mode:</b> money is added instantly so your class can try the whole flow. A real payment (GCash / Maya) would connect here.
                @if($isRunner)<br><br><b>Runner reminder:</b> when a customer pays you in cash, the amount you owe UniFetch is taken from this wallet after you mark the order delivered. Keep it topped up to accept cash jobs.@endif
            </div>
        </aside>
    </div>
</main>
@endsection

@push('scripts')
<script>
    document.querySelectorAll('.chip').forEach(function (c) {
        c.addEventListener('click', function () {
            document.getElementById('amount').value = c.dataset.amt;
            document.querySelectorAll('.chip').forEach(function (x) { x.classList.toggle('on', x === c); });
        });
    });
</script>
@endpush
