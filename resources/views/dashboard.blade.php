<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard | UniFetch</title>
    <link rel="icon" type="image/svg+xml" href="{{ asset('favicon.svg') }}">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        :root{--navy:#1A1A2E;--orange:#FF6B35;--bg:#F4F5F7;--text:#1A1A2E;--muted:#7f8698;--line:#e6e8ed}
        *{box-sizing:border-box}body{margin:0;font-family:Outfit,sans-serif;background:var(--bg);color:var(--text)}
        a{text-decoration:none;color:inherit}.nav{background:var(--navy);color:#fff;position:sticky;top:0;z-index:10}
        .nav-inner{width:min(1140px,calc(100% - 40px));margin:auto;min-height:68px;display:flex;align-items:center;justify-content:space-between;gap:20px}
        .brand{display:flex;align-items:center;gap:10px;font-size:1.25rem;font-weight:800}.brand img{width:36px;height:36px}.brand b{color:var(--orange)}
        .menu{display:flex;align-items:center;gap:4px}.menu a{padding:9px 12px;border-radius:9px;color:#cbd0df;font-size:.86rem}.menu a:hover,.menu a.active{color:#fff;background:rgba(255,255,255,.09)}
        .user-menu{display:flex;align-items:center;gap:10px}.user-name{font-size:.85rem;color:#d9dbea}.logout{border:1px solid rgba(255,255,255,.15);background:transparent;color:#fff;padding:8px 12px;border-radius:9px;cursor:pointer;font:inherit}
        .logout:hover{background:rgba(255,255,255,.1)}
        main{width:min(1140px,calc(100% - 40px));margin:40px auto}.welcome{background:#fff;border:1px solid var(--line);border-radius:22px;padding:32px;box-shadow:0 10px 30px rgba(18,39,95,.06)}
        .eyebrow{color:var(--orange);font-size:.78rem;font-weight:800;letter-spacing:.1em;text-transform:uppercase}.welcome h1{margin:8px 0 8px;font-size:2.2rem}.welcome p{color:var(--muted)}
        .cards{display:grid;grid-template-columns:repeat(4,1fr);gap:16px;margin-top:22px}.card{background:#fff;border:1px solid var(--line);border-radius:18px;padding:22px;transition:.2s}.card:hover{transform:translateY(-3px);box-shadow:0 12px 25px rgba(18,39,95,.08)}
        .icon{font-size:1.8rem}.card h3{margin:12px 0 5px}.card p{color:var(--muted);font-size:.9rem;margin:0}
        .profile{margin-top:22px;background:#fff;border:1px solid var(--line);border-radius:18px;padding:22px}.profile p{margin:7px 0;color:var(--muted)}.profile strong{color:var(--text)}
        .alert{margin-bottom:20px;padding:12px 15px;border-radius:10px;background:#eaf8f1;color:#176b45;border:1px solid #bce8d2}
        @media(max-width:850px){.menu{display:none}.cards{grid-template-columns:1fr 1fr}.user-name{display:none}}
        @media(max-width:520px){main,.nav-inner{width:min(100% - 28px,1140px)}.cards{grid-template-columns:1fr}.welcome{padding:24px}.welcome h1{font-size:1.8rem}}
    </style>
</head>
<body>
<nav class="nav">
    <div class="nav-inner">
        <a class="brand" href="{{ route('dashboard') }}">
            <img src="{{ asset('favicon.svg') }}" alt="">
            <b>UniFetch</b>
        </a>
        <div class="menu">
            <a class="active" href="{{ route('dashboard') }}">Home</a>
            <a href="#">Food Ordering</a>
            <a href="#">Errand Services</a>
            <a href="#">My Orders</a>
            <a href="{{ route('profile') }}">Profile</a>
        </div>
        <div class="user-menu">
            <span class="user-name">{{ Auth::user()->name }}</span>
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button class="logout" type="submit">Log Out</button>
            </form>
        </div>
    </div>
</nav>

<main>
    @if(session('success'))
        <div class="alert">{{ session('success') }}</div>
    @endif

    <section class="welcome">
        <div class="eyebrow">UniFetch Dashboard</div>
        <h1>Welcome, {{ Auth::user()->name }}! 👋</h1>
        <p>Choose a service below to start using UniFetch.</p>
    </section>

    <section class="cards">
        <a class="card" href="#"><div class="icon">🍔</div><h3>Food Ordering</h3><p>Browse food and place an order.</p></a>
        <a class="card" href="#"><div class="icon">🛵</div><h3>Errand Services</h3><p>Request an errand around campus.</p></a>
        <a class="card" href="#"><div class="icon">📦</div><h3>My Orders</h3><p>View your current and past orders.</p></a>
        <a class="card" href="{{ route('profile') }}"><div class="icon">👤</div><h3>My Profile</h3><p>View your UniFetch account details.</p></a>
    </section>

    <section class="profile">
        <h2>Account Information</h2>
        <p><strong>Student ID:</strong> {{ Auth::user()->student_id }}</p>
        <p><strong>Email:</strong> {{ Auth::user()->email }}</p>
    </section>
</main>
</body>
</html>
