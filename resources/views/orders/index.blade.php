@extends('layouts.shop')

@section('title', 'My Orders')
@section('nav', 'orders')

@section('content')
<main class="wrap page">
    <div class="page-head">
        <div>
            <div class="eyebrow">Order history</div>
            <h1>My Orders</h1>
            <p>Your current and past orders.</p>
        </div>
        <div style="display:flex;gap:10px;flex-wrap:wrap">
            <a class="btn btn-ghost" href="{{ route('errand.create') }}">Errand Services</a>
            <a class="btn btn-primary" href="{{ route('menu') }}">Order something</a>
        </div>
    </div>

    @forelse($orders as $order)
        <a class="order-card" href="{{ route('orders.show', $order) }}">
            <div class="order-top">
                <div>
                    <div class="order-no">{{ $order->order_number }}@if($order->isRequest())<span class="type-chip">{{ $order->type_label }}</span>@endif</div>
                    <div class="order-date">{{ $order->created_at->format('M j, Y · g:i A') }}</div>
                </div>
                <span class="status {{ $order->status }}">{{ $order->status_label }}</span>
            </div>
            <div class="order-items">{{ $order->summary }}</div>
            <div class="order-bottom">
                <span class="order-date">{{ $order->payment_label }}</span>
                <span class="order-total">{{ \App\Support\Money::format($order->total) }}</span>
            </div>
        </a>
    @empty
        <div class="empty">
            <div class="big">📦</div>
            <h3>No orders yet</h3>
            <p>When you place an order it will show up here.</p>
            <a class="btn btn-primary" href="{{ route('menu') }}">Browse the menu</a>
        </div>
    @endforelse
</main>
@endsection
