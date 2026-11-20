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
                <div class="field"><label class="lbl" for="location">Location</label><input class="input" id="location" name="location" value="{{ old('location', $vendor->location) }}" maxlength="120"></div>
            </div>
            <div style="display:flex;align-items:center;justify-content:space-between;gap:12px;margin-top:16px;flex-wrap:wrap">
                <label class="check-line"><input type="checkbox" name="is_open" value="1" {{ $vendor->is_open ? 'checked' : '' }}> Open (shown on the menu)</label>
                <button class="btn btn-dark btn-sm" type="submit">Save vendor</button>
            </div>
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
