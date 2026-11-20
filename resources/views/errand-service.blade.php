@extends('layouts.shop')

@section('title', 'Errand Services')
@section('nav', 'errand')
@section('inline-errors', '1')

@section('content')
<main class="wrap page">
    <div class="page-head">
        <div>
            <div class="eyebrow">Errand Services</div>
            <h1>Let a runner do it for you</h1>
            <p>Need a parcel picked up or someone to wait in line? Post an errand and a verified runner will take it.</p>
        </div>
        <a class="link-arrow" href="{{ route('orders') }}">My orders →</a>
    </div>

    <div class="errand-hero">
        <div class="eyebrow">How it works</div>
        <div class="errand-steps">
            <div><b>01 · Post your errand</b>Pick the kind, say where to go and what to do.</div>
            <div><b>02 · A runner takes it</b>You'll see "Looking for runners", then your runner's name.</div>
            <div><b>03 · Get it done</b>Follow it until the runner marks it done.</div>
        </div>
    </div>

    <form class="checkout-layout" method="POST" action="{{ route('errand.store') }}" novalidate>
        @csrf
        <div style="display:grid;gap:24px">
            <section class="panel">
                <div class="panel-head"><h2>🏃 What kind of errand?</h2></div>
                <div class="form-pad">
                    <div class="cat-grid">
                        @foreach($categories as $name => [$icon, $hint])
                            <label class="cat">
                                <input type="radio" name="errand_category" value="{{ $name }}" data-hint="{{ $hint }}" {{ old('errand_category') === $name ? 'checked' : '' }} required>
                                <span><i>{{ $icon }}</i><b>{{ $name }}</b><small>{{ $hint }}</small></span>
                            </label>
                        @endforeach
                    </div>
                    @error('errand_category')<div class="err">{{ $message }}</div>@enderror
                </div>
            </section>

            <section class="panel">
                <div class="panel-head"><h2>📝 The details</h2></div>
                <div class="form-pad">
                    <div class="field">
                        <label class="lbl" for="errand_title">Short title</label>
                        <input class="input" id="errand_title" name="errand_title" maxlength="120" value="{{ old('errand_title') }}" placeholder="e.g. Get my parcel from the guard house" required>
                        @error('errand_title')<div class="err">{{ $message }}</div>@enderror
                    </div>
                    <div class="field">
                        <label class="lbl" for="errand_details">What should the runner do?</label>
                        <textarea class="input" id="errand_details" name="errand_details" maxlength="1000" rows="4" required
                                  placeholder="Tell the runner everything they need to know: names, tracking numbers, how long the line usually is...">{{ old('errand_details') }}</textarea>
                        @error('errand_details')<div class="err">{{ $message }}</div>@enderror
                    </div>
                    <div class="row2">
                        <div class="field" style="margin-bottom:0">
                            <label class="lbl" for="pickup_location">Where should the runner go?</label>
                            <input class="input" id="pickup_location" name="pickup_location" list="places" maxlength="120" value="{{ old('pickup_location') }}" placeholder="e.g. Cashier, Main Building" required>
                            @error('pickup_location')<div class="err">{{ $message }}</div>@enderror
                        </div>
                        <div class="field" style="margin-bottom:0">
                            <label class="lbl" for="delivery_location">Where should they meet you?</label>
                            <input class="input" id="delivery_location" name="delivery_location" list="places" maxlength="120" value="{{ old('delivery_location', $defaultLocation) }}" placeholder="e.g. Annex Building, Room 211" required>
                            @error('delivery_location')<div class="err">{{ $message }}</div>@enderror
                        </div>
                    </div>
                    <datalist id="places">@foreach($locations as $place)<option value="{{ $place }}">@endforeach</datalist>

                    <div class="field" style="margin:16px 0 0">
                        <label class="lbl" for="budget">Money the runner may need to pay <span style="font-weight:400;color:var(--muted)">(optional)</span></label>
                        <input class="input" id="budget" name="budget" type="number" min="0" max="5000" step="1" value="{{ old('budget') }}" placeholder="₱ e.g. cash-on-delivery fee for your parcel">
                        @error('budget')<div class="err">{{ $message }}</div>@enderror
                        <p class="hint" style="margin-top:6px">You pay this straight back to your runner. Your payment on the right is only the runner fee.</p>
                    </div>
                </div>
            </section>
        </div>

        <aside class="panel summary">
            <h2>Runner fee</h2>
            <div class="field">
                <label class="lbl" for="fee">What will you pay the runner? (₱{{ $minFee }}–₱{{ $maxFee }})</label>
                <input class="input" id="fee" name="fee" type="number" min="{{ $minFee }}" max="{{ $maxFee }}" step="1" value="{{ old('fee', 50) }}" required>
                <p class="hint" style="margin-top:6px">Longer waits deserve a bigger fee. A fair fee gets picked up faster.</p>
                @error('fee')<div class="err">{{ $message }}</div>@enderror
            </div>

            <div class="lbl" style="font-weight:700;margin-bottom:8px">Pay the fee with</div>
            <div class="pay-options">
                <label class="pay">
                    <input type="radio" name="payment_method" value="cod" {{ old('payment_method', 'cod') === 'cod' ? 'checked' : '' }}>
                    <span class="ico">💵</span><span><b>Cash</b><small>Pay the runner when it's done.</small></span><span class="dot"></span>
                </label>
                <label class="pay">
                    <input type="radio" name="payment_method" value="wallet" {{ old('payment_method') === 'wallet' ? 'checked' : '' }}>
                    <span class="ico">👛</span><span><b>UniFetch Wallet</b><small>Balance: {{ \App\Support\Money::format($walletBalance) }}</small></span><span class="dot"></span>
                </label>
            </div>
            @error('payment_method')<div class="err">{{ $message }}</div>@enderror

            <button class="btn btn-primary btn-block" type="submit" style="margin-top:18px">Post errand</button>
            <p class="hint">You can cancel while we're still looking for a runner.</p>

            @if($recent->isNotEmpty())
                <div style="margin-top:20px;border-top:1px solid var(--line);padding-top:14px">
                    <div class="lbl" style="font-weight:700;margin-bottom:6px">Your recent errands</div>
                    @foreach($recent as $r)
                        <a class="sum-item" href="{{ route('orders.show', $r) }}" style="padding:8px 0">
                            <div class="grow"><b>{{ $r->errand_title }}</b><small>{{ $r->errand_category }}</small></div>
                            <span class="status {{ $r->status }}">{{ $r->status_label }}</span>
                        </a>
                    @endforeach
                </div>
            @endif
        </aside>
    </form>
</main>
@endsection

@push('scripts')
<script>
    // little examples that change with the kind of errand
    var examples = {
        'Pick up a parcel': ['Get my parcel from the guard house', 'Tracking no. ..., name on the parcel: ... Please check it is not damaged.', 'Guard house, Main Gate'],
        'Wait in line': ['Line up at the cashier for me', 'Pay my tuition balance. I will send the exact amount and my ID photo. Message me when you are near the window.', 'Cashier, Main Building'],
        'Submit or claim documents': ['Submit my clearance form to the registrar', 'Hand in the folder, get it stamped, and bring back the receipt.', 'Registrar, Main Building'],
        'Get something for me': ['Get my charger from my friend', 'Pick it up from Ana at the library entrance, black cable in a blue pouch.', 'Library entrance'],
        'Other': ['Short title for your errand', 'Tell the runner everything they need to know.', 'Where the runner should go']
    };
    document.querySelectorAll('input[name=errand_category]').forEach(function (r) {
        r.addEventListener('change', function () {
            var e = examples[r.value]; if (!e) { return; }
            document.getElementById('errand_title').placeholder = 'e.g. ' + e[0];
            document.getElementById('errand_details').placeholder = e[1];
            document.getElementById('pickup_location').placeholder = 'e.g. ' + e[2];
        });
    });
</script>
@endpush
