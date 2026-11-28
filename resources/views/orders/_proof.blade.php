{{-- Proof of delivery + photo proofs. $readonly = admin view (no upload forms). --}}
@php
    $viewer = $readonly ? null : $me;
    $photoRoute = fn ($p) => $readonly ? route('admin.orders.photo', [$order, $p]) : route('orders.photos.show', [$order, $p]);
    $fmt = fn ($d) => $d?->format('l, M j, Y · g:i A');
@endphp

@if($order->status === 'delivered')
<section class="panel pod" style="margin-top:24px">
    <div class="panel-head"><h2>✅ Proof of delivery</h2><button class="btn btn-ghost btn-sm" type="button" onclick="window.print()">Print</button></div>
    <div class="kv pod-grid">
        <div><small>Order</small><b>{{ $order->order_number }}</b></div>
        <div><small>Delivered on</small><b>{{ $fmt($order->delivered_at) }}</b></div>
        <div><small>Customer</small><b>{{ $order->user->name }}</b></div>
        <div><small>Runner</small><b>{{ $order->runner?->name ?? '—' }}</b></div>
        <div><small>Deliver to</small><b>{{ $order->delivery_location }}</b></div>
        <div><small>Total</small><b>{{ \App\Support\Money::format($order->total) }} · {{ $order->payment_label }}</b></div>
    </div>
</section>
@endif

<section class="panel" style="margin-top:24px">
    <div class="panel-head"><h2>📸 Photo proofs</h2></div>
    <div class="proof-grid">
        @foreach(\App\Models\OrderPhoto::KINDS as $kind => $label)
            @php($photo = $order->photo($kind))
            <div class="proof">
                <b>{{ $label }}</b>
                @if($photo)
                    <a href="{{ $photoRoute($photo) }}" target="_blank" rel="noopener"><img src="{{ $photoRoute($photo) }}" alt="{{ $label }}" loading="lazy"></a>
                    <small>By {{ $photo->user?->name ?? 'a removed user' }}<br>{{ $fmt($photo->created_at) }}</small>
                @else
                    <div class="proof-empty">No photo yet</div>
                @endif

                @if($viewer && $order->photoAllowedFor($viewer, $kind))
                    <form method="POST" action="{{ route('orders.photos.store', $order) }}" enctype="multipart/form-data">
                        @csrf <input type="hidden" name="kind" value="{{ $kind }}">
                        <input class="input" type="file" name="photo" accept="image/*" capture="environment" required>
                        <button class="btn btn-primary btn-sm" type="submit">{{ $photo ? 'Replace photo' : '📸 Upload photo' }}</button>
                    </form>
                @endif
            </div>
        @endforeach
    </div>
</section>
