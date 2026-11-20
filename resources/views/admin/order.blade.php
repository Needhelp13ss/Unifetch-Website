@extends('layouts.admin')
@section('title', 'Order ' . $order->order_number)
@section('nav', 'orders')

@section('content')
@php($m = fn ($n) => \App\Support\Money::format($n))
<main class="wrap page">
    <div class="page-head">
        <div><div class="eyebrow">{{ $order->type_label }}</div><h1>{{ $order->order_number }}</h1>
            <p>Placed {{ $order->created_at->format('M j, Y · g:i A') }} by <a href="{{ route('admin.users.show', $order->user) }}" style="font-weight:600">{{ $order->user->name }}</a></p></div>
        <a class="link-arrow" href="{{ route('admin.orders') }}">← All orders</a>
    </div>

    <div class="detail-grid">
        <div style="display:grid;gap:24px">
            <section class="panel">
                <div class="panel-head"><h2>{{ $order->isRequest() ? $order->type_label : 'Items' }}</h2><span class="status {{ $order->status }}">{{ $order->status_label }}</span></div>
                @if($order->isRequest())
                    @include('orders._request_details')
                @else
                    @foreach($order->items as $item)
                        <div class="item-row"><div class="mini">{{ $item->emoji }}</div>
                            <div class="grow"><b>{{ $item->name }}</b><small>{{ $item->vendor_name }} · {{ $m($item->price) }} × {{ $item->quantity }}</small></div><b>{{ $m($item->line_total) }}</b></div>
                    @endforeach
                    <div style="padding:6px 22px 18px">
                        <div class="sum-line"><span>Subtotal</span><span>{{ $m($order->subtotal) }}</span></div>
                        <div class="sum-line"><span>Delivery fee</span><span>{{ $m($order->delivery_fee) }}</span></div>
                        <div class="sum-line total"><span>Total</span><span>{{ $m($order->total) }}</span></div>
                    </div>
                @endif
            </section>

            <section class="panel"><div class="sec">
                <h2 class="sub">Manage</h2>
                @if($order->status === 'pending' || $order->status === 'preparing')
                    <form method="POST" action="{{ route('admin.orders.assign', $order) }}" class="inline-form" style="margin-bottom:14px">@csrf
                        <div class="field"><label class="lbl" for="runner_id">{{ $order->runner ? 'Change runner' : 'Assign a runner' }}</label>
                            <select class="input" id="runner_id" name="runner_id" required>
                                <option value="">Choose a runner…</option>
                                @foreach($runners as $r)<option value="{{ $r->id }}" @selected($order->runner_id === $r->id)>{{ $r->name }}</option>@endforeach
                            </select></div>
                        <button class="btn btn-dark" type="submit">Assign</button>
                    </form>
                @endif
                <div class="row-actions" style="justify-content:flex-start">
                    @if($order->status === 'preparing')
                        <form method="POST" action="{{ route('admin.orders.start', $order) }}">@csrf <button class="btn btn-ghost btn-sm" type="submit">Mark on the way</button></form>
                        <form method="POST" action="{{ route('admin.orders.release', $order) }}">@csrf <button class="btn btn-ghost btn-sm" type="submit">Back to looking for runners</button></form>
                    @endif
                    @if($order->status === 'on_the_way')
                        <form method="POST" action="{{ route('admin.orders.complete', $order) }}" onsubmit="return confirm('Mark delivered and settle wallet money?')">@csrf <button class="btn btn-ok btn-sm" type="submit">Mark delivered</button></form>
                    @endif
                    @if($order->isActive())
                        <form method="POST" action="{{ route('admin.orders.cancel', $order) }}" onsubmit="return confirm('Cancel this order?{{ $order->payment_method === 'wallet' ? ' The customer is refunded.' : '' }}')">@csrf <button class="btn btn-danger btn-sm" type="submit">Cancel order</button></form>
                    @else
                        <form method="POST" action="{{ route('admin.orders.destroy', $order) }}" onsubmit="return confirm('Delete this order record for good?')">@csrf @method('DELETE') <button class="btn btn-danger btn-sm" type="submit">Delete record</button></form>
                    @endif
                </div>
            </div></section>
        </div>

        <aside class="panel">
            <div class="panel-head"><h2>Details</h2></div>
            <div class="kv">
                <div><small>Customer</small><b>{{ $order->user->name }}@if($order->user->phone) · {{ $order->user->phone }}@endif</b></div>
                <div><small>Runner</small><b>{{ $order->runner?->name ?? 'Nobody yet' }}</b></div>
                <div><small>Deliver to</small><b>{{ $order->delivery_location }}</b></div>
                <div><small>Payment</small><b>{{ $order->payment_label }}</b></div>
                @if($order->notes)<div><small>Notes</small><b>{{ $order->notes }}</b></div>@endif
                @if($order->cashToRemit() > 0)<div><small>Cash runner owes UniFetch</small><b>{{ $m($order->cashToRemit()) }} @if($order->remitted > 0)<span style="color:var(--green)">(settled)</span>@endif</b></div>@endif
            </div>
        </aside>
    </div>
</main>
@endsection
