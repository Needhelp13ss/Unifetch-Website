@extends('layouts.admin')
@section('title', $person->name)
@section('nav', 'users')

@section('content')
@php($m = fn ($n) => \App\Support\Money::format($n))
<main class="wrap page">
    <div class="page-head">
        <div><div class="eyebrow">User</div><h1>{{ $person->name }} <span class="role {{ $person->role }}" style="vertical-align:middle">{{ \App\Models\User::ROLES[$person->role] ?? $person->role }}</span></h1>
            <p>{{ $person->student_id }} · {{ $person->email }}@if($person->phone) · {{ $person->phone }}@endif</p>
            @if($person->isVerifiedStudent())<span class="ok-badge">✓ UCLM student</span>@endif
            @if($person->portal_id || $person->id_photo_path)
                <div class="verify"><span>🪪 Portal ID: <b>{{ $person->portal_id ?? '—' }}</b></span><span>🎓 {{ $person->course ?? '—' }} · {{ $person->year_level ?? '—' }}</span>
                    @if($person->id_photo_path)<a class="id-link" href="{{ route('admin.runners.id', $person) }}" target="_blank" rel="noopener">View school ID photo ↗</a>@endif</div>
            @endif
        </div>
        <a class="link-arrow" href="{{ route('admin.users') }}">← All users</a>
    </div>

    <div class="profile-grid">
        <div style="display:grid;gap:24px">
            <section class="panel">
                <div class="panel-head"><h2>Wallet</h2><b style="font-size:1.4rem">{{ $m($person->wallet_balance) }}</b></div>
                @unless($person->isAdmin())
                <div class="sec">
                    <p class="mutedp">Add money (for example cash received) or take it away (corrections). It is saved in their history with your reason.</p>
                    <form method="POST" action="{{ route('admin.users.wallet', $person) }}" class="inline-form">@csrf
                        <div class="field"><label class="lbl" for="direction">Action</label>
                            <select class="input" id="direction" name="direction"><option value="add">Add money</option><option value="deduct">Deduct money</option></select></div>
                        <div class="field"><label class="lbl" for="amount">Amount (₱)</label><input class="input" id="amount" name="amount" type="number" min="1" step="1" required></div>
                        <div class="field" style="flex:2"><label class="lbl" for="reason">Reason</label><input class="input" id="reason" name="reason" maxlength="120" required placeholder="e.g. Cash top-up received"></div>
                        <button class="btn btn-dark" type="submit">Save</button>
                    </form>
                </div>
                @endunless
                @forelse($transactions as $t)
                    <div class="tx"><div class="grow"><b>{{ $t->description }}</b><small>{{ $t->type_label }} · {{ $t->created_at->format('M j, g:i A') }}</small></div>
                        <span class="{{ $t->amount >= 0 ? 'amt-in' : 'amt-out' }}">{{ $t->amount >= 0 ? '+' : '−' }}{{ $m(abs($t->amount)) }}</span></div>
                @empty
                    <div style="padding:22px;color:var(--muted)">No wallet activity.</div>
                @endforelse
            </section>
        </div>

        <div style="display:grid;gap:24px">
            <section class="panel">
                <div class="panel-head"><h2>Orders placed ({{ $orders->count() }})</h2></div>
                @forelse($orders as $o)
                    <a class="item-row" href="{{ route('admin.orders.show', $o) }}"><div class="grow"><b>{{ $o->order_number }}</b><small>{{ $o->summary }}</small></div><span class="status {{ $o->status }}">{{ $o->status_label }}</span></a>
                @empty
                    <div style="padding:22px;color:var(--muted)">No orders.</div>
                @endforelse
            </section>
            @if($person->isRunner() || $jobs->isNotEmpty())
            <section class="panel">
                <div class="panel-head"><h2>Jobs run ({{ $jobs->count() }})</h2></div>
                @foreach($jobs as $o)
                    <a class="item-row" href="{{ route('admin.orders.show', $o) }}"><div class="grow"><b>{{ $o->order_number }}</b><small>{{ $o->summary }}</small></div><span class="status {{ $o->status }}">{{ $o->status_label }}</span></a>
                @endforeach
            </section>
            @endif

            @unless($person->isAdmin())
            <section class="panel"><div class="sec">
                <h2 class="sub">Danger zone</h2>
                <p class="mutedp">Deleting an account also deletes their orders and wallet history.</p>
                <form method="POST" action="{{ route('admin.users.destroy', $person) }}" onsubmit="return confirm('Delete {{ addslashes($person->name) }} and all their data?')">@csrf @method('DELETE')
                    <button class="btn btn-danger" type="submit">Delete this user</button></form>
            </div></section>
            @endunless
        </div>
    </div>
</main>
@endsection
