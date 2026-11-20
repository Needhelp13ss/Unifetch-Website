@php
    $me = Auth::user();
    $navKey = $nav ?? '';
    $cartCount = (int) $me->cartItems()->sum('quantity');
@endphp
<nav class="nav">
    <div class="nav-inner">
        <a class="brand" href="{{ route('dashboard') }}">
            <img src="{{ asset('favicon.svg') }}" alt="">
            <span>Uni<b>Fetch</b></span>
        </a>

        <div class="menu">
            <a href="{{ route('dashboard') }}" class="{{ $navKey === 'home' ? 'active' : '' }}">Home</a>
            <a href="{{ route('menu') }}" class="{{ $navKey === 'menu' ? 'active' : '' }}">Food Ordering</a>
            <a href="{{ route('errand.create') }}" class="{{ $navKey === 'errand' ? 'active' : '' }}">Errand Services</a>
            @if($me->isRunner())
                {{-- the job board is only for approved runners --}}
                <a href="{{ route('jobs.board') }}" class="{{ $navKey === 'jobs' ? 'active' : '' }}">Runner Jobs</a>
            @endif
            <a href="{{ route('orders') }}" class="{{ $navKey === 'orders' ? 'active' : '' }}">My Orders</a>
            <a href="{{ route('wallet') }}" class="{{ $navKey === 'wallet' ? 'active' : '' }}">Wallet</a>
            <a href="{{ route('profile') }}">Profile</a>
        </div>

        <div class="nav-right">
            <a href="{{ route('wallet') }}" class="wallet-chip" title="Your wallet balance">
                <span aria-hidden="true">👛</span> {{ \App\Support\Money::format($me->wallet_balance ?? 0) }}
            </a>
            <a href="{{ route('cart') }}" class="cart-link" id="cartLink" aria-label="Open cart">
                <svg class="icon"><use href="#i-cart"/></svg>
                <span class="badge" id="cartBadge" data-count="{{ $cartCount }}">{{ $cartCount }}</span>
            </a>
            <span class="user-name">{{ $me->name }}</span>
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button class="logout" type="submit">Log Out</button>
            </form>
        </div>
    </div>
</nav>
