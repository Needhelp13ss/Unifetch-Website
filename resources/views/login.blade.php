
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login | UniFetch</title>

    <style>
        
    :root {
    --red: #ff6633;
    --red-dark: #ed5425;
    --navy: #1b1a2e;
    --text: #25243a;
    --muted: #77778d;
    --border: #e3e5ed;
    --background: #f5f5f8;
}


        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            min-height: 100vh;
            font-family: "Segoe UI", Tahoma, Geneva, Verdana, sans-serif;
            color: var(--text);
            background: var(--background);
        }

        a {
            text-decoration: none;
        }

        .page {
            display: grid;
            grid-template-columns: 1fr 1fr;
            min-height: 100vh;
        }

        /* Left branding panel */
        .brand-panel {
            position: relative;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            overflow: hidden;
            min-height: 100vh;
            padding: 38px 9%;
            color: white;
            background:
                linear-gradient(145deg, rgba(16, 29, 51, .91), rgba(23, 36, 59, .78)),
                url('{{ asset("images/image_6deb76.jpg") }}') center / cover no-repeat,
                var(--navy);
        }

        .brand-panel::after {
            content: "";
            position: absolute;
            width: 360px;
            height: 360px;
            right: -150px;
            bottom: -120px;
            border: 1px solid rgba(255,255,255,.13);
            border-radius: 50%;
            box-shadow:
                0 0 0 45px rgba(255,255,255,.035),
                0 0 0 90px rgba(255,255,255,.025);
            pointer-events: none;
        }

        .brand-logo {
            position: relative;
            z-index: 1;
            display: inline-flex;
            align-items: center;
            gap: 12px;
            width: fit-content;
            color: white;
            font-size: 25px;
            font-weight: 800;
            letter-spacing: -.8px;
        }

        .logo-mark {
            display: grid;
            place-items: center;
            width: 43px;
            height: 43px;
            color: white;
            background: var(--red);
            border-radius: 13px;
            font-size: 22px;
            box-shadow: 0 7px 18px rgba(230,57,70,.3);
        }

        .brand-content {
            position: relative;
            z-index: 1;
            max-width: 490px;
            margin: 60px 0;
        }

        .eyebrow {
            display: inline-block;
            margin-bottom: 22px;
            padding: 9px 13px;
            color: #ffdadd;
            background: rgba(230,57,70,.17);
            border: 1px solid rgba(255,255,255,.12);
            border-radius: 30px;
            font-size: 12px;
            font-weight: 700;
            letter-spacing: 1.2px;
            text-transform: uppercase;
        }

        .brand-content h1 {
            margin-bottom: 22px;
            font-size: clamp(36px, 4vw, 56px);
            line-height: 1.12;
            letter-spacing: -1.8px;
        }

        .brand-content h1 span {
            color: #ff737d;
        }

        .brand-content p {
            max-width: 420px;
            color: #d0d8e5;
            font-size: 16px;
            line-height: 1.9;
        }

        .brand-features {
            display: grid;
            gap: 17px;
            margin-top: 34px;
        }

        .feature {
            display: flex;
            align-items: center;
            gap: 13px;
            color: #f2f5fa;
            font-size: 14px;
        }

        .feature-icon {
            display: grid;
            place-items: center;
            flex-shrink: 0;
            width: 34px;
            height: 34px;
            color: #ff9ca3;
            background: rgba(255,255,255,.09);
            border-radius: 10px;
            font-size: 17px;
        }

        .brand-footer {
            position: relative;
            z-index: 1;
            color: #aeb9ca;
            font-size: 12px;
        }

        /* Right form panel */
        .form-panel {
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 45px 30px;
            background: var(--background);
        }

        .auth-card {
            width: 100%;
            max-width: 430px;
            padding: 42px;
            background: #fff;
            border: 1px solid rgba(226,232,240,.8);
            border-radius: 22px;
            box-shadow: 0 18px 55px rgba(23,36,59,.07);
        }

        .back-link {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            margin-bottom: 35px;
            color: var(--muted);
            font-size: 13px;
            font-weight: 600;
            transition: color .2s;
        }

        .back-link:hover {
            color: var(--red);
        }

        .welcome-label {
            margin-bottom: 10px;
            color: var(--red);
            font-size: 12px;
            font-weight: 800;
            letter-spacing: 1.5px;
            text-transform: uppercase;
        }

        .auth-card h2 {
            margin-bottom: 10px;
            color: var(--navy);
            font-size: 32px;
            font-weight: 800;
            letter-spacing: -.8px;
        }

        .subtitle {
            margin-bottom: 30px;
            color: var(--muted);
            font-size: 14px;
            line-height: 1.7;
        }

        .form-group {
            margin-bottom: 21px;
        }

        .form-group label {
            display: block;
            margin-bottom: 9px;
            color: #344054;
            font-size: 13px;
            font-weight: 700;
        }

        .form-group input {
            display: block;
            width: 100%;
            min-height: 49px;
            padding: 13px 15px;
            color: var(--text);
            background: #fbfcfe;
            border: 1px solid var(--border);
            border-radius: 10px;
            outline: none;
            font: inherit;
            font-size: 14px;
            transition: border-color .2s, box-shadow .2s, background .2s;
        }

        .form-group input::placeholder {
            color: #a0aaba;
        }

        .form-group input:hover {
            border-color: #c5ceda;
        }

        .form-group input:focus {
            background: #fff;
            border-color: var(--red);
            box-shadow: 0 0 0 4px rgba(230,57,70,.09);
        }

        .btn-submit {
            display: flex;
            align-items: center;
            justify-content: center;
            width: 100%;
            min-height: 51px;
            margin-top: 28px;
            padding: 14px 20px;
            color: white;
            background: var(--red);
            border: none;
            border-radius: 10px;
            cursor: pointer;
            font: inherit;
            font-size: 15px;
            font-weight: 700;
            box-shadow: 0 7px 16px rgba(230,57,70,.18);
            transition: background .2s, transform .2s, box-shadow .2s;
        }

        .btn-submit:hover {
            background: var(--red-dark);
            box-shadow: 0 9px 22px rgba(230,57,70,.25);
            transform: translateY(-1px);
        }

        .auth-links {
            margin-top: 26px;
            color: var(--muted);
            text-align: center;
            font-size: 13px;
            line-height: 1.8;
        }

        .auth-links a {
            color: var(--red);
            font-weight: 700;
        }

        .auth-links a:hover {
            color: var(--red-dark);
            text-decoration: underline;
        }

        .security-note {
            display: flex;
            justify-content: center;
            align-items: center;
            gap: 7px;
            margin-top: 27px;
            color: #8a96a8;
            font-size: 11px;
            text-align: center;
        }

        @media (max-width: 900px) {
            .page {
                grid-template-columns: 0.85fr 1.15fr;
            }

            .brand-panel {
                padding: 30px 8%;
            }

            .brand-content h1 {
                font-size: 38px;
            }

            .auth-card {
                padding: 32px;
            }
        }

        @media (max-width: 680px) {
            .page {
                display: block;
            }

            .brand-panel {
                min-height: auto;
                padding: 22px 24px;
            }

            .brand-content {
                margin: 30px 0 15px;
            }

            .brand-content h1 {
                margin-bottom: 10px;
                font-size: 29px;
                letter-spacing: -.7px;
            }

            .brand-content p,
            .brand-features,
            .brand-footer {
                display: none;
            }

            .eyebrow {
                margin-bottom: 12px;
                padding: 7px 10px;
                font-size: 10px;
            }

            .logo-mark {
                width: 37px;
                height: 37px;
                border-radius: 11px;
            }

            .brand-logo {
                font-size: 22px;
            }

            .form-panel {
                min-height: auto;
                padding: 25px 18px 35px;
            }

            .auth-card {
                max-width: 460px;
                padding: 28px 23px;
                border-radius: 18px;
            }

            .back-link {
                margin-bottom: 27px;
            }

            .auth-card h2 {
                font-size: 28px;
            }
        }

        @media (prefers-reduced-motion: reduce) {
            *, *::before, *::after {
                transition: none !important;
            }
        }
    </style>
</head>
<body>
    <main class="page">

        <section class="brand-panel">
            <a class="brand-logo" href="{{ route('home') }}" aria-label="UniFetch home">
                <span class="logo-mark">U</span>
                <span>UniFetch</span>
            </a>

            <div class="brand-content">
                <span class="eyebrow">Your campus, made easier</span>

                <h1>Campus life,<br><span>delivered.</span></h1>

                <p>
                    Your campus companion for food orders and everyday errands.
                    Spend less time waiting and more time doing what matters.
                </p>

                <div class="brand-features">
                    <div class="feature">
                        <span class="feature-icon">✓</span>
                        <span>Convenient campus food ordering</span>
                    </div>
                    <div class="feature">
                        <span class="feature-icon">↗</span>
                        <span>Helpful on-campus errand services</span>
                    </div>
                    <div class="feature">
                        <span class="feature-icon">⌂</span>
                        <span>Made for the UCLM community</span>
                    </div>
                </div>
            </div>

            <div class="brand-footer">
                © {{ date('Y') }} UniFetch · University of Cebu Lapu-Lapu and Mandaue
            </div>
        </section>

        <section class="form-panel">
            <div class="auth-card">

                <a class="back-link" href="{{ route('home') }}">
                    <span aria-hidden="true">←</span>
                    Back to UniFetch
                </a>

                <div class="welcome-label">Welcome back</div>
                <h2>Sign in to your account</h2>
                <p class="subtitle">
                    Enter your university credentials to continue to UniFetch.
                </p>

                <form method="POST" action="">
                    @csrf

                    <div class="form-group">
                        <label for="email">Institutional Email</label>
                        <input
                            type="email"
                            id="email"
                            name="email"
                            placeholder="student@uclm.edu.ph"
                            autocomplete="username"
                            required
                        >
                    </div>

                    <div class="form-group">
                        <label for="password">Password</label>
                        <input
                            type="password"
                            id="password"
                            name="password"
                            placeholder="Enter your password"
                            autocomplete="current-password"
                            required
                        >
                    </div>

                    <button type="submit" class="btn-submit">
                        Sign In <span aria-hidden="true" style="margin-left: 9px;">→</span>
                    </button>
                </form>

                <div class="auth-links">
                    Don't have an account?
                    <a href="{{ route('register') }}">Create one</a>
                </div>

                <div class="security-note">
                    <span aria-hidden="true">▣</span>
                    Your campus journey starts here.
                </div>
            </div>
        </section>

    </main>
</body>
</html>
