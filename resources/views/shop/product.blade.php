@extends('layouts.admin')
@section('title', 'Edit ' . $product->name)
@section('nav', 'products')

@section('content')
<main class="wrap page" style="max-width:820px">
    <div class="page-head">
        <div><div class="eyebrow">My products</div><h1>Edit product</h1></div>
        <a class="link-arrow" href="{{ route('shop.products') }}">← Back to products</a>
    </div>
    <section class="panel"><div class="sec">
        <form method="POST" action="{{ route('shop.products.update', $product) }}">@csrf @method('PUT')
            @include('admin._product_fields')
            <div style="display:flex;align-items:center;justify-content:space-between;gap:12px;margin-top:16px;flex-wrap:wrap">
                <label class="check-line"><input type="checkbox" name="is_available" value="1" {{ $product->is_available ? 'checked' : '' }}> Available on the menu</label>
                <button class="btn btn-primary" type="submit">Save product</button>
            </div>
        </form>
    </div></section>
</main>
@endsection
