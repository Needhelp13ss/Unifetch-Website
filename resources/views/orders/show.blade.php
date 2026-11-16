@extends('layouts.shop')

@section('title', 'Order ' . $order->order_number)
@section('nav', 'orders')

@section('content')
@php
    $flow = [
        'pending' => ['🧾', 'Placed'],
        'preparing' => ['👨‍🍳', 'Preparing'],
        'on_the_way' => ['🛵', 'On the way'],
        'delivered' => ['✅', 'Delivered'],
    ];
    $keys = array_keys($flow);
    $at = array_search($order->status, $keys);
@endphp
<main class="wrap page">
    <div class="page-head">
        <div>
            <div class="eyebrow">Order details</div>
            <h1>{{ $order->order_number }}</h1>
            <p>Placed {{ $order->created_at->format('M j, Y · g:i A') }}</p>
        </div>
        <a class="link-arrow" href="{{ route('orders') }}">← All orders</a>
    </div>

    {{-- Progress --}}
    <section class="panel" style="padding:24px 22px;margin-bottom:24px">
        @if($order->status === 'cancelled')
            <span class="status cancelled">Cancelled</span>
            <p style="margin:10px 0 0;color:var(--muted)">This order was cancelled.</p>
        @else
            <div class="tracker">
                @foreach($flow as $key => [$icon, $label])
                    @php($i = array_search($key, $keys))
                    <div class="tstep {{ $i < $at ? 'done' : ($i === $at ? 'now' : '') }}">
                        <i>{{ $i < $at ? '✓' : $icon }}</i>{{ $label }}
                    </div>
                @endforeach
            </div>
        @endif
    </section>

    <div class="detail-grid">
        <section class="panel">
            <div class="panel-head"><h2>Items</h2><span class="status {{ $order->status }}">{{ $order->status_label }}</span></div>
            @foreach($order->items as $item)
                <div class="item-row">
                    <div class="mini">{{ $item->emoji }}</div>
                    <div class="grow">
                        <b>{{ $item->name }}</b>
                        <small>{{ $item->vendor_name }} · {{ \App\Support\Money::format($item->price) }} × {{ $item->quantity }}</small>
                    </div>
                    <b>{{ \App\Support\Money::format($item->line_total) }}</b>
                </div>
            @endforeach
            <div style="padding:6px 22px 18px">
                <div class="sum-line"><span>Subtotal</span><span>{{ \App\Support\Money::format($order->subtotal) }}</span></div>
                <div class="sum-line"><span>Delivery fee</span><span>{{ \App\Support\Money::format($order->delivery_fee) }}</span></div>
                <div class="sum-line total"><span>Total</span><span>{{ \App\Support\Money::format($order->total) }}</span></div>
            </div>
        </section>

        <aside class="panel">
            <div class="panel-head"><h2>Delivery &amp; payment</h2></div>
            <div class="kv">
                <div><small>Deliver to</small><b>{{ $order->delivery_location }}</b></div>
                <div><small>Payment</small><b>{{ $order->payment_label }}</b></div>
                @if($order->notes)<div><small>Notes</small><b>{{ $order->notes }}</b></div>@endif
            </div>
            @if($order->canBeCancelled())
                <form method="POST" action="{{ route('orders.cancel', $order) }}" style="padding:0 22px 22px"
                      onsubmit="return confirm('Cancel this order?')">
                    @csrf @method('PATCH')
                    <button class="btn btn-danger btn-block" type="submit">Cancel order</button>
                </form>
            @endif
        </aside>
    </div>
</main>
@endsection
