@extends('layouts.shop')

@section('title', 'Food Ordering')
@section('nav', 'menu')
@section('band')<div class="band"></div>@endsection

@section('content')
<main class="wrap">

    {{-- Category bar + search --}}
    <form class="filterbar" method="GET" action="{{ route('menu') }}">
        <div class="pills">
            <a class="pill {{ !$activeCategory ? 'active' : '' }}" href="{{ route('menu', array_filter(['q' => $search])) }}">✨ All</a>
            @foreach($categories as $key => $label)
                <a class="pill {{ $activeCategory === $key ? 'active' : '' }}"
                   href="{{ route('menu', array_filter(['category' => $key, 'q' => $search])) }}">{{ $label }}</a>
            @endforeach
        </div>
        @if($activeCategory)<input type="hidden" name="category" value="{{ $activeCategory }}">@endif
        <label class="search">
            <svg class="icon"><use href="#i-search"/></svg>
            <input type="search" name="q" value="{{ $search }}" placeholder="Search food, shops…" aria-label="Search">
        </label>
    </form>

    <div class="section-head">
        <div>
            <div class="eyebrow">Popular around campus</div>
            <h2>What can we fetch for you?</h2>
        </div>
        <div class="count-note">{{ $products->count() }} {{ Str::plural('item', $products->count()) }}</div>
    </div>

    @if($products->isEmpty())
        <div class="empty">
            <div class="big">🔍</div>
            <h3>Nothing found</h3>
            <p>Try a different category or search word.</p>
            <a class="btn btn-dark" href="{{ route('menu') }}">See everything</a>
        </div>
    @else
        <div class="products" style="padding-bottom:70px">
            @foreach($products as $p)
                <article class="product">
                    <div class="thumb" style="background: linear-gradient(145deg, {{ $p->bg_color }}, #ffffff 140%)">
                        @if($p->image)
                            <img src="{{ asset($p->image) }}" alt="{{ $p->name }}" loading="lazy">
                        @else
                            <span class="emoji" aria-hidden="true">{{ $p->emoji }}</span>
                        @endif
                        <span class="tag">{{ $p->category_label }}</span>

                        <form class="add-form" method="POST" action="{{ route('cart.store') }}">
                            @csrf
                            <input type="hidden" name="product_id" value="{{ $p->id }}">
                            <button class="cart-btn" type="submit" aria-label="Add {{ $p->name }} to cart" title="Add to cart">
                                <svg class="icon"><use href="#i-cart"/></svg>
                            </button>
                        </form>
                    </div>
                    <div class="info">
                        <div class="vendor">{{ $p->vendor->name }}</div>
                        <h3>{{ $p->name }}</h3>
                        <p class="desc">{{ $p->description }}</p>
                        <div class="meta">
                            <span class="price">{{ \App\Support\Money::format($p->price) }}</span>
                            <span class="time"><svg class="icon"><use href="#i-clock"/></svg> {{ $p->prep_time }}</span>
                        </div>
                    </div>
                </article>
            @endforeach
        </div>
    @endif

    <section class="promo">
        <div>
            <div class="eyebrow">More than delivery</div>
            <h3>Want something that isn't listed?</h3>
            <p>Make a custom order. Tell us what you want and where to get it, and a runner will buy it and bring it to you.</p>
        </div>
        <a class="btn btn-primary" href="{{ route('custom.create') }}">Request a custom order →</a>
    </section>
</main>
@endsection

@push('scripts')
<script>
    // Add to cart without reloading the page
    document.querySelectorAll('.add-form').forEach(function (form) {
        form.addEventListener('submit', function (e) {
            e.preventDefault();
            var btn = form.querySelector('.cart-btn');
            btn.disabled = true;
            UF.send(form.action, 'POST', { product_id: form.product_id.value }).then(function (res) {
                if (res.ok) {
                    UF.setCount(res.count);
                    UF.toast('🛒 ' + res.message);
                    btn.classList.add('done');
                    btn.innerHTML = '<svg class="icon"><use href="#i-check"/></svg>';
                    setTimeout(function () {
                        btn.classList.remove('done');
                        btn.innerHTML = '<svg class="icon"><use href="#i-cart"/></svg>';
                    }, 1200);
                } else {
                    UF.toast(res.message || 'Could not add that item.', true);
                }
            }).catch(function () {
                form.submit(); // fall back to a normal page reload
            }).finally(function () { btn.disabled = false; });
        });
    });
</script>
@endpush
