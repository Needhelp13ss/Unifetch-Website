@extends('layouts.shop')

@section('title', 'Dashboard')
@section('nav', 'home')

@section('content')
@php
    $u = Auth::user();
    $openJobs = $u->isRunner()
        ? \App\Models\Order::where('status', 'pending')->whereNull('runner_id')->where('user_id', '!=', $u->id)->count()
        : 0;
@endphp
<main class="wrap page">
    <section class="welcome">
        <div class="eyebrow">UniFetch Dashboard</div>
        <h1>Welcome, {{ $u->name }}! 👋</h1>
        <p>@if($u->isRunner()) You're an approved runner. Pick up jobs and earn on every delivery. @else Choose a service below to start using UniFetch. @endif</p>
    </section>

    <section class="cards">
        <a class="card" href="{{ route('menu') }}"><div class="ico">🍔</div><h3>Ordering</h3><p>Order what you need, or request something that isn't on the menu.</p></a>
        <a class="card" href="{{ route('errand.create') }}"><div class="ico">🏃</div><h3>Errand Services</h3><p>Need a parcel picked up or someone to wait in line? Ask a runner.</p></a>

        @if($u->isRunner())
            <a class="card" href="{{ route('jobs.board') }}">
                <div class="ico">🛵</div><h3>Runner Jobs</h3>
                <p>{{ $openJobs }} {{ \Illuminate\Support\Str::plural('job', $openJobs) }} waiting for a runner.</p>
            </a>
        @endif

        <a class="card" href="{{ route('orders') }}"><div class="ico">📦</div><h3>My Orders</h3><p>Track your current and past orders.</p></a>
        <a class="card" href="{{ route('history') }}"><div class="ico">🧾</div><h3>History</h3><p>Old and new transactions with proof of delivery.</p></a>
        <a class="card" href="{{ route('wallet') }}"><div class="ico">👛</div><h3>Wallet</h3><p>Balance: <b>{{ \App\Support\Money::format($u->wallet_balance ?? 0) }}</b>. Add money anytime.</p></a>
        <a class="card" href="{{ route('profile') }}"><div class="ico">👤</div><h3>My Profile</h3><p>View your UniFetch account details.</p></a>
    </section>

    @unless($u->isRunner())
        <section class="panel runner-cta">
            @if($u->hasPendingRunnerApplication())
                <div class="ico">⏳</div>
                <div class="grow"><h3>Runner application pending</h3><p>The admin is checking your school ID and reviewing your application. You'll get access to the runner board as soon as you're approved.</p></div>
            @else
                <div class="ico">🛵</div>
                <div class="grow">
                    <h3>{{ $u->runner_status === 'rejected' ? 'Want to try becoming a runner again?' : 'Want to earn as a runner?' }}</h3>
                    <p>Runners pick up orders and errands around campus and keep the delivery fee. You'll need your portal ID and a photo of your school ID, and the admin approves every runner.</p>
                </div>
                <a class="btn btn-dark" href="{{ route('runner.apply.form') }}">Apply to be a runner</a>
            @endif
        </section>
    @endunless

    <section class="panel" style="padding:22px;margin-top:22px">
        <h2 style="margin:0 0 8px;font-size:1.15rem">Account Information</h2>
        <p style="margin:6px 0;color:var(--muted)"><strong style="color:var(--text)">Student ID:</strong> {{ $u->student_id }}</p>
        <p style="margin:6px 0;color:var(--muted)"><strong style="color:var(--text)">Email:</strong> {{ $u->email }}</p>
        <p style="margin:6px 0;color:var(--muted)"><strong style="color:var(--text)">Account:</strong> {{ \App\Models\User::ROLES[$u->role] ?? $u->role }}</p>
    </section>
</main>
@endsection
