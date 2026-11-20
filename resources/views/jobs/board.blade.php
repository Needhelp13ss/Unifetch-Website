@extends('layouts.shop')

@section('title', 'Runner Jobs')
@section('nav', 'jobs')

@section('content')
@php
    $money = fn ($n) => \App\Support\Money::format($n);
    /** one job card, used for open jobs and my jobs */
    $mode = null;
@endphp
<main class="wrap page">
    <div class="page-head">
        <div>
            <div class="eyebrow">Runner board</div>
            <h1>Runner Jobs</h1>
            <p>Take an order or errand, get it done, get paid. Only approved runners can see this page.</p>
        </div>
        <a class="link-arrow" href="{{ route('wallet') }}">Open wallet →</a>
    </div>

    <div class="stat-row">
        <div class="stat"><small>Delivered</small><b>{{ $deliveredCount }}</b></div>
        <div class="stat"><small>Earned from fees</small><b>{{ $money($earned) }}</b></div>
        <div class="stat"><small>Wallet</small><b>{{ $money($balance) }}</b></div>
        <div class="stat"><small>Reserved for cash jobs</small><b>{{ $money($committed) }}</b></div>
    </div>

    {{-- My active jobs --}}
    <div class="board-h"><h2>My active jobs</h2><span>{{ $mine->count() }} / {{ \App\Models\Order::MAX_ACTIVE_JOBS }}</span></div>
    @forelse($mine as $job)
        @include('jobs._job', ['job' => $job, 'mode' => 'mine'])
    @empty
        <div class="empty" style="padding:30px"><div class="big">🛵</div><p style="margin:4px 0 0">You have no active jobs. Take one from the list below.</p></div>
    @endforelse

    {{-- Open jobs --}}
    <div class="board-h"><h2>Waiting for a runner</h2><span>{{ $available->count() }} open</span></div>
    @forelse($available as $job)
        @include('jobs._job', ['job' => $job, 'mode' => 'open'])
    @empty
        <div class="empty" style="padding:30px"><div class="big">🎉</div><p style="margin:4px 0 0">No open jobs right now. Check again in a bit.</p></div>
    @endforelse

    {{-- Recently done --}}
    @if($done->isNotEmpty())
        <div class="board-h"><h2>Recently delivered</h2></div>
        <section class="panel">
            @foreach($done as $job)
                <div class="item-row">
                    <div class="mini">{{ $job->isErrand() ? '🏃' : ($job->isCustom() ? '📝' : '📦') }}</div>
                    <div class="grow"><b>{{ $job->order_number }}</b><small>{{ $job->summary }} · {{ $job->delivered_at?->format('M j, g:i A') }}</small></div>
                    <b style="color:var(--green)">+{{ $money($job->runnerEarning()) }}</b>
                </div>
            @endforeach
        </section>
    @endif
</main>
@endsection
