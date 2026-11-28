@extends('layouts.admin')
@section('title', $vendor->name)
@section('nav', 'menu')

@section('content')
<main class="wrap page">
    <div class="page-head">
        <div><div class="eyebrow">Vendor</div><h1>{{ $vendor->name }}</h1><p>{{ $vendor->tagline }}</p></div>
        <a class="link-arrow" href="{{ route('admin.menu') }}">← All vendors</a>
    </div>

    <section class="panel" style="margin-bottom:24px"><div class="sec">
        <h2 class="sub">Vendor details</h2>
        <form method="POST" action="{{ route('admin.vendors.update', $vendor) }}">@csrf @method('PUT')
            <div class="grid-form">
                <div class="field"><label class="lbl" for="name">Name</label><input class="input" id="name" name="name" value="{{ old('name', $vendor->name) }}" maxlength="100" required></div>
                <div class="field"><label class="lbl" for="tagline">Tagline</label><input class="input" id="tagline" name="tagline" value="{{ old('tagline', $vendor->tagline) }}" maxlength="160"></div>
                <div class="field"><label class="lbl" for="location">Location (text)</label><input class="input" id="location" name="location" value="{{ old('location', $vendor->location) }}" maxlength="120"></div>
                <div class="field"><label class="lbl" for="campus_location_id">Campus place (for distance fee)</label>
                    <select class="input" id="campus_location_id" name="campus_location_id"><option value="">Not set</option>
                        @foreach($places as $pl)<option value="{{ $pl->id }}" @selected(old('campus_location_id', $vendor->campus_location_id) == $pl->id)>{{ $pl->name }}</option>@endforeach
                    </select></div>
            </div>
            <div style="display:flex;align-items:center;justify-content:space-between;gap:12px;margin-top:16px;flex-wrap:wrap">
                <label class="check-line"><input type="checkbox" name="is_open" value="1" {{ $vendor->is_open ? 'checked' : '' }}> Open (shown on the menu)</label>
                <button class="btn btn-dark btn-sm" type="submit">Save vendor</button>
            </div>
        </form>
    </div></section>

    <section class="panel" style="margin-bottom:24px"><div class="sec">
        <h2 class="sub">Shop account (the seller's login)</h2>
        @if($owner)<p class="mutedp">Logs in as <b>{{ $owner->email }}</b>. Leave the password empty to keep it.</p>@else<p class="mutedp">No login yet. Create one so the seller can manage this shop and upload handover photos.</p>@endif
        <form method="POST" action="{{ route('admin.vendors.account', $vendor) }}">@csrf
            <div class="grid-form">
                <div class="field"><label class="lbl" for="a_name">Seller name</label><input class="input" id="a_name" name="name" value="{{ old('name', $owner?->name) }}" maxlength="100" {{ $owner ? '' : 'required' }}></div>
                <div class="field"><label class="lbl" for="a_email">Email</label><input class="input" id="a_email" name="email" type="email" value="{{ old('email', $owner?->email) }}" maxlength="255" {{ $owner ? '' : 'required' }}></div>
                <div class="field"><label class="lbl" for="a_pw">{{ $owner ? 'New password' : 'Password' }}</label><input class="input" id="a_pw" name="password" type="password" minlength="8" autocomplete="new-password" {{ $owner ? '' : 'required' }}></div>
            </div>
            <div style="margin-top:16px;text-align:right"><button class="btn btn-dark btn-sm" type="submit">{{ $owner ? 'Save account' : 'Create shop account' }}</button></div>
        </form>
    </div></section>

    <section class="panel" style="margin-bottom:24px">
        <div class="panel-head"><h2>Products ({{ $products->count() }})</h2></div>
        <div class="table-wrap"><table class="tbl">
            <thead><tr><th>Product</th><th>Category</th><th class="num">Price</th><th>Status</th><th></th></tr></thead>
            <tbody>
            @forelse($products as $p)
                <tr>
                    <td><b>{{ $p->emoji }} {{ $p->name }}</b><small>{{ $p->description }}</small></td>
                    <td>{{ $p->category_label }}</td>
                    <td class="num">{{ \App\Support\Money::format($p->price) }}</td>
                    <td><span class="role {{ $p->is_available ? '' : 'pending' }}">{{ $p->is_available ? 'Available' : 'Hidden' }}</span></td>
                    <td><div class="row-actions">
                        <a class="btn btn-ghost btn-sm" href="{{ route('admin.products.edit', $p) }}">Edit</a>
                        <form method="POST" action="{{ route('admin.products.toggle', $p) }}">@csrf @method('PATCH')<button class="btn btn-ghost btn-sm" type="submit">{{ $p->is_available ? 'Hide' : 'Show' }}</button></form>
                        <form method="POST" action="{{ route('admin.products.destroy', $p) }}" onsubmit="return confirm('Remove {{ addslashes($p->name) }}?')">@csrf @method('DELETE')<button class="btn btn-danger btn-sm" type="submit">Remove</button></form>
                    </div></td>
                </tr>
            @empty
                <tr><td colspan="5" style="text-align:center;color:var(--muted);padding:30px">No products yet. Add one below.</td></tr>
            @endforelse
            </tbody>
        </table></div>
    </section>

    <section class="panel" style="margin-bottom:24px"><div class="sec">
        <h2 class="sub">Add a product</h2>
        <form method="POST" action="{{ route('admin.products.store', $vendor) }}">@csrf
            @include('admin._product_fields', ['product' => null])
            <button class="btn btn-primary" type="submit" style="margin-top:16px">Add product</button>
        </form>
    </div></section>

    <section class="panel"><div class="sec">
        <h2 class="sub">Remove this vendor</h2>
        <p class="mutedp">This also removes all of its products.</p>
        <form method="POST" action="{{ route('admin.vendors.destroy', $vendor) }}" onsubmit="return confirm('Remove {{ addslashes($vendor->name) }} and all its products?')">@csrf @method('DELETE')<button class="btn btn-danger" type="submit">Remove vendor</button></form>
    </div></section>
</main>
@endsection
