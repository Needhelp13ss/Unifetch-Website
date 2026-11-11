
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
/* ===== EDIT PROFILE / CRUD ===== */
.profile-bio { margin-top: 12px; font-size: 14px; line-height: 1.5; max-width: 520px; }
.profile-bio.empty { color: #9ca3af; font-style: italic; }

.chips { display: flex; flex-wrap: wrap; gap: 8px; margin-top: 14px; }
.chip { background: #f3f4f6; color: #374151; padding: 6px 12px; border-radius: 30px; font-size: 12px; font-weight: 600; }
.chip.accent { background: var(--orange-light); color: var(--orange-dark); }

.profile-actions { display: flex; flex-wrap: wrap; gap: 10px; margin-top: 16px; }

.btn { border: none; padding: 10px 17px; border-radius: 9px; font-weight: 600; font-size: 14px; cursor: pointer; transition: .2s; font-family: inherit; }
.btn-primary { background: var(--orange); color: #fff; }
.btn-primary:hover { background: var(--orange-dark); }
.btn-outline { background: #fff; color: var(--orange-dark); border: 1px solid #fed7aa; }
.btn-outline:hover { background: var(--orange-light); }
.btn-ghost-danger { background: #fff; color: #b91c1c; border: 1px solid #fecaca; }
.btn-ghost-danger:hover { background: #fef2f2; }
.btn-danger { background: #dc2626; color: #fff; }
.btn-danger:hover { background: #b91c1c; }

.card-header { display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 12px; margin-bottom: 18px; }
.card-header .card-heading { margin-bottom: 0; }
.header-actions { display: flex; gap: 10px; flex-wrap: wrap; }

.completion { margin-bottom: 22px; }
.completion-top { display: flex; justify-content: space-between; font-size: 12px; color: var(--muted); margin-bottom: 8px; }
.completion-bar { height: 8px; background: #f3f4f6; border-radius: 99px; overflow: hidden; }
.completion-fill { height: 100%; background: linear-gradient(90deg, #fb923c, var(--orange)); border-radius: 99px; transition: width .4s; }

.info-item p.empty { color: #9ca3af; font-weight: 500; }
.info-item.full { grid-column: 1 / -1; }

/* Modal */
.modal-overlay { position: fixed; inset: 0; background: rgba(17,24,39,.55); display: none; align-items: center; justify-content: center; padding: 20px; z-index: 50; }
.modal-overlay.open { display: flex; }
.modal { background: #fff; border-radius: 18px; width: 100%; max-width: 640px; max-height: 90vh; overflow-y: auto; padding: 28px; box-shadow: 0 20px 50px rgba(0,0,0,.2); }
.modal h3 { font-size: 20px; margin-bottom: 4px; }
.modal-sub { color: var(--muted); font-size: 13px; margin-bottom: 22px; }
.form-grid { display: grid; grid-template-columns: repeat(2, 1fr); gap: 16px; }
.form-group.full { grid-column: 1 / -1; }
.form-group label { display: block; font-size: 12px; font-weight: 600; margin-bottom: 6px; color: #374151; }
.form-group input, .form-group select, .form-group textarea {
    width: 100%; padding: 10px 12px; border: 1px solid #e5e7eb; border-radius: 9px;
    font-size: 14px; font-family: inherit; background: #fff; color: var(--text);
}
.form-group textarea { resize: vertical; min-height: 70px; }
.form-group input:focus, .form-group select:focus, .form-group textarea:focus {
    outline: none; border-color: var(--orange); box-shadow: 0 0 0 3px rgba(249,115,22,.15);
}
.form-group .checkbox-row { display: flex; align-items: center; gap: 8px; font-size: 13px; margin-top: 10px; font-weight: 500; }
.form-group .checkbox-row input { width: auto; }
.field-error { color: #b91c1c; font-size: 12px; margin-top: 5px; }
.char-count { text-align: right; font-size: 11px; color: #9ca3af; margin-top: 4px; }
.modal-actions { display: flex; justify-content: flex-end; gap: 10px; margin-top: 24px; }

/* Danger zone */
.danger-card { border-color: #fecaca; }
.danger-card .card-heading { color: #b91c1c; }
.danger-text { color: var(--muted); font-size: 13px; margin-bottom: 16px; }
.danger-row { display: flex; gap: 10px; flex-wrap: wrap; }
.danger-row input { flex: 1; min-width: 200px; padding: 10px 12px; border: 1px solid #e5e7eb; border-radius: 9px; font-size: 14px; font-family: inherit; }

@media (max-width: 650px) {
    .form-grid { grid-template-columns: 1fr; }
    .modal { padding: 22px; }
    .modal-actions .btn { flex: 1; }
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
@php $user = Auth::user(); @endphp
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

                @if($user->bio)
    <p class="profile-bio">{{ $user->bio }}</p>
@else
    <p class="profile-bio empty">No caption yet. Tell people about yourself!</p>
@endif

<div class="chips">
    @if($user->show_gender && $user->gender_label)
        <span class="chip accent">{{ $user->gender_label }}</span>
    @endif
    @if($user->course)
        <span class="chip">🎓 {{ $user->course }}</span>
    @endif
    @if($user->year_level)
        <span class="chip">{{ $user->year_level }}</span>
    @endif
    @if($user->campus_location)
        <span class="chip">📍 {{ $user->campus_location }}</span>
    @endif
</div>

<div class="profile-actions">
    <button type="button" class="btn btn-primary" onclick="openEdit()">✏️ Edit Profile</button>
</div>

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
                    @if($user->profile_image)
    <form action="{{ route('profile.image.destroy') }}" method="POST"
          onsubmit="return confirm('Remove your profile picture?')" style="margin-top:10px;">
        @csrf
        @method('DELETE')
        <button type="submit" class="btn btn-ghost-danger" style="padding:7px 13px;font-size:12px;">
            Remove photo
        </button>
    </form>
@endif
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
{{-- ABOUT ME --}}
<section class="card">

    <div class="card-header">
        <h3 class="card-heading">About Me</h3>

        <div class="header-actions">
            <button type="button" class="btn btn-outline" onclick="openEdit()">✏️ Edit</button>

            <form action="{{ route('profile.details.clear') }}" method="POST"
                  onsubmit="return confirm('Clear all your About Me details? (Name, email and student ID stay.)')">
                @csrf
                @method('DELETE')
                <button type="submit" class="btn btn-ghost-danger">Clear details</button>
            </form>
        </div>
    </div>

    <div class="completion">
        <div class="completion-top">
            <span>Profile completion</span>
            <strong>{{ $user->profile_completion }}%</strong>
        </div>
        <div class="completion-bar">
            <div class="completion-fill" style="width: {{ $user->profile_completion }}%"></div>
        </div>
    </div>

    <div class="info-grid">
        <div class="info-item">
            <label>Gender</label>
            <p class="{{ $user->gender_label ? '' : 'empty' }}">
                {{ $user->gender_label ?? 'Not set' }}
                @if($user->gender_label)
                    <small style="color:#9ca3af;font-weight:500;">
                        · {{ $user->show_gender ? 'visible on profile' : 'hidden' }}
                    </small>
                @endif
            </p>
        </div>

        <div class="info-item">
            <label>Birthday</label>
            <p class="{{ $user->birthday ? '' : 'empty' }}">
                {{ $user->birthday ? $user->birthday->format('F j, Y') . ' (' . $user->birthday->age . ' yrs)' : 'Not set' }}
            </p>
        </div>

        <div class="info-item">
            <label>Phone</label>
            <p class="{{ $user->phone ? '' : 'empty' }}">{{ $user->phone ?? 'Not set' }}</p>
        </div>

        <div class="info-item">
            <label>Course / Program</label>
            <p class="{{ $user->course ? '' : 'empty' }}">{{ $user->course ?? 'Not set' }}</p>
        </div>

        <div class="info-item">
            <label>Year Level</label>
            <p class="{{ $user->year_level ? '' : 'empty' }}">{{ $user->year_level ?? 'Not set' }}</p>
        </div>

        <div class="info-item">
            <label>Campus Location / Dorm</label>
            <p class="{{ $user->campus_location ? '' : 'empty' }}">{{ $user->campus_location ?? 'Not set' }}</p>
        </div>

        <div class="info-item">
            <label>Favorite Food</label>
            <p class="{{ $user->favorite_food ? '' : 'empty' }}">🍔 {{ $user->favorite_food ?? 'Not set' }}</p>
        </div>

        <div class="info-item">
            <label>Interests</label>
            @if(count($user->interests_list))
                <div class="chips" style="margin-top:0;">
                    @foreach($user->interests_list as $tag)
                        <span class="chip accent">#{{ $tag }}</span>
                    @endforeach
                </div>
            @else
                <p class="empty">Not set</p>
            @endif
        </div>
    </div>

</section>
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
{{-- DANGER ZONE --}}
<section class="card danger-card">
    <h3 class="card-heading">Danger Zone</h3>
    <p class="danger-text">
        Deleting your account permanently removes your profile and photo. This can't be undone.
    </p>

    @if($errors->hasBag('delete'))
        <div class="alert error">{{ $errors->getBag('delete')->first('password') }}</div>
    @endif

    <form action="{{ route('profile.destroy') }}" method="POST"
          onsubmit="return confirm('Are you absolutely sure? This cannot be undone.')">
        @csrf
        @method('DELETE')

        <div class="danger-row">
            <input type="password" name="password" placeholder="Enter your password to confirm" required>
            <button type="submit" class="btn btn-danger">Delete Account</button>
        </div>
    </form>
</section>
</main>

<footer class="footer">
    © {{ date('Y') }} UniFetch. All rights reserved.
</footer>
{{-- EDIT PROFILE MODAL --}}
<div class="modal-overlay {{ $errors->hasBag('profile') ? 'open' : '' }}" id="editModal">
    <div class="modal">

        <h3>Edit Profile</h3>
        <p class="modal-sub">Everything except name, email and student ID is optional.</p>

        @if($errors->hasBag('profile'))
            <div class="alert error">Please fix the highlighted fields below.</div>
        @endif

        <form action="{{ route('profile.update') }}" method="POST">
            @csrf
            @method('PUT')

            <div class="form-grid">

                <div class="form-group full">
                    <label for="name">Full Name</label>
                    <input type="text" id="name" name="name" value="{{ old('name', $user->name) }}" required>
                    @error('name', 'profile') <div class="field-error">{{ $message }}</div> @enderror
                </div>

                <div class="form-group">
                    <label for="student_id">Student ID</label>
                    <input type="text" id="student_id" name="student_id" value="{{ old('student_id', $user->student_id) }}" required>
                    @error('student_id', 'profile') <div class="field-error">{{ $message }}</div> @enderror
                </div>

                <div class="form-group">
                    <label for="email">University Email</label>
                    <input type="email" id="email" name="email" value="{{ old('email', $user->email) }}" required>
                    @error('email', 'profile') <div class="field-error">{{ $message }}</div> @enderror
                </div>

                <div class="form-group full">
                    <label for="bio">Caption / Bio</label>
                    <textarea id="bio" name="bio" maxlength="160"
                              placeholder="e.g. Coffee lover ☕ | Always hungry between classes">{{ old('bio', $user->bio) }}</textarea>
                    <div class="char-count" id="bioCount">0/160</div>
                    @error('bio', 'profile') <div class="field-error">{{ $message }}</div> @enderror
                </div>

                <div class="form-group">
                    <label for="gender">Gender</label>
                    <select id="gender" name="gender">
                        <option value="">Select…</option>
                        @foreach(['male' => 'Male', 'female' => 'Female', 'other' => 'Other', 'prefer_not_to_say' => 'Prefer not to say'] as $value => $label)
                            <option value="{{ $value }}" @selected(old('gender', $user->gender) === $value)>{{ $label }}</option>
                        @endforeach
                    </select>
                    <label class="checkbox-row">
                        <input type="checkbox" name="show_gender" value="1"
                               @checked(old('show_gender', $user->show_gender))>
                        Show gender on my profile
                    </label>
                    @error('gender', 'profile') <div class="field-error">{{ $message }}</div> @enderror
                </div>

                <div class="form-group">
                    <label for="birthday">Birthday</label>
                    <input type="date" id="birthday" name="birthday"
                           value="{{ old('birthday', $user->birthday?->format('Y-m-d')) }}"
                           max="{{ now()->subDay()->format('Y-m-d') }}">
                    @error('birthday', 'profile') <div class="field-error">{{ $message }}</div> @enderror
                </div>

                <div class="form-group">
                    <label for="phone">Phone Number</label>
                    <input type="text" id="phone" name="phone" value="{{ old('phone', $user->phone) }}" placeholder="09XX XXX XXXX">
                    @error('phone', 'profile') <div class="field-error">{{ $message }}</div> @enderror
                </div>

                <div class="form-group">
                    <label for="course">Course / Program</label>
                    <input type="text" id="course" name="course" value="{{ old('course', $user->course) }}" placeholder="e.g. BS Information Technology">
                    @error('course', 'profile') <div class="field-error">{{ $message }}</div> @enderror
                </div>

                <div class="form-group">
                    <label for="year_level">Year Level</label>
                    <select id="year_level" name="year_level">
                        <option value="">Select…</option>
                        @foreach(['1st Year','2nd Year','3rd Year','4th Year','5th Year','Graduate'] as $level)
                            <option value="{{ $level }}" @selected(old('year_level', $user->year_level) === $level)>{{ $level }}</option>
                        @endforeach
                    </select>
                    @error('year_level', 'profile') <div class="field-error">{{ $message }}</div> @enderror
                </div>

                <div class="form-group">
                    <label for="campus_location">Campus Location / Dorm</label>
                    <input type="text" id="campus_location" name="campus_location"
                           value="{{ old('campus_location', $user->campus_location) }}" placeholder="e.g. Engineering Bldg, Room 204">
                    @error('campus_location', 'profile') <div class="field-error">{{ $message }}</div> @enderror
                </div>

                <div class="form-group">
                    <label for="favorite_food">Favorite Food</label>
                    <input type="text" id="favorite_food" name="favorite_food"
                           value="{{ old('favorite_food', $user->favorite_food) }}" placeholder="e.g. Chicken Joy">
                    @error('favorite_food', 'profile') <div class="field-error">{{ $message }}</div> @enderror
                </div>

                <div class="form-group full">
                    <label for="interests">Interests <span style="color:#9ca3af;font-weight:400;">(separate with commas, max 6)</span></label>
                    <input type="text" id="interests" name="interests"
                           value="{{ old('interests', $user->interests) }}" placeholder="gaming, coding, basketball">
                    @error('interests', 'profile') <div class="field-error">{{ $message }}</div> @enderror
                </div>

            </div>

            <div class="modal-actions">
                <button type="button" class="btn btn-outline" onclick="closeEdit()">Cancel</button>
                <button type="submit" class="btn btn-primary">Save Changes</button>
            </div>
        </form>

    </div>
</div>

<script>
    const editModal = document.getElementById('editModal');

    function openEdit()  { editModal.classList.add('open');    document.body.style.overflow = 'hidden'; }
    function closeEdit() { editModal.classList.remove('open'); document.body.style.overflow = ''; }

    // click the dark backdrop or press Esc to close
    editModal.addEventListener('click', e => { if (e.target === editModal) closeEdit(); });
    document.addEventListener('keydown', e => { if (e.key === 'Escape') closeEdit(); });

    // live caption counter
    const bio = document.getElementById('bio');
    const bioCount = document.getElementById('bioCount');
    function updateBioCount() { bioCount.textContent = bio.value.length + '/160'; }
    bio.addEventListener('input', updateBioCount);
    updateBioCount();

    @if($errors->hasBag('profile'))
        document.body.style.overflow = 'hidden';
    @endif
</script>
</body>
</html>
