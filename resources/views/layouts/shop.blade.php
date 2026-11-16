<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title') | UniFetch</title>
    <link rel="icon" type="image/svg+xml" href="{{ asset('favicon.svg') }}">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/shop.css') }}">
</head>
<body>

{{-- Icon sprite --}}
<svg class="sprite" aria-hidden="true">
    <symbol id="i-cart" viewBox="0 0 24 24"><circle cx="9" cy="20" r="1.4"/><circle cx="18" cy="20" r="1.4"/><path d="M2 3h3l2.6 11.2a2 2 0 0 0 2 1.5h8.2a2 2 0 0 0 2-1.5L21 7H6"/></symbol>
    <symbol id="i-clock" viewBox="0 0 24 24"><circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 2"/></symbol>
    <symbol id="i-trash" viewBox="0 0 24 24"><path d="M4 7h16M10 11v6M14 11v6M6 7l1 13h10l1-13M9 7V4h6v3"/></symbol>
    <symbol id="i-check" viewBox="0 0 24 24"><path d="m5 12.5 4.5 4.5L19 7.5"/></symbol>
    <symbol id="i-search" viewBox="0 0 24 24"><circle cx="11" cy="11" r="7"/><path d="m20 20-3.5-3.5"/></symbol>
</svg>

@php($nav = trim($__env->yieldContent('nav')))
<nav class="nav">
    <div class="nav-inner">
        <a class="brand" href="{{ route('dashboard') }}">
            <img src="{{ asset('favicon.svg') }}" alt="">
            <span>Uni<b>Fetch</b></span>
        </a>

        <div class="menu">
            <a href="{{ route('dashboard') }}" class="{{ $nav === 'home' ? 'active' : '' }}">Home</a>
            <a href="{{ route('menu') }}" class="{{ $nav === 'menu' ? 'active' : '' }}">Food Ordering</a>
            <a href="#">Errand Services</a>
            <a href="{{ route('orders') }}" class="{{ $nav === 'orders' ? 'active' : '' }}">My Orders</a>
            <a href="{{ route('profile') }}">Profile</a>
        </div>

        <div class="nav-right">
            <a href="{{ route('cart') }}" class="cart-link" id="cartLink" aria-label="Open cart">
                <svg class="icon"><use href="#i-cart"/></svg>
                @php($cartCount = (int) Auth::user()->cartItems()->sum('quantity'))
                <span class="badge" id="cartBadge" data-count="{{ $cartCount }}">{{ $cartCount }}</span>
            </a>
            <span class="user-name">{{ Auth::user()->name }}</span>
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button class="logout" type="submit">Log Out</button>
            </form>
        </div>
    </div>
</nav>

@yield('band')

<div class="wrap">
    @if(session('success'))<div class="alert success" role="status">{{ session('success') }}</div>@endif
    @if(session('error'))<div class="alert error" role="alert">{{ session('error') }}</div>@endif
    @if($errors->any() && !View::hasSection('inline-errors'))
        <div class="alert error" role="alert">{{ $errors->first() }}</div>
    @endif
</div>

@yield('content')

<div class="toast" id="toast" role="status" aria-live="polite"></div>

<script>
    // Small helpers shared by the shop pages
    window.UF = {
        csrf: document.querySelector('meta[name="csrf-token"]').content,
        peso: function (n) {
            n = Number(n);
            return '₱' + n.toLocaleString('en-PH', {
                minimumFractionDigits: Number.isInteger(n) ? 0 : 2,
                maximumFractionDigits: 2
            });
        },
        send: function (url, method, data) {
            var body = new FormData();
            Object.keys(data || {}).forEach(function (k) { body.append(k, data[k]); });
            if (method !== 'POST') { body.append('_method', method); }
            return fetch(url, {
                method: 'POST',
                body: body,
                headers: { 'X-CSRF-TOKEN': UF.csrf, 'Accept': 'application/json' }
            }).then(function (r) { return r.json().then(function (j) { j._status = r.status; return j; }); });
        },
        setCount: function (n) {
            var b = document.getElementById('cartBadge');
            b.textContent = n; b.dataset.count = n;
            var l = document.getElementById('cartLink');
            l.classList.remove('bump'); void l.offsetWidth; l.classList.add('bump');
        },
        toast: function (msg, isError) {
            var t = document.getElementById('toast');
            t.textContent = msg; t.className = 'toast show' + (isError ? ' error' : '');
            clearTimeout(UF._t); UF._t = setTimeout(function () { t.classList.remove('show'); }, 2400);
        }
    };
</script>
@stack('scripts')
</body>
</html>
