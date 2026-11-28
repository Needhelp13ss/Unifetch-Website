@extends($user->isShop() ? 'layouts.admin' : 'layouts.shop')
@section('title', 'History')
@section('nav', 'history')

@section('content')
<link rel="stylesheet" href="{{ asset('css/admin.css') }}">
@php($m = fn ($n) => \App\Support\Money::format($n))
<main class="wrap page">
    <div class="page-head">
        <div><div class="eyebrow">Transaction history</div><h1>History</h1>
            <p>All your old and new transactions, with who ordered and who ran each one.</p></div>
    </div>

    <form class="toolbar" method="GET" action="{{ route('history') }}">
        <input class="input" name="q" value="{{ $q }}" placeholder="Order number">
        <select class="input" name="status"><option value="">All statuses</option>
            @foreach(\App\Models\Order::STATUSES as $k => $label)<option value="{{ $k }}" @selected($status === $k)>{{ $label }}</option>@endforeach</select>
        <select class="input" name="type"><option value="">All types</option>
            <option value="food" @selected($type === 'food')>Orders</option><option value="custom" @selected($type === 'custom')>Custom orders</option><option value="errand" @selected($type === 'errand')>Errands</option></select>
        <button class="btn btn-dark" type="submit">Filter</button>
    </form>

    <section class="panel">
        <div class="table-wrap"><table class="tbl">
            <thead><tr><th>Order</th><th>Date &amp; time</th><th>Customer</th><th>Runner</th><th>Your role</th><th>Status</th><th class="num">Total</th></tr></thead>
            <tbody>
            @forelse($orders as $o)
                <tr>
                    <td><a class="strong" href="{{ route('orders.show', $o) }}">{{ $o->order_number }}</a><span class="type-chip">{{ $o->type_label }}</span><small>{{ \Illuminate\Support\Str::limit($o->summary, 40) }}</small></td>
                    <td>{{ $o->created_at->format('D, M j, Y') }}<small>{{ $o->created_at->format('g:i A') }}@if($o->delivered_at) · delivered {{ $o->delivered_at->format('M j, g:i A') }}@endif</small></td>
                    <td>{{ $o->user->name }}</td>
                    <td>{{ $o->runner?->name ?? '—' }}</td>
                    <td>{{ $user->isShop() ? 'Seller' : ($o->user_id === $user->id ? 'Customer' : 'Runner') }}</td>
                    <td><span class="status {{ $o->status }}">{{ $o->status_label }}</span></td>
                    <td class="num">{{ $user->isShop() ? $m($o->items->where('vendor_name', $user->vendor?->name)->sum(fn ($i) => $i->line_total)) : ($o->runner_id === $user->id && $o->user_id !== $user->id ? '+' . $m($o->runnerEarning()) : $m($o->total)) }}</td>
                </tr>
            @empty
                <tr><td colspan="7" style="text-align:center;color:var(--muted);padding:30px">Nothing here yet.</td></tr>
            @endforelse
            </tbody>
        </table></div>
    </section>
    <div class="pager">{{ $orders->links('pagination.unifetch') }}</div>
</main>
@endsection
