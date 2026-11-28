@extends('layouts.admin')
@section('title', 'Reports')
@section('nav', 'reports')

@section('content')
@php
    use App\Support\Charts;
    $m = fn ($n) => \App\Support\Money::format($n);
    $inactive = $total - $active;
@endphp
<main class="wrap page">
    <div class="page-head">
        <div><div class="eyebrow">Admin</div><h1>Reports</h1>
            <p>Charts of how UniFetch is used. “Active” means the person used the system in the last {{ \App\Http\Middleware\TrackActivity::ACTIVE_DAYS }} days.</p></div>
    </div>

    <div class="chart-grid">
        <section class="panel chart-card">
            <h2>Active vs inactive users</h2>
            {{ Charts::donut([['Active', $active, '#1FA187'], ['Inactive', $inactive, '#C9CED8']], (string) $total, 'users') }}
            <div class="role-table">
                @foreach($byRole as $r)
                    <div><b>{{ $r['label'] }}</b><span>{{ $r['active'] }} active · {{ $r['inactive'] }} inactive</span></div>
                @endforeach
            </div>
        </section>

        <section class="panel chart-card">
            <h2>Orders per day <small>last 14 days</small></h2>
            {{ Charts::bars($dayLabels, [['Orders', $perDay, '#FF6B35']], '', 'Orders per day') }}
        </section>

        <section class="panel chart-card">
            <h2>UniFetch profit per day <small>₱{{ \App\Support\Pricing::SERVICE_FEE }} service fee, delivered orders</small></h2>
            <div class="big-num">{{ $m($profitTotal) }} <small>earned in total</small></div>
            {{ Charts::line($dayLabels, $profitPerDay, '₱', 'Profit per day') }}
        </section>

        <section class="panel chart-card">
            <h2>Orders by status</h2>
            {{ Charts::donut($statuses, (string) array_sum(array_column($statuses, 1)), 'orders') }}
        </section>

        <section class="panel chart-card">
            <h2>Top shops by sales <small>delivered orders</small></h2>
            {{ Charts::hbars($topShops, '₱') }}
        </section>

        <section class="panel chart-card">
            <h2>Top runners <small>jobs delivered</small></h2>
            {{ Charts::hbars($topRunners, '', '#1A1A2E') }}
        </section>

        <section class="panel chart-card wide">
            <h2>New users per week <small>by role</small></h2>
            {{ Charts::bars($weekLabels, [['Customers', $newCustomers, '#FF6B35'], ['Runners', $newRunners, '#1A1A2E'], ['Shops', $newShops, '#1FA187']], '', 'New users per week') }}
        </section>
    </div>
</main>
@endsection
