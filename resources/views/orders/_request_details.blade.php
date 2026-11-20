{{-- Details of a custom order or an errand (no menu items). Used by the customer and admin pages. --}}
@php($errand = $order->isErrand())
<div class="kv">
    @if($errand)<div><small>Kind of errand</small><b>{{ $order->errand_category }}</b></div>@endif
    <div><small>{{ $errand ? 'What you need done' : 'What you want' }}</small><b>{{ $order->errand_title }}</b></div>
    <div><small>Details</small><b style="white-space:pre-line">{{ $order->errand_details }}</b></div>
    @if($order->pickup_location)<div><small>{{ $errand ? 'Where the runner goes' : 'Get it from' }}</small><b>{{ $order->pickup_location }}</b></div>@endif
    @if($order->budget !== null)<div><small>{{ $errand ? 'Money to pay on your behalf' : 'Item budget' }}</small><b>{{ \App\Support\Money::format($order->budget) }} <span style="font-weight:400;color:var(--muted)">(settled directly with the runner)</span></b></div>@endif
</div>
<div style="padding:6px 22px 18px"><div class="sum-line total"><span>Runner fee</span><span>{{ \App\Support\Money::format($order->total) }}</span></div></div>
