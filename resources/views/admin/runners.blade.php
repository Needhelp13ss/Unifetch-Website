@extends('layouts.admin')
@section('title', 'Runners')
@section('nav', 'runners')

@section('content')
<main class="wrap page">
    <div class="page-head">
        <div><div class="eyebrow">Admin</div><h1>Runners</h1><p>Check each applicant's school ID, then approve who can run. Only approved runners can see Runner Jobs.</p></div>
    </div>

    <section class="panel" style="margin-bottom:24px">
        <div class="panel-head"><h2>Waiting for approval ({{ $pending->count() }})</h2></div>
        @forelse($pending as $u)
            <div class="item-row">
                <div class="grow"><b>{{ $u->name }}</b><small>{{ $u->student_id }} · {{ $u->email }} · applied {{ $u->runner_applied_at?->diffForHumans() }}</small>
                    <div class="verify">
                        <span>🪪 Portal ID: <b>{{ $u->portal_id ?? '—' }}</b></span>
                        <span>🎓 {{ $u->course ?? '—' }} · {{ $u->year_level ?? '—' }}</span>
                        <span>{!! $u->enrolled_confirmed ? '<span class="ok">✓ Says they are a currently enrolled UCLM student</span>' : '<span style="color:var(--red)">Did not confirm enrolment</span>' !!}</span>
                        @if($u->id_photo_path)<a class="id-link" href="{{ route('admin.runners.id', $u) }}" target="_blank" rel="noopener">View school ID photo ↗</a>@else<span style="color:var(--red)">No ID photo uploaded</span>@endif
                    </div></div>
                <div class="row-actions">
                    <form method="POST" action="{{ route('admin.runners.approve', $u) }}">@csrf @method('PATCH')<button class="btn btn-ok btn-sm" type="submit">Approve</button></form>
                    <form method="POST" action="{{ route('admin.runners.reject', $u) }}">@csrf @method('PATCH')<button class="btn btn-danger btn-sm" type="submit">Reject</button></form>
                </div>
            </div>
        @empty
            <div style="padding:26px;color:var(--muted)">No applications right now. 🎉</div>
        @endforelse
    </section>

    <section class="panel" style="margin-bottom:24px">
        <div class="panel-head"><h2>Approved runners ({{ $runners->count() }})</h2></div>
        <div class="table-wrap"><table class="tbl">
            <thead><tr><th>Runner</th><th>Active</th><th>Delivered</th><th class="num">Wallet</th><th></th></tr></thead>
            <tbody>
            @forelse($runners as $r)
                <tr>
                    <td><a class="strong" href="{{ route('admin.users.show', $r) }}">{{ $r->name }}</a><small>{{ $r->student_id }} · {{ $r->email }}</small>
                        <small>Portal ID: {{ $r->portal_id ?? '—' }} · {{ $r->course ?? '—' }}, {{ $r->year_level ?? '—' }}@if($r->id_photo_path) · <a class="id-link" style="margin:0" href="{{ route('admin.runners.id', $r) }}" target="_blank" rel="noopener">ID photo ↗</a>@endif</small></td>
                    <td>{{ $r->active_count }}</td><td>{{ $r->delivered_count }}</td>
                    <td class="num" style="color:{{ $r->wallet_balance < 0 ? 'var(--red)' : 'inherit' }}">{{ \App\Support\Money::format($r->wallet_balance) }}</td>
                    <td><div class="row-actions"><form method="POST" action="{{ route('admin.runners.revoke', $r) }}" onsubmit="return confirm('Remove runner access for {{ addslashes($r->name) }}?')">@csrf @method('PATCH')<button class="btn btn-danger btn-sm" type="submit">Remove runner</button></form></div></td>
                </tr>
            @empty
                <tr><td colspan="5" style="text-align:center;color:var(--muted);padding:30px">No approved runners yet.</td></tr>
            @endforelse
            </tbody>
        </table></div>
    </section>

    @if($rejected->isNotEmpty())
    <section class="panel">
        <div class="panel-head"><h2>Rejected / removed ({{ $rejected->count() }})</h2></div>
        @foreach($rejected as $u)
            <div class="item-row">
                <div class="grow"><b>{{ $u->name }}</b><small>{{ $u->student_id }} · {{ $u->email }}</small>
                    <div class="verify">
                        <span>🪪 Portal ID: <b>{{ $u->portal_id ?? '—' }}</b></span>
                        <span>🎓 {{ $u->course ?? '—' }} · {{ $u->year_level ?? '—' }}</span>
                        <span>{!! $u->enrolled_confirmed ? '<span class="ok">✓ Says they are a currently enrolled UCLM student</span>' : '<span style="color:var(--red)">Did not confirm enrolment</span>' !!}</span>
                        @if($u->id_photo_path)<a class="id-link" href="{{ route('admin.runners.id', $u) }}" target="_blank" rel="noopener">View school ID photo ↗</a>@else<span style="color:var(--red)">No ID photo uploaded</span>@endif
                    </div></div>
                <form method="POST" action="{{ route('admin.runners.approve', $u) }}">@csrf @method('PATCH')<button class="btn btn-ghost btn-sm" type="submit">Approve anyway</button></form>
            </div>
        @endforeach
    </section>
    @endif
</main>
@endsection
