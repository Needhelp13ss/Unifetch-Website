@extends('layouts.admin')
@section('title', 'Shop orders')
@section('nav', 'dash')

@section('content')
@php($m = fn ($n) => \App\Support\Money::format($n))
<main class="wrap page">
    <div class="page-head">
        <div>
            <div class="eyebrow">Shop account</div>
            <h1>{{ $vendor->name }}</h1>
            <p>Orders for your products. When a runner collects an order, take a photo as proof.</p>
        </div>
        <form method="POST" action="{{ route('shop.open') }}">@csrf @method('PATCH')
            <button class="btn {{ $vendor->is_open ? 'btn-ghost' : 'btn-primary' }}" type="submit">
                {{ $vendor->is_open ? '● Open · tap to close' : '○ Closed · tap to open' }}
            </button>
        </form>
    </div>

    <div class="kpis">
        <div class="kpi {{ $stats['active'] ? 'alert-kpi' : '' }}"><small>Active orders</small><b>{{ $stats['active'] }}</b><span>waiting or on the way</span></div>
        <div class="kpi"><small>Delivered</small><b>{{ $stats['delivered'] }}</b><span>orders completed</span></div>
        <div class="kpi"><small>Your sales</small><b>{{ $m($stats['sales']) }}</b><span>delivered items</span></div>
        <div class="kpi"><small>Products</small><b>{{ $stats['products'] }}</b><span><a href="{{ route('shop.products') }}" style="color:var(--orange);font-weight:600">Manage →</a></span></div>
    </div>

    <section class="panel">
        <div class="panel-head"><h2>Active orders</h2><a class="link-arrow" href="{{ route('history') }}">Full history →</a></div>
        @forelse($active as $o)
            <div class="vendor-card" style="align-items:flex-start;flex-wrap:wrap">
                <div class="grow">
                    <a class="strong" href="{{ route('orders.show', $o) }}" style="font-weight:700">{{ $o->order_number }}</a>
                    <span class="status {{ $o->status }}" style="margin-left:6px">{{ $o->status_label }}</span><br>
                    <small>{{ $o->items->where('vendor_name', $vendor->name)->map(fn ($i) => $i->quantity . '× ' . $i->name)->implode(', ') }}</small><br>
                    <small>Customer: {{ $o->user->name }} · Runner: {{ $o->runner?->name ?? 'not yet' }} · {{ $o->created_at->format('M j, g:i A') }}</small>
                </div>
                @if($o->runner_id && $o->photo('shop_handover'))
                    <span class="ok-badge">📸 Handover photo saved</span>
                @elseif($o->runner_id)
                    <form method="POST" action="{{ route('orders.photos.store', $o) }}" enctype="multipart/form-data" class="inline-form">
                        @csrf <input type="hidden" name="kind" value="shop_handover">
                        <div class="field"><input class="input" type="file" name="photo" accept="image/*" capture="environment" required></div>
                        <button class="btn btn-primary btn-sm" type="submit">📸 Runner collected it</button>
                    </form>
                @else
                    <small>Waiting for a runner…</small>
                @endif
            </div>
        @empty
            <div style="padding:30px;text-align:center;color:var(--muted)">No active orders right now.</div>
        @endforelse
    </section>
</main>
@endsection
