@php($money = fn ($n) => \App\Support\Money::format($n))
<article class="job">
    <div>
        <h3>
            {{ $job->isRequest() ? $job->errand_title : $job->items->count() . ' ' . \Illuminate\Support\Str::plural('item', $job->items->sum('quantity')) . ' · ' . $job->items->pluck('vendor_name')->unique()->implode(', ') }}
            @if($job->isRequest())<span class="type-chip">{{ $job->type_label }}</span>@endif
            @if($job->isErrand())<span class="type-chip" style="background:#e1edff;color:#1c5bc8">{{ $job->errand_category }}</span>@endif
        </h3>
        <div class="what">
            @if($job->isRequest())
                <span style="white-space:pre-line">{{ $job->errand_details }}</span>
            @else
                {{ $job->summary }}
            @endif
        </div>
        <div class="meta-line">
            @if($job->pickup_location)<span>{{ $job->isErrand() ? '🧭 Go to' : '📥 Get from' }}: <b>{{ $job->pickup_location }}</b></span>@endif
            <span>📍 {{ $job->isErrand() ? 'Meet / bring back to' : 'Deliver to' }}: <b>{{ $job->delivery_location }}</b></span>
            @if($job->budget !== null)<span>💰 {{ $job->isErrand() ? 'Money you may need to pay' : 'Item budget' }}: <b>{{ $money($job->budget) }}</b> (customer pays you back)</span>@endif
            <span>👤 {{ $job->user->name }}@if($mode === 'mine' && $job->user->phone) · {{ $job->user->phone }}@endif</span>
            @if($job->notes)<span>🗒️ {{ $job->notes }}</span>@endif
            <span><span class="pay-tag {{ $job->payment_method }}">{{ $job->payment_method === 'cod' ? 'Customer pays cash' : 'Paid via wallet' }}</span></span>
        </div>
    </div>

    <div class="fee">
        <b>+{{ $money($job->runnerEarning()) }}</b>
        <small>your fee</small>
        @if($job->cashToRemit() > 0)
            <small style="display:block;margin-top:6px;color:#96650a">Collect {{ $money($job->total) }} cash,<br>hand over {{ $money($job->cashToRemit()) }} via wallet</small>
        @endif
    </div>

    <div class="actions">
        @if($mode === 'open')
            <form method="POST" action="{{ route('jobs.accept', $job) }}">@csrf
                <button class="btn btn-primary" type="submit">Take this job</button></form>
            @if($job->cashToRemit() > 0)<span class="warn">Cash job: your wallet must cover {{ $money($job->cashToRemit()) }}.</span>@endif
            <span style="color:var(--muted);font-size:.85rem;margin-left:auto">Posted {{ $job->created_at->diffForHumans() }}</span>
        @else
            @if($job->status === 'preparing')
                <form method="POST" action="{{ route('jobs.start', $job) }}">@csrf <button class="btn btn-primary" type="submit">I'm on my way</button></form>
                <form method="POST" action="{{ route('jobs.release', $job) }}" onsubmit="return confirm('Give this job back so another runner can take it?')">@csrf <button class="btn btn-ghost" type="submit">Give back</button></form>
            @else
                <form method="POST" action="{{ route('jobs.complete', $job) }}" onsubmit="return confirm('Mark this job as done?')">@csrf <button class="btn btn-primary" type="submit">{{ $job->isErrand() ? 'Mark done ✓' : 'Mark delivered ✓' }}</button></form>
            @endif
            <span class="status {{ $job->status }}" style="margin-left:auto">{{ $job->status_label }}</span>
        @endif
    </div>
</article>
