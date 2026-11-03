
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>My Profile | UniFetch</title>

    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        :root {
            --orange: #f97316;
            --orange-dark: #ea580c;
            --orange-light: #fff7ed;
            --text: #1f2937;
            --muted: #6b7280;
            --border: #f1f1f1;
            --background: #f8fafc;
        }

        body {
            font-family: "Segoe UI", Arial, sans-serif;
            background: var(--background);
            color: var(--text);
        }

        /* NAVBAR */
        .navbar {
            height: 75px;
            background: white;
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 0 8%;
            border-bottom: 1px solid #eee;
        }

        .logo {
            font-size: 25px;
            font-weight: 800;
            color: var(--orange);
            letter-spacing: -1px;
        }

        .back-btn {
            text-decoration: none;
            color: #555;
            font-weight: 600;
            font-size: 14px;
            transition: .2s;
        }

        .back-btn:hover {
            color: var(--orange);
        }

        /* PAGE */
        .container {
            max-width: 1050px;
            margin: 45px auto;
            padding: 0 24px;
        }

        .page-heading {
            margin-bottom: 28px;
        }

        .page-heading h1 {
            font-size: 30px;
            font-weight: 750;
            margin-bottom: 8px;
        }

        .page-heading p {
            color: var(--muted);
            font-size: 14px;
        }

        /* ALERTS */
        .alert {
            padding: 14px 18px;
            border-radius: 10px;
            margin-bottom: 20px;
            font-size: 14px;
        }

        .success {
            background: #ecfdf5;
            color: #047857;
            border: 1px solid #a7f3d0;
        }

        .error {
            background: #fef2f2;
            color: #b91c1c;
            border: 1px solid #fecaca;
        }

        /* CARDS */
        .card {
            background: white;
            border: 1px solid var(--border);
            border-radius: 18px;
            padding: 28px;
            margin-bottom: 24px;
            box-shadow: 0 4px 18px rgba(0,0,0,.025);
        }

        .card-heading {
            font-size: 18px;
            font-weight: 700;
            margin-bottom: 22px;
        }

        /* PROFILE TOP */
        .profile-top {
            display: flex;
            align-items: center;
            gap: 28px;
            padding-bottom: 28px;
            border-bottom: 1px solid var(--border);
        }

        .avatar-wrapper {
            position: relative;
            width: 115px;
            height: 115px;
            min-width: 115px;
        }

        .avatar {
            width: 100%;
            height: 100%;
            border-radius: 50%;
            overflow: hidden;
            background: var(--orange-light);
            border: 4px solid #ffedd5;
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--orange);
            font-size: 42px;
            font-weight: 800;
        }

        .avatar img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .profile-name {
            font-size: 24px;
            margin-bottom: 10px;
        }

        .badge {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            background: var(--orange-light);
            color: var(--orange-dark);
            padding: 7px 13px;
            border-radius: 30px;
            font-size: 12px;
            font-weight: 700;
        }

        .profile-description {
            color: var(--muted);
            font-size: 13px;
            margin-top: 12px;
        }

        /* UPLOAD */
        .upload-area {
            margin-top: 22px;
        }

        .upload-area label {
            display: block;
            font-size: 13px;
            font-weight: 600;
            margin-bottom: 10px;
        }

        .upload-controls {
            display: flex;
            align-items: center;
            flex-wrap: wrap;
            gap: 12px;
        }

        .file-input {
            font-size: 13px;
            max-width: 100%;
        }

        .upload-btn {
            background: var(--orange);
            color: white;
            border: none;
            padding: 10px 17px;
            border-radius: 9px;
            font-weight: 600;
            cursor: pointer;
            transition: .2s;
        }

        .upload-btn:hover {
            background: var(--orange-dark);
            transform: translateY(-1px);
        }

        .upload-hint {
            color: #9ca3af;
            font-size: 12px;
            margin-top: 10px;
        }

        /* INFORMATION */
        .info-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 18px;
        }

        .info-item {
            background: #fafafa;
            border: 1px solid #f3f4f6;
            padding: 18px;
            border-radius: 12px;
        }

        .info-item label {
            display: block;
            color: var(--muted);
            font-size: 12px;
            margin-bottom: 9px;
        }

        .info-item p {
            font-size: 14px;
            font-weight: 650;
            overflow-wrap: anywhere;
        }

        /* RUNNER */
        .runner-card {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 24px;
            background: linear-gradient(135deg, #fff7ed, #ffffff);
            border: 1px solid #fed7aa;
        }

        .runner-icon {
            width: 48px;
            height: 48px;
            min-width: 48px;
            background: #ffedd5;
            color: var(--orange-dark);
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 13px;
            font-size: 23px;
            margin-bottom: 15px;
        }

        .runner-card h3 {
            font-size: 18px;
            margin-bottom: 8px;
        }

        .runner-card p {
            color: var(--muted);
            font-size: 14px;
            line-height: 1.6;
            max-width: 550px;
        }

        .coming-soon {
            background: #ffedd5;
            color: var(--orange-dark);
            border: none;
            padding: 12px 20px;
            border-radius: 10px;
            font-weight: 700;
            white-space: nowrap;
            cursor: not-allowed;
        }

        /* FOOTER */
        .footer {
            text-align: center;
            color: #9ca3af;
            font-size: 13px;
            padding: 30px 20px;
        }

        /* RESPONSIVE */
        @media (max-width: 650px) {
            .navbar {
                padding: 0 5%;
            }

            .container {
                margin: 30px auto;
                padding: 0 16px;
            }

            .page-heading h1 {
                font-size: 25px;
            }

            .card {
                padding: 22px;
            }

            .profile-top {
                flex-direction: column;
                align-items: flex-start;
                gap: 20px;
            }

            .profile-name {
                font-size: 21px;
            }

            .info-grid {
                grid-template-columns: 1fr;
            }

            .runner-card {
                flex-direction: column;
                align-items: flex-start;
            }

            .coming-soon {
                width: 100%;
            }
        }
    </style>
</head>

<body>

<nav class="navbar">
    <div class="logo">UniFetch</div>

    <a href="{{ route('dashboard') }}" class="back-btn">
        ← Back to Dashboard
    </a>
</nav>

<main class="container">

    <div class="page-heading">
        <h1>My Profile</h1>
        <p>Manage your personal information and UniFetch account.</p>
    </div>

    {{-- SUCCESS MESSAGE --}}
    @if(session('success'))
        <div class="alert success">
            {{ session('success') }}
        </div>
    @endif

    {{-- ERROR MESSAGE --}}
    @if($errors->any())
        <div class="alert error">
            {{ $errors->first() }}
        </div>
    @endif

    {{-- PROFILE CARD --}}
    <section class="card">

        <div class="profile-top">

            <div class="avatar-wrapper">
                <div class="avatar">
                    @if(Auth::user()->profile_image)
                        <img
                            src="{{ asset('storage/' . Auth::user()->profile_image) }}"
                            alt="Profile Picture"
                        >
                    @else
                        {{ strtoupper(substr(Auth::user()->name, 0, 1)) }}
                    @endif
                </div>
            </div>

            <div class="profile-content">

                <h2 class="profile-name">
                    {{ Auth::user()->name }}
                </h2>

                <span class="badge">
                    ● STUDENT ACCOUNT
                </span>

                <p class="profile-description">
                    Welcome to your UniFetch account.
                </p>

                {{-- UPLOAD FORM --}}
                <form
                    action="{{ route('profile.image') }}"
                    method="POST"
                    enctype="multipart/form-data"
                    class="upload-area"
                >
                    @csrf

                    <label for="profile_image">
                        Update Profile Picture
                    </label>

                    <div class="upload-controls">
                        <input
                            type="file"
                            id="profile_image"
                            name="profile_image"
                            accept="image/png,image/jpeg,image/webp"
                            required
                            class="file-input"
                        >

                        <button type="submit" class="upload-btn">
                            Upload Photo
                        </button>
                    </div>

                    <p class="upload-hint">
                        JPG, PNG, or WEBP · Maximum 2MB
                    </p>
                </form>

            </div>

        </div>

    </section>

    {{-- PERSONAL INFORMATION --}}
    <section class="card">

        <h3 class="card-heading">Personal Information</h3>

        <div class="info-grid">

            <div class="info-item">
                <label>Full Name</label>
                <p>{{ Auth::user()->name }}</p>
            </div>

            <div class="info-item">
                <label>Student ID</label>
                <p>{{ Auth::user()->student_id ?? 'Not provided' }}</p>
            </div>

            <div class="info-item">
                <label>University Email</label>
                <p>{{ Auth::user()->email }}</p>
            </div>

            <div class="info-item">
                <label>Account Type</label>
                <p>Student / Customer</p>
            </div>

        </div>

    </section>

    {{-- RUNNER SECTION --}}
    <section class="card runner-card">

        <div>
            <div class="runner-icon">🚴</div>

            <h3>Become a UniFetch Runner</h3>

            <p>
                Earn while helping fellow students! Deliver food and
                fulfill campus errands with UniFetch.
                Runner enrollment will be available soon.
            </p>
        </div>

        <button class="coming-soon" disabled>
            Coming Soon
        </button>

    </section>

</main>

<footer class="footer">
    © {{ date('Y') }} UniFetch. All rights reserved.
</footer>

</body>
</html>
