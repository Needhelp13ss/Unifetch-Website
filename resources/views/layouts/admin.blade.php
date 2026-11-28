<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title') | UniFetch {{ Auth::user()->isShop() ? 'Shop' : 'Admin' }}</title>
    <link rel="icon" type="image/svg+xml" href="{{ asset('favicon.svg') }}">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/shop.css') }}">
    <link rel="stylesheet" href="{{ asset('css/admin.css') }}">
</head>
<body>
@php
    $pendingRunners = Auth::user()->isAdmin() ? \App\Models\User::where('role', 'customer')->where('runner_status', 'pending')->count() : 0;
    $k = trim($__env->yieldContent('nav'));
@endphp
<nav class="nav admin-nav">
    <div class="nav-inner">
        @if(Auth::user()->isShop())
        <a class="brand" href="{{ route('shop.dashboard') }}">
            <img src="{{ asset('favicon.svg') }}" alt=""><span>Uni<b>Fetch</b></span><em class="admin-tag">Shop</em>
        </a>
        <div class="menu">
            <a href="{{ route('shop.dashboard') }}" class="{{ $k === 'dash' ? 'active' : '' }}">Orders</a>
            <a href="{{ route('shop.products') }}" class="{{ $k === 'products' ? 'active' : '' }}">My products</a>
            <a href="{{ route('history') }}" class="{{ $k === 'history' ? 'active' : '' }}">History</a>
            <a href="{{ route('profile') }}">Profile</a>
        </div>
        @else
        <a class="brand" href="{{ route('admin.dashboard') }}">
            <img src="{{ asset('favicon.svg') }}" alt=""><span>Uni<b>Fetch</b></span><em class="admin-tag">Admin</em>
        </a>
        <div class="menu">
            <a href="{{ route('admin.dashboard') }}" class="{{ $k === 'dash' ? 'active' : '' }}">Overview</a>
            <a href="{{ route('admin.runners') }}" class="{{ $k === 'runners' ? 'active' : '' }}">Runners @if($pendingRunners)<span class="pill-count">{{ $pendingRunners }}</span>@endif</a>
            <a href="{{ route('admin.users') }}" class="{{ $k === 'users' ? 'active' : '' }}">Users</a>
            <a href="{{ route('admin.orders') }}" class="{{ $k === 'orders' ? 'active' : '' }}">Orders</a>
            <a href="{{ route('admin.menu') }}" class="{{ $k === 'menu' ? 'active' : '' }}">Menu</a>
            <a href="{{ route('admin.reports') }}" class="{{ $k === 'reports' ? 'active' : '' }}">Reports</a>
            <a href="{{ route('profile') }}">Profile</a>
        </div>
        @endif
        <div class="nav-right">
            <span class="user-name">{{ Auth::user()->name }}</span>
            <form method="POST" action="{{ route('logout') }}">@csrf <button class="logout" type="submit">Log Out</button></form>
        </div>
    </div>
</nav>

<div class="wrap">
    @if(session('success'))<div class="alert success" role="status">{{ session('success') }}</div>@endif
    @if(session('error'))<div class="alert error" role="alert">{{ session('error') }}</div>@endif
    @if($errors->any())<div class="alert error" role="alert">{{ $errors->first() }}</div>@endif
</div>

@yield('content')
</body>
</html>
