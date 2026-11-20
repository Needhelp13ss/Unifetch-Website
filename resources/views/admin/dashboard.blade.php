@extends('layouts.admin')
@section('title', 'Overview')
@section('nav', 'dash')

@section('content')
@php($m = fn ($n) => \App\Support\Money::format($n))
<main class="wrap page">
    <div class="page-head">
        <div><div class="eyebrow">Admin</div><h1>Overview</h1><p>A quick look at how UniFetch is doing. The admin account can check and manage things, but can't place orders.</p></div>
    </div>

    <div class="kpis">
        <div class="kpi {{ $stats['pending'] ? 'alert-kpi' : '' }}"><small>Runner applications</small><b>{{ $stats['pending'] }}</b><span><a href="{{ route('admin.runners') }}" style="color:var(--orange);font-weight:600">Review →</a></span></div>
        <div class="kpi"><small>Waiting for a runner</small><b>{{ $stats['waiting'] }}</b><span>{{ $stats['active'] }} active orders</span></div>
        <div class="kpi"><small>Orders today</small><b>{{ $stats['orders_today'] }}</b><span>Delivered sales: {{ $m($stats['sales']) }}</span></div>
        <div class="kpi"><small>People</small><b>{{ $stats['customers'] + $stats['runners'] }}</b><span>{{ $stats['customers'] }} customers · {{ $stats['runners'] }} runners</span></div>
    </div>

    <div class="two-col">
        <section class="panel">
            <div class="panel-head"><h2>Recent orders</h2><a class="link-arrow" href="{{ route('admin.orders') }}">All orders →</a></div>
            <div class="table-wrap"><table class="tbl">
                <thead><tr><th>Order</th><th>Customer</th><th>Runner</th><th>Status</th><th class="num">Total</th></tr></thead>
                <tbody>
                @forelse($recent as $o)
                    <tr>
                        <td><a class="strong" href="{{ route('admin.orders.show', $o) }}">{{ $o->order_number }}</a>@if($o->isRequest())<span class="type-chip">{{ $o->type_label }}</span>@endif<small>{{ $o->created_at->diffForHumans() }}</small></td>
                        <td>{{ $o->user->name }}</td>
                        <td>{{ $o->runner?->name ?? '—' }}</td>
                        <td><span class="status {{ $o->status }}">{{ $o->status_label }}</span></td>
                        <td class="num">{{ $m($o->total) }}</td>
                    </tr>
                @empty
                    <tr><td colspan="5" style="text-align:center;color:var(--muted);padding:30px">No orders yet.</td></tr>
                @endforelse
                </tbody>
            </table></div>
        </section>

        <div style="display:grid;gap:24px">
            <section class="panel">
                <div class="panel-head"><h2>Pending runners</h2><a class="link-arrow" href="{{ route('admin.runners') }}">Manage →</a></div>
                @forelse($applications as $a)
                    <div class="item-row">
                        <div class="grow"><b>{{ $a->name }}</b><small>{{ $a->student_id }} · applied {{ $a->runner_applied_at?->diffForHumans() }}</small></div>
                        <form method="POST" action="{{ route('admin.runners.approve', $a) }}">@csrf @method('PATCH')<button class="btn btn-ok btn-sm" type="submit">Approve</button></form>
                    </div>
                @empty
                    <div style="padding:24px;color:var(--muted)">Nobody is waiting for approval.</div>
                @endforelse
            </section>

            <section class="panel">
                <div class="panel-head"><h2>Wallet money</h2></div>
                <div style="padding:6px 22px 4px"><div class="kpi" style="border:0;padding:12px 0"><small>Held in all wallets</small><b>{{ $m($stats['wallets']) }}</b></div></div>
                @foreach($transactions as $t)
                    <div class="item-row"><div class="grow"><b>{{ $t->user->name }}</b><small>{{ $t->description }}</small></div>
                        <b style="color:{{ $t->amount >= 0 ? 'var(--green)' : 'var(--red)' }}">{{ $t->amount >= 0 ? '+' : '−' }}{{ $m(abs($t->amount)) }}</b></div>
                @endforeach
            </section>
        </div>
    </div>
</main>
@endsection
