@extends('layouts.shop')

@section('title', 'Become a runner')
@section('nav', 'home')
@section('inline-errors', '1')

@section('content')
<main class="wrap page" style="max-width:820px">
    <div class="page-head">
        <div>
            <div class="eyebrow">Runner application</div>
            <h1>Become a UniFetch runner</h1>
            <p>Runners pick up orders and errands around campus and keep the fee. Because they carry out jobs for other students, only currently enrolled UCLM students can be approved.</p>
        </div>
        <a class="link-arrow" href="{{ route('dashboard') }}">← Back</a>
    </div>

    @if($errors->any())<div class="alert error" role="alert">{{ $errors->first() }}</div>@endif

    <form method="POST" action="{{ route('runner.apply') }}" enctype="multipart/form-data" novalidate>
        @csrf
        <section class="panel" style="margin-bottom:24px">
            <div class="panel-head"><h2>🎓 Prove you're a UCLM student</h2></div>
            <div class="form-pad">
                <div class="field">
                    <label class="lbl" for="portal_id">UCLM Portal ID</label>
                    <input class="input" id="portal_id" name="portal_id" value="{{ old('portal_id', $user->portal_id) }}" maxlength="50" placeholder="Your student portal ID" required>
                    @error('portal_id')<div class="err">{{ $message }}</div>@enderror
                </div>
                <div class="row2">
                    <div class="field" style="margin-bottom:0">
                        <label class="lbl" for="course">Course</label>
                        <input class="input" id="course" name="course" value="{{ old('course', $user->course) }}" maxlength="100" placeholder="e.g. BS Information Technology" required>
                        @error('course')<div class="err">{{ $message }}</div>@enderror
                    </div>
                    <div class="field" style="margin-bottom:0">
                        <label class="lbl" for="year_level">Year level</label>
                        <select class="input" id="year_level" name="year_level" required>
                            <option value="">Choose…</option>
                            @foreach($yearLevels as $level)<option value="{{ $level }}" @selected(old('year_level', $user->year_level) === $level)>{{ $level }}</option>@endforeach
                        </select>
                        @error('year_level')<div class="err">{{ $message }}</div>@enderror
                    </div>
                </div>
            </div>
        </section>

        <section class="panel" style="margin-bottom:24px">
            <div class="panel-head"><h2>🪪 School ID photo</h2></div>
            <div class="form-pad">
                <div class="field">
                    <label class="lbl" for="id_photo">Upload a clear photo of your school ID</label>
                    <input class="input" id="id_photo" name="id_photo" type="file" accept="image/png,image/jpeg" required>
                    <p class="hint" style="margin-top:6px">JPG or PNG, up to 4 MB. Your name and photo must be readable. Only the admin can see it, and it is deleted if you delete your account.</p>
                    @error('id_photo')<div class="err">{{ $message }}</div>@enderror
                </div>
                <label class="plain-check">
                    <input type="checkbox" name="enrolled_confirmed" value="1" {{ old('enrolled_confirmed') ? 'checked' : '' }}>
                    <span>I am a currently enrolled UCLM student.</span>
                </label>
                @error('enrolled_confirmed')<div class="err">{{ $message }}</div>@enderror
            </div>
        </section>

        <button class="btn btn-primary" type="submit">Send application</button>
        <p class="hint" style="margin-top:10px">The admin reviews every application. You'll see Runner Jobs once you're approved.</p>
    </form>
</main>
@endsection
