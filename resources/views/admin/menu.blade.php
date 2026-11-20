@extends('layouts.admin')
@section('title', 'Menu')
@section('nav', 'menu')

@section('content')
<main class="wrap page">
    <div class="page-head"><div><div class="eyebrow">Admin</div><h1>Menu</h1><p>Add, change or remove vendors and their products.</p></div></div>

    <div class="two-col" style="grid-template-columns:1.4fr 1fr">
        <section class="panel">
            <div class="panel-head"><h2>Vendors ({{ $vendors->count() }})</h2></div>
            @forelse($vendors as $v)
                <a class="vendor-card" href="{{ route('admin.vendors.show', $v) }}">
                    <div class="grow"><b>{{ $v->name }}</b> @unless($v->is_open)<span class="role pending">Closed</span>@endunless<br><small>{{ $v->location ?: 'No location' }} · {{ $v->products_count }} products</small></div>
                    <span class="link-arrow">Manage →</span>
                </a>
            @empty
                <div style="padding:26px;color:var(--muted)">No vendors yet. Add the first one →</div>
            @endforelse
        </section>

        <section class="panel"><div class="sec">
            <h2 class="sub">Add a vendor</h2>
            <form method="POST" action="{{ route('admin.vendors.store') }}">@csrf
                <div class="field"><label class="lbl" for="name">Name</label><input class="input" id="name" name="name" maxlength="100" required></div>
                <div class="field"><label class="lbl" for="tagline">Tagline <span style="font-weight:400;color:var(--muted)">(optional)</span></label><input class="input" id="tagline" name="tagline" maxlength="160"></div>
                <div class="field"><label class="lbl" for="location">Location <span style="font-weight:400;color:var(--muted)">(optional)</span></label><input class="input" id="location" name="location" maxlength="120" placeholder="e.g. Canteen, Ground floor"></div>
                <button class="btn btn-primary btn-block" type="submit">Add vendor</button>
            </form>
        </div></section>
    </div>
</main>
@endsection
