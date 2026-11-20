@extends('layouts.admin')
@section('title', 'Users')
@section('nav', 'users')

@section('content')
<main class="wrap page">
    <div class="page-head"><div><div class="eyebrow">Admin</div><h1>Users</h1><p>Everyone with a UniFetch account.</p></div></div>

    <form class="toolbar" method="GET">
        <input class="input" type="search" name="q" value="{{ $q }}" placeholder="Search name, email or student ID">
        <select class="input" name="role">
            <option value="">All roles</option>
            @foreach(\App\Models\User::ROLES as $k => $label)<option value="{{ $k }}" @selected($role === $k)>{{ $label }}</option>@endforeach
        </select>
        <button class="btn btn-dark" type="submit">Filter</button>
    </form>

    <section class="panel"><div class="table-wrap"><table class="tbl">
        <thead><tr><th>Name</th><th>Role</th><th>Orders</th><th class="num">Wallet</th><th></th></tr></thead>
        <tbody>
        @forelse($users as $u)
            <tr>
                <td><a class="strong" href="{{ route('admin.users.show', $u) }}">{{ $u->name }}</a><small>{{ $u->student_id }} · {{ $u->email }}</small></td>
                <td><span class="role {{ $u->role }}">{{ \App\Models\User::ROLES[$u->role] ?? $u->role }}</span>@if($u->hasPendingRunnerApplication()) <span class="role pending">Applied</span>@endif</td>
                <td>{{ $u->orders_count }}</td>
                <td class="num">{{ \App\Support\Money::format($u->wallet_balance) }}</td>
                <td><div class="row-actions"><a class="btn btn-ghost btn-sm" href="{{ route('admin.users.show', $u) }}">View</a></div></td>
            </tr>
        @empty
            <tr><td colspan="5" style="text-align:center;color:var(--muted);padding:30px">Nobody matches.</td></tr>
        @endforelse
        </tbody>
    </table></div></section>
    {{ $users->links('pagination.unifetch') }}
</main>
@endsection
