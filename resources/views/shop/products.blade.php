@extends('layouts.admin')
@section('title', 'My products')
@section('nav', 'products')

@section('content')
<main class="wrap page">
    <div class="page-head">
        <div><div class="eyebrow">{{ $vendor->name }}</div><h1>My products</h1><p>Add, change, hide or remove what you sell.</p></div>
    </div>

    <div class="two-col">
        <section class="panel">
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
                            <a class="btn btn-ghost btn-sm" href="{{ route('shop.products.edit', $p) }}">Edit</a>
                            <form method="POST" action="{{ route('shop.products.toggle', $p) }}">@csrf @method('PATCH')<button class="btn btn-ghost btn-sm" type="submit">{{ $p->is_available ? 'Hide' : 'Show' }}</button></form>
                            <form method="POST" action="{{ route('shop.products.destroy', $p) }}" onsubmit="return confirm('Remove {{ addslashes($p->name) }}?')">@csrf @method('DELETE')<button class="btn btn-danger btn-sm" type="submit">Remove</button></form>
                        </div></td>
                    </tr>
                @empty
                    <tr><td colspan="5" style="text-align:center;color:var(--muted);padding:30px">No products yet. Add your first one →</td></tr>
                @endforelse
                </tbody>
            </table></div>
        </section>

        <section class="panel"><div class="sec">
            <h2 class="sub">Add a product</h2>
            <form method="POST" action="{{ route('shop.products.store') }}">@csrf
                @php($product = null)
                @include('admin._product_fields')
                <button class="btn btn-primary btn-block" type="submit" style="margin-top:16px">Add product</button>
            </form>
        </div></section>
    </div>
</main>
@endsection
