@extends('layouts.shop')

@section('title', 'Order ' . $order->order_number)
@section('nav', 'orders')

@section('content')
@php
    $flow = [
        'placed' => ['🧾', 'Placed'],
        'pending' => ['🔎', 'Looking for runners'],
        'preparing' => ['👨‍🍳', 'Preparing'],
        'on_the_way' => ['🛵', 'On the way'],
        'delivered' => ['✅', 'Delivered'],
    ];
    $keys = array_keys($flow);
    // "placed" is always finished; the order's own status is the step that is happening now
    $at = array_search($order->status, $keys);
    $runner = $order->runner;
    $isCustomer = $me->id === $order->user_id;
@endphp
<main class="wrap page">
    <div class="page-head">
        <div>
            <div class="eyebrow">{{ $order->isRequest() ? $order->type_label : 'Order details' }}</div>
            <h1>{{ $order->order_number }}</h1>
            <p>Placed {{ $order->created_at->format('M j, Y · g:i A') }}</p>
        </div>
        <a class="link-arrow" href="{{ $me->isShop() ? route('shop.dashboard') : route('history') }}">← {{ $me->isShop() ? 'Shop orders' : 'History' }}</a>
    </div>

    {{-- Progress --}}
    <section class="panel" style="padding:24px 22px;margin-bottom:24px">
        @if($order->status === 'cancelled')
            <span class="status cancelled">Cancelled</span>
            <p style="margin:10px 0 0;color:var(--muted)">This order was cancelled.@if($order->payment_method === 'wallet') Your payment was returned to your wallet.@endif</p>
        @else
            <div class="tracker five">
                @foreach($flow as $key => [$icon, $label])
                    @php($i = array_search($key, $keys))
                    <div class="tstep {{ $i < $at ? 'done' : ($i === $at ? 'now' : '') }} {{ $key === 'pending' && $i === $at ? 'searching' : '' }}">
                        <i>{{ $i < $at ? '✓' : $icon }}</i>{{ $label }}
                    </div>
                @endforeach
            </div>

            @if(!$isCustomer)
                {{-- the runner and the shop see the people on the order below --}}
            @elseif($order->status === 'pending')
                <div class="runner-note wait">
                    <span class="em">🔎</span>
                    <div><b>Looking for a runner…</b>
                        <small>Nobody has taken your {{ $order->isErrand() ? 'errand' : 'order' }} yet. You'll see your runner here as soon as someone does. You can cancel until then.</small></div>
                </div>
            @elseif($runner && in_array($order->status, ['preparing', 'on_the_way']))
                <div class="runner-note ok">
                    <span class="em">🛵</span>
                    <div><b>{{ $runner->name }} is {{ $order->status === 'on_the_way' ? 'on the way to you' : 'on it' }}!</b>
                        <small>@if($runner->phone) Contact: {{ $runner->phone }} · @endif Taken {{ $order->accepted_at?->diffForHumans() }}</small></div>
                </div>
            @elseif($order->status === 'delivered' && $runner)
                <div class="runner-note ok">
                    <span class="em">✅</span>
                    <div><b>Delivered by {{ $runner->name }}</b><small>{{ $order->delivered_at?->format('M j, g:i A') }}</small></div>
                </div>
            @endif
        @endif
    </section>

    <div class="detail-grid">
        <section class="panel">
            <div class="panel-head"><h2>{{ $order->isRequest() ? $order->type_label : 'Items' }}</h2><span class="status {{ $order->status }}">{{ $order->status_label }}</span></div>

            @if($order->isRequest())
                @include('orders._request_details')
            @else
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
                    <div class="sum-line"><span>Runner fee @if($order->distance_m)<small style="color:var(--muted)">({{ $order->distance_m }} m)</small>@endif</span><span>{{ \App\Support\Money::format($order->delivery_fee) }}</span></div>
                    <div class="sum-line"><span>Service fee</span><span>{{ \App\Support\Money::format($order->service_fee) }}</span></div>
                    <div class="sum-line total"><span>Total</span><span>{{ \App\Support\Money::format($order->total) }}</span></div>
                </div>
            @endif
        </section>

        <aside class="panel">
            <div class="panel-head"><h2>Delivery &amp; payment</h2></div>
            <div class="kv">
                <div><small>Customer</small><b>{{ $order->user->name }}</b></div>
                <div><small>Runner</small><b>{{ $runner?->name ?? 'Not taken yet' }}</b></div>
                @if(!$order->isRequest())<div><small>Shop</small><b>{{ implode(', ', $order->shopNames()) }}</b></div>@endif
                <div><small>Deliver to</small><b>{{ $order->delivery_location }}</b></div>
                <div><small>Payment</small><b>{{ $order->payment_label }}</b></div>
                @if($order->notes)<div><small>Notes</small><b>{{ $order->notes }}</b></div>@endif
            </div>
            @if($isCustomer && $order->canBeCancelled())
                <form method="POST" action="{{ route('orders.cancel', $order) }}" style="padding:0 22px 22px"
                      onsubmit="return confirm('Cancel this order?')">
                    @csrf @method('PATCH')
                    <button class="btn btn-danger btn-block" type="submit">Cancel order</button>
                </form>
            @endif
        </aside>
    </div>

    @include('orders._proof', ['readonly' => false])
    @if($order->hasChat() && ($isCustomer || $me->id === $order->runner_id))
        @include('orders._chat')
    @endif
</main>
@endsection
