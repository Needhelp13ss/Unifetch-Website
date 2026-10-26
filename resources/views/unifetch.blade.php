<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>UniFetch | Food Ordering with Errand Services at UCLM</title>
    <meta name="description" content="UniFetch is a food ordering web application with errand services for the University of Cebu Lapu-Lapu and Mandaue (UCLM).">
    <link rel="icon" type="image/svg+xml" href="{{ asset('favicon.svg') }}">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">

    <style>
        :root {
            --navy: #1A1A2E;
            --navy-deep: #12121F;
            --orange: #FF6B35;
            --orange-soft: #FFEFE8;
            --blue: #1F5FE0;
            --green: #1FA971;
            --teal: #2BB5A0;
            --bg: #F4F5F7;
            --card: #FFFFFF;
            --text: #1A1A2E;
            --muted: #8A90A2;
            --line: #E8EAEF;
            --radius: 24px;
            --shadow: 0 10px 30px rgba(18, 39, 95, .08);
        }

        * { box-sizing: border-box; margin: 0; padding: 0; }
        html { scroll-behavior: smooth; }
        body {
            font-family: 'Outfit', system-ui, -apple-system, 'Segoe UI', Roboto, sans-serif;
            background: var(--bg); color: var(--text); line-height: 1.6;
        }
        img { max-width: 100%; display: block; }
        a { color: inherit; text-decoration: none; }
        .container { width: min(1140px, 100% - 40px); margin-inline: auto; }

        /* ---------- Navigation ---------- */
        .nav {
            position: sticky;
            top: 0;
            z-index: 1000;
            background: rgba(26, 26, 46, .96);
            border-bottom: 1px solid rgba(255, 255, 255, .07);
            backdrop-filter: blur(16px);
            -webkit-backdrop-filter: blur(16px);
        }
        .nav .container {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 24px;
            padding: 12px 0;
        }
        .brand {
            display: flex;
            align-items: center;
            gap: 10px;
            flex-shrink: 0;
            color: #fff;
            font-weight: 700;
            font-size: 1.3rem;
        }
        .brand img {
            width: 38px;
            height: 38px;
            filter: drop-shadow(0 5px 10px rgba(255,107,53,.3));
        }
        .brand b { color: var(--orange); font-weight: 700; }
        .main-nav { display: flex; justify-content: center; min-width: 0; }
        .nav ul {
            list-style: none;
            display: flex;
            align-items: center;
            gap: 3px;
            background: rgba(255,255,255,.07);
            padding: 5px;
            border: 1px solid rgba(255,255,255,.05);
            border-radius: 999px;
        }
        .nav ul a {
            display: block;
            color: #c2c5d7;
            font-size: .78rem;
            font-weight: 500;
            padding: 9px 12px;
            border-radius: 999px;
            white-space: nowrap;
            transition: color .2s, background .2s;
        }
        .nav ul a:hover { color: #fff; background: rgba(255,255,255,.09); }
        .nav ul a.active {
            color: #fff;
            background: var(--orange);
            box-shadow: 0 4px 12px rgba(255,107,53,.2);
        }

        /* Authentication buttons */
        .auth-nav {
            display: flex;
            align-items: center;
            justify-content: flex-end;
            gap: 10px;
            flex-shrink: 0;
            font-family: 'Outfit', sans-serif;
        }
        .auth-link, .auth-button {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 7px;
            text-decoration: none;
            white-space: nowrap;
            font-size: .82rem;
            font-weight: 600;
            border-radius: 10px;
            transition: all .2s ease;
        }
        .auth-link {
            padding: 10px 14px;
            color: #f0eff8;
            border: 1px solid transparent;
        }
        .auth-link:hover {
            color: var(--orange);
            background: rgba(255,255,255,.06);
        }
        .auth-button {
            padding: 11px 16px;
            color: #fff;
            background: var(--orange);
            border: 1px solid var(--orange);
            box-shadow: 0 5px 15px rgba(255,107,53,.2);
        }
        .auth-button:hover {
            background: #f85b27;
            border-color: #f85b27;
            box-shadow: 0 7px 20px rgba(255,107,53,.3);
            transform: translateY(-1px);
        }
        .auth-arrow { font-size: 15px; line-height: 1; transition: transform .2s ease; }
        .auth-button:hover .auth-arrow { transform: translate(2px, -2px); }
        .auth-link:focus-visible, .auth-button:focus-visible {
            outline: 2px solid #ffb394;
            outline-offset: 3px;
        }
        section, .hero { scroll-margin-top: 80px; }

        @media (max-width: 1050px) {
            .nav .container { gap: 12px; }
            .nav ul a { padding: 8px 9px; font-size: .73rem; }
            .auth-nav { gap: 5px; }
            .auth-link { padding: 9px 10px; }
            .auth-button { padding: 10px 12px; }
        }

        /* ---------- Hero (navy header with wave, like the UniFetch UI) ---------- */
        .hero { position: relative; background: var(--navy); color: #fff; padding: 56px 0 120px; overflow: hidden; }
        .hero::before, .hero::after { content: ""; position: absolute; border-radius: 50%; background: rgba(255, 107, 53, .2); }
        .hero::before { width: 460px; height: 460px; right: -140px; top: -120px; }
        .hero::after { width: 260px; height: 260px; left: -110px; top: 90px; }
        .hero .container { position: relative; z-index: 1; display: grid; grid-template-columns: 1.05fr .95fr; gap: 48px; align-items: center; }
        .wave { position: absolute; left: 0; bottom: -1px; width: 100%; height: 90px; display: block; }
        .wave path { fill: var(--bg); }

        .pill {
            display: inline-flex; align-items: center; gap: 8px; padding: 7px 16px; border-radius: 999px;
            background: rgba(255, 255, 255, .08); color: #ffd9c2; font-size: .85rem; font-weight: 600; letter-spacing: .02em;
        }
        .pill i { width: 8px; height: 8px; border-radius: 50%; background: var(--orange); }
        .tile { width: 76px; height: 76px; border-radius: 22px; background: var(--orange); display: grid; place-items: center; margin: 22px 0 6px; box-shadow: 0 14px 30px rgba(255,107,53,.5); }
        .tile img { width: 48px; height: 48px; filter: none; }
        .sub { color: #9fb0d6; font-weight: 500; }
        .hero h1 { font-size: clamp(2.8rem, 6vw, 4.2rem); line-height: 1; font-weight: 800; letter-spacing: -.03em; margin: 8px 0 4px; }
        .hero h1 b { color: var(--orange); }
        .tagline { font-size: clamp(1.3rem, 2.6vw, 1.7rem); font-weight: 700; margin: 22px 0 8px; }
        .project-title { color: #c4cfee; max-width: 520px; font-weight: 300; font-size: 1.05rem; margin-bottom: 30px; }
        .hero-actions { display: flex; flex-wrap: wrap; gap: 14px; }
        .btn { display: inline-block; padding: 15px 32px; border-radius: 18px; font-weight: 600; font-size: 1rem; transition: transform .15s, background .2s; }
        .btn:hover { transform: translateY(-2px); }
        .btn-primary { background: var(--orange); color: #fff; box-shadow: 0 12px 26px rgba(255, 107, 53, .4); }
        .btn-primary:hover { background: #ff5a1f; }
        .btn-dark { background: var(--navy); color: #fff; border-radius: 14px; padding: 12px 22px; font-size: .95rem; }
        .btn-ghost { background: rgba(255, 255, 255, .08); color: #fff; border: 1.5px solid rgba(255, 255, 255, .3); }

        .hero-visual { position: relative; }
        .hero-image { border-radius: 40px; overflow: hidden; box-shadow: 0 28px 60px rgba(0, 0, 0, .35); aspect-ratio: 5 / 5; border: 6px solid rgba(255, 255, 255, .12); }
        .hero-image img { width: 100%; height: 100%; object-fit: cover; object-position: left top; }

        /* ---------- Shared section styles ---------- */
        section { padding: 72px 0; }
        .section-head { max-width: 680px; margin-bottom: 38px; }
        .eyebrow { display: inline-block; font-size: .8rem; font-weight: 700; letter-spacing: .12em; text-transform: uppercase; color: var(--orange); margin-bottom: 8px; }
        .section-head h2 { font-size: clamp(1.9rem, 4vw, 2.6rem); line-height: 1.15; letter-spacing: -.02em; color: var(--navy); }
        .section-head p { color: var(--muted); margin-top: 10px; font-size: 1.05rem; }

        /* ---------- Target users ---------- */
        .users { padding-top: 24px; }
        .user-grid { display: grid; grid-template-columns: repeat(5, 1fr); gap: 16px; }
        .user {
            background: #fff; border: 1px solid var(--line); box-shadow: var(--shadow); border-radius: var(--radius); padding: 26px 16px; text-align: center;
            transition: transform .2s;
        }
        .user:hover { transform: translateY(-5px); }
        .user .emoji { width: 56px; height: 56px; margin: 0 auto 12px; border-radius: 18px; background: var(--orange-soft); display: grid; place-items: center; font-size: 1.7rem; }
        .user h3 { font-size: 1.05rem; color: var(--navy); }
        .user p { font-size: .86rem; color: var(--muted); margin-top: 2px; line-height: 1.4; }
        .users-note { margin-top: 22px; color: var(--muted); }
        .verified { display: inline-flex; align-items: center; gap: 8px; margin-top: 12px; color: var(--teal); font-size: .88rem; font-weight: 600; }

        /* ---------- Feature cards ---------- */
        .features { background: var(--bg); padding-top: 40px; }
        .grid-4 { display: grid; grid-template-columns: repeat(4, 1fr); gap: 22px; }
        .card { background: #fff; border-radius: var(--radius); padding: 28px 24px; transition: transform .2s, box-shadow .2s; border: 1px solid var(--line); }
        .card:hover { transform: translateY(-6px); box-shadow: var(--shadow); }
        .icon { width: 60px; height: 60px; border-radius: 18px; background: var(--orange); display: grid; place-items: center; margin-bottom: 18px; box-shadow: 0 12px 24px rgba(255, 107, 53, .4); }
        .icon svg { width: 28px; height: 28px; stroke: #fff; fill: none; stroke-width: 2; stroke-linecap: round; stroke-linejoin: round; }
        .card h3 { font-size: 1.25rem; color: var(--navy); margin-bottom: 8px; }
        .card p { color: var(--muted); font-size: .98rem; }

        /* ---------- How it works ---------- */
        .how { background: #fff; }
        .steps { display: grid; grid-template-columns: repeat(4, 1fr); gap: 20px; counter-reset: step; }
        .step { background: #fff; box-shadow: var(--shadow); border: 1px solid var(--line); border-radius: var(--radius); padding: 24px; }
        .step::before {
            counter-increment: step; content: counter(step); display: grid; place-items: center; width: 40px; height: 40px;
            border-radius: 50%; background: var(--orange); color: #fff; font-weight: 700; margin-bottom: 14px; box-shadow: 0 6px 14px rgba(255, 107, 53, .35);
        }
        .step h4 { color: var(--navy); font-size: 1.15rem; margin-bottom: 4px; }
        .step p { color: var(--muted); font-size: .95rem; }

        /* ---------- About ---------- */
        .about .container { display: grid; grid-template-columns: .9fr 1.1fr; gap: 52px; align-items: center; }
        .about-img { border-radius: 36px; overflow: hidden; box-shadow: var(--shadow); aspect-ratio: 4 / 4.4; }
        .about-img img { width: 100%; height: 100%; object-fit: cover; }
        .about p { color: var(--muted); margin-top: 14px; font-size: 1.05rem; }
        .about ul { list-style: none; margin-top: 20px; display: grid; gap: 10px; }
        .about li { display: flex; gap: 12px; align-items: center; font-weight: 500; background: #fff; border-radius: 14px; padding: 12px 16px; box-shadow: var(--shadow); }
        .about li::before { content: "✓"; flex: none; width: 24px; height: 24px; border-radius: 50%; background: var(--orange); color: #fff; display: grid; place-items: center; font-size: .8rem; font-weight: 700; }

        /* ---------- Footer ---------- */
        footer { background: var(--navy); color: #c4cfee; padding: 44px 0; text-align: center; }
        footer .brand { justify-content: center; margin-bottom: 8px; }
        footer .verified { margin-top: 14px; }
        footer small { display: block; margin-top: 4px; font-size: .9rem; opacity: .8; }

        /* ---------- Responsive ---------- */
        @media (max-width: 1040px) {
            .user-grid { grid-template-columns: repeat(3, 1fr); }
        }
        @media (max-width: 960px) {
            .grid-4, .steps { grid-template-columns: repeat(2, 1fr); }
            .hero .container, .about .container { grid-template-columns: 1fr; }
            .hero-visual { max-width: 460px; margin-inline: auto; width: 100%; }
            .about-img { aspect-ratio: 16 / 11; order: -1; }
        }
        @media (max-width: 640px) {
            .nav .container { flex-direction: column; gap: 12px; padding: 12px 0; }
            .main-nav { width: 100%; justify-content: center; }
            .nav ul { max-width: 100%; overflow-x: auto; scrollbar-width: none; }
            .nav ul::-webkit-scrollbar { display: none; }
            .nav ul a { padding: 8px 11px; font-size: .78rem; white-space: nowrap; }
            .auth-nav { width: 100%; justify-content: center; gap: 10px; }
            .auth-link, .auth-button { padding: 10px 16px; font-size: .82rem; }
            .hero { padding: 40px 0 96px; }
            section { padding: 52px 0; }
            .user-grid, .grid-4, .steps { grid-template-columns: 1fr; }
            .user { display: flex; align-items: center; gap: 14px; text-align: left; padding: 16px; }
            .user .emoji { margin: 0; flex: none; }
            .btn { width: 100%; text-align: center; }
        }
    </style>
</head>
<body>

    {{-- Navigation --}}
    <header class="nav">
        <div class="container">
            <a href="#top" class="brand">
                <img src="{{ asset('images/logo.svg') }}" alt="UniFetch logo">
                <span>Uni<b>Fetch</b></span>
            </a>

            <nav class="main-nav" aria-label="Main navigation">
                <ul>
                    <li><a href="#users">Who It's For</a></li>
                    <li><a href="#features">Features</a></li>
                    <li><a href="#how">How It Works</a></li>
                    <li><a href="#about">About</a></li>
                </ul>
            </nav>

            {{-- Only show authentication links when the named routes exist.
                 This prevents RouteNotFoundException in projects where auth pages
                 have not been added yet. --}}
            @if (\Illuminate\Support\Facades\Route::has('login') && \Illuminate\Support\Facades\Route::has('register'))
                <div class="auth-nav">
                    <a href="{{ route('login') }}" class="auth-link">Sign In</a>
                    <a href="{{ route('register') }}" class="auth-button">
                        Create Account <span class="auth-arrow" aria-hidden="true">↗</span>
                    </a>
                </div>
            @endif
        </div>
    </header>

    <main id="top">

        {{-- Hero --}}
        <div class="hero">
            <div class="container">
                <div>
                    <span class="pill">👋 Welcome to the UCLM web platform</span>
                    <div class="tile"><img src="{{ asset('images/logo.svg') }}" alt=""></div>
                    <h1>Uni<b>Fetch</b></h1>
                    <p class="sub">Your campus, delivered.</p>
                    <p class="tagline">Your campus needs, just a fetch away!</p>
                    <p class="project-title">A Food Ordering Web Application with Errand Services in the University of Cebu Lapu-Lapu and Mandaue (UCLM)</p>
                    <div class="hero-actions">
                        <a href="#features" class="btn btn-primary">Explore Features</a>
                        <a href="#about" class="btn btn-ghost">About UniFetch</a>
                    </div>
                </div>
                <div class="hero-visual">
                    <div class="hero-image">
                        <img src="{{ asset('images/hero.jpg') }}" alt="Students using the UniFetch website on the UCLM campus">
                    </div>

                </div>
            </div>
            <svg class="wave" viewBox="0 0 1440 90" preserveAspectRatio="none" aria-hidden="true"><path d="M0,12 Q720,104 1440,12 L1440,90 L0,90 Z"/></svg>
        </div>

        {{-- Target users --}}
        <section class="users" id="users">
            <div class="container">
                <div class="section-head">
                    <span class="eyebrow">Target Users</span>
                    <h2>Who will use UniFetch</h2>
                </div>
                <div class="user-grid">
                    <div class="user"><div class="emoji">🎓</div><div><h3>Students</h3><p>Order food and request errands.</p></div></div>
                    <div class="user"><div class="emoji">👩‍🏫</div><div><h3>Faculty &amp; Staff</h3><p>Conveniently order meals and request services.</p></div></div>
                    <div class="user"><div class="emoji">🍳</div><div><h3>Food Vendors</h3><p>Manage menus and incoming orders.</p></div></div>
                    <div class="user"><div class="emoji">🏃</div><div><h3>Student Runners</h3><p>Accept and complete campus requests.</p></div></div>
                    <div class="user"><div class="emoji">⚙️</div><div><h3>Administrators</h3><p>Manage accounts and monitor activity.</p></div></div>
                </div>
                <p class="users-note">UniFetch is intended for UCLM students, faculty, and university staff who want food and simple errands handled during busy campus hours.</p>
                <span class="verified">⬡ Verified UCLM community only</span>
            </div>
        </section>

        {{-- Main features --}}
        <section class="features" id="features">
            <div class="container">
                <div class="section-head">
                    <span class="eyebrow">Core Capabilities</span>
                    <h2>One website for campus food and errands</h2>
                    <p>Everything a busy day on campus needs, in a single place.</p>
                </div>

                <div class="grid-4">
                    <article class="card">
                        <div class="icon">
                            <svg viewBox="0 0 24 24"><path d="M4 3v8a3 3 0 0 0 3 3v7M7 3v6M10 3v8a3 3 0 0 1-3 3"/><path d="M18 3c-2 1.5-3 4-3 7s1 4 3 4v7"/></svg>
                        </div>
                        <h3>Food Ordering</h3>
                        <p>Browse campus food vendors, view their menus, add items to your cart, and place your food order without leaving your seat.</p>
                    </article>

                    <article class="card">
                        <div class="icon">
                            <svg viewBox="0 0 24 24"><path d="M6 8h12l-1 12H7L6 8z"/><path d="M9 8V6a3 3 0 0 1 6 0v2"/></svg>
                        </div>
                        <h3>Errand Services</h3>
                        <p>Request minor errands such as purchasing school supplies or fetching documents from another part of the campus.</p>
                    </article>

                    <article class="card">
                        <div class="icon">
                            <svg viewBox="0 0 24 24"><circle cx="12" cy="8" r="4"/><path d="M4 21a8 8 0 0 1 16 0"/><path d="m9.5 8 1.8 1.8L15 6.2"/></svg>
                        </div>
                        <h3>Student Runners</h3>
                        <p>Verified student runners view available requests, accept the ones that fit their schedule, fulfill them on campus, and earn extra income.</p>
                    </article>

                    <article class="card">
                        <div class="icon">
                            <svg viewBox="0 0 24 24"><path d="M12 22s7-6.2 7-12a7 7 0 0 0-14 0c0 5.8 7 12 7 12z"/><circle cx="12" cy="10" r="2.5"/></svg>
                        </div>
                        <h3>Order Tracking</h3>
                        <p>Monitor your food orders and errand requests in real time, from vendor preparation to runner arrival and completion.</p>
                    </article>
                </div>
            </div>
        </section>

        {{-- How it works --}}
        <section class="how" id="how">
            <div class="container">
                <div class="section-head">
                    <span class="eyebrow">Basic System Workflow</span>
                    <h2>How UniFetch works</h2>
                </div>
                <div class="steps">
                    <div class="step">
                        <h4>Log in</h4>
                        <p>Users sign in with a verified UCLM ID or institutional email.</p>
                    </div>
                    <div class="step">
                        <h4>Choose a service</h4>
                        <p>Order food from campus vendors or submit an errand request.</p>
                    </div>
                    <div class="step">
                        <h4>A runner fulfills it</h4>
                        <p>A verified student runner accepts the task and delivers it on campus.</p>
                    </div>
                    <div class="step">
                        <h4>Track and receive</h4>
                        <p>Follow the status on the website, receive your order, and pay by Cash on Delivery or e-wallet.</p>
                    </div>
                </div>
            </div>
        </section>

        {{-- About --}}
        <section class="about" id="about">
            <div class="container">
                <div class="about-img">
                    <img src="{{ asset('images/campus.jpg') }}" alt="UCLM students enjoying food on campus">
                </div>
                <div>
                    <span class="eyebrow">About UniFetch</span>
                    <div class="section-head" style="margin-bottom:0">
                        <h2>A faster, smarter, more organized campus experience</h2>
                    </div>
                    <p>UniFetch is a proposed web-based system that combines food ordering and errand services for the University of Cebu Lapu-Lapu and Mandaue. It was created to help students, faculty, and staff save time during short breaks and busy schedules, while giving student runners and campus food vendors new opportunities to earn and reach more customers.</p>
                    <ul>
                        <li>Reduce time spent waiting in long canteen queues</li>
                        <li>Simplify minor campus errand requests</li>
                        <li>Improve order tracking and communication</li>
                        <li>Support student-led services within the university</li>
                    </ul>
                    <a href="#features" class="btn btn-dark" style="margin-top:22px">Explore Features →</a>
                </div>
            </div>
        </section>
    </main>

    <footer>
        <div class="container">
            <a href="#top" class="brand">
                <img src="{{ asset('images/logo.svg') }}" alt="UniFetch logo">
                <span>Uni<b>Fetch</b></span>
            </a>
            <p>A Food Ordering Web Application with Errand Services in UCLM</p>
            <small>College of Computer Studies &middot; University of Cebu Lapu-Lapu and Mandaue</small>
            <span class="verified">⬡ Verified UCLM campus website</span>
        </div>
    </footer>

    <script>
        // Highlight the nav link of the section currently on screen (and the one just clicked)
        (function () {
            var links = Array.prototype.slice.call(document.querySelectorAll('.nav ul a'));
            var items = links.map(function (a) { return { a: a, el: document.querySelector(a.getAttribute('href')) }; })
                             .filter(function (i) { return i.el; });

            function setActive(link) {
                links.forEach(function (a) { a.classList.toggle('active', a === link); });
            }

            function update() {
                var current = null;
                items.forEach(function (i) {
                    if (i.el.getBoundingClientRect().top <= 140) { current = i.a; }
                });
                if (window.innerHeight + window.scrollY >= document.documentElement.scrollHeight - 4) {
                    current = items[items.length - 1].a;
                }
                setActive(current);
            }

            links.forEach(function (a) { a.addEventListener('click', function () { setActive(a); }); });
            window.addEventListener('scroll', update, { passive: true });
            window.addEventListener('load', update);
            update();
        })();
    </script>
</body>
</html>