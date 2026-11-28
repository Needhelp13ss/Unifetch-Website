@extends('layouts.admin')
@section('title', 'Campus places')
@section('nav', 'menu')

@section('content')
<main class="wrap page">
    <div class="page-head">
        <div><div class="eyebrow">Admin</div><h1>Campus places</h1>
            <p>The delivery fee grows with the straight-line distance between two places. The coordinates here are placeholders: open each place in Google Maps, right-click it, copy the latitude and longitude, and paste them in.</p></div>
        <a class="link-arrow" href="{{ route('admin.menu') }}">← Menu</a>
    </div>

    <div class="two-col" style="grid-template-columns:1.6fr 1fr">
        <section class="panel">
            <div class="panel-head"><h2>Places ({{ $locations->count() }})</h2></div>
            @foreach($locations as $l)
                <div class="sec" style="border-top:1px solid var(--line)">
                    <form method="POST" action="{{ route('admin.locations.update', $l) }}" class="inline-form">@csrf @method('PUT')
                        <div class="field"><label class="lbl" for="n{{ $l->id }}">Name</label><input class="input" id="n{{ $l->id }}" name="name" value="{{ $l->name }}" maxlength="80" required></div>
                        <div class="field"><label class="lbl" for="la{{ $l->id }}">Latitude</label><input class="input" id="la{{ $l->id }}" name="latitude" value="{{ $l->latitude }}" required></div>
                        <div class="field"><label class="lbl" for="lo{{ $l->id }}">Longitude</label><input class="input" id="lo{{ $l->id }}" name="longitude" value="{{ $l->longitude }}" required></div>
                        <button class="btn btn-dark btn-sm" type="submit">Save</button>
                    </form>
                    <form method="POST" action="{{ route('admin.locations.destroy', $l) }}" onsubmit="return confirm('Remove {{ addslashes($l->name) }}?')" style="margin-top:8px;text-align:right">@csrf @method('DELETE')
                        <button class="btn btn-danger btn-sm" type="submit">Remove</button></form>
                </div>
            @endforeach
        </section>

        <section class="panel"><div class="sec">
            <h2 class="sub">Add a place</h2>
            <form method="POST" action="{{ route('admin.locations.store') }}">@csrf
                <div class="field"><label class="lbl" for="name">Name</label><input class="input" id="name" name="name" maxlength="80" required></div>
                <div class="field"><label class="lbl" for="latitude">Latitude</label><input class="input" id="latitude" name="latitude" placeholder="10.3300000" required></div>
                <div class="field"><label class="lbl" for="longitude">Longitude</label><input class="input" id="longitude" name="longitude" placeholder="123.9500000" required></div>
                <button class="btn btn-primary btn-block" type="submit">Add place</button>
            </form>
        </div></section>
    </div>
</main>
@endsection
