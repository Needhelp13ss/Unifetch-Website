@extends('layouts.admin')
@section('title', 'Orders')
@section('nav', 'orders')

@section('content')
<main class="wrap page">
    <div class="page-head"><div><div class="eyebrow">Admin</div><h1>Orders</h1><p>Orders, custom orders and errands.</p></div></div>

    <form class="toolbar" method="GET">
        <input class="input" type="search" name="q" value="{{ $q }}" placeholder="Order number or customer">
        <select class="input" name="status"><option value="">All statuses</option>
            @foreach(\App\Models\Order::STATUSES as $k => $l)<option value="{{ $k }}" @selected($status === $k)>{{ $l }}</option>@endforeach</select>
        <select class="input" name="type"><option value="">All types</option><option value="food" @selected($type === 'food')>Orders</option><option value="custom" @selected($type === 'custom')>Custom orders</option><option value="errand" @selected($type === 'errand')>Errands</option></select>
        <button class="btn btn-dark" type="submit">Filter</button>
    </form>

    <section class="panel"><div class="table-wrap"><table class="tbl">
        <thead><tr><th>Order</th><th>Customer</th><th>Runner</th><th>Payment</th><th>Status</th><th class="num">Total</th></tr></thead>
        <tbody>
        @forelse($orders as $o)
            <tr>
                <td><a class="strong" href="{{ route('admin.orders.show', $o) }}">{{ $o->order_number }}</a>@if($o->isRequest())<span class="type-chip">{{ $o->type_label }}</span>@endif<small>{{ $o->created_at->format('M j, g:i A') }}</small></td>
                <td>{{ $o->user->name }}</td>
                <td>{{ $o->runner?->name ?? '—' }}</td>
                <td>{{ $o->payment_label }}</td>
                <td><span class="status {{ $o->status }}">{{ $o->status_label }}</span></td>
                <td class="num">{{ \App\Support\Money::format($o->total) }}</td>
            </tr>
        @empty
            <tr><td colspan="6" style="text-align:center;color:var(--muted);padding:30px">No orders match.</td></tr>
        @endforelse
        </tbody>
    </table></div></section>
    {{ $orders->links('pagination.unifetch') }}
</main>
@endsection
