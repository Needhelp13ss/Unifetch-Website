@extends('layouts.shop')

@section('title', 'Custom Order')
@section('nav', 'menu')
@section('inline-errors', '1')

@section('content')
<main class="wrap page">
    <div class="page-head">
        <div>
            <div class="eyebrow">Food Ordering</div>
            <h1>Want something that isn't listed?</h1>
            <p>Make a custom order. A verified runner will buy it and bring it to you.</p>
        </div>
        <a class="link-arrow" href="{{ route('menu') }}">← Back to menu</a>
    </div>

    <div class="errand-hero">
        <div class="eyebrow">How it works</div>
        <div class="errand-steps">
            <div><b>01 · Tell us what you want</b>Describe the item and where to get it.</div>
            <div><b>02 · We match a runner</b>A verified runner takes the job. You'll see their name.</div>
            <div><b>03 · Track it to your door</b>Follow it from "Looking for runners" to "Delivered".</div>
        </div>
    </div>

    <form class="checkout-layout" method="POST" action="{{ route('custom.store') }}" novalidate>
        @csrf
        <div style="display:grid;gap:24px">
            <section class="panel">
                <div class="panel-head"><h2>📝 Your order</h2></div>
                <div class="form-pad">
                    <div class="field">
                        <label class="lbl" for="errand_title">What do you want?</label>
                        <input class="input" id="errand_title" name="errand_title" list="ideas" maxlength="120"
                               value="{{ old('errand_title') }}" placeholder="e.g. Chicken sandwich from the shop across the gate" required>
                        <datalist id="ideas">@foreach($ideas as $i)<option value="{{ $i }}">@endforeach</datalist>
                        @error('errand_title')<div class="err">{{ $message }}</div>@enderror
                    </div>
                    <div class="field">
                        <label class="lbl" for="errand_details">Details for the runner</label>
                        <textarea class="input" id="errand_details" name="errand_details" maxlength="1000" rows="4" required
                                  placeholder="e.g. 2 pieces, no mayo. Any brand of bottled water is fine.">{{ old('errand_details') }}</textarea>
                        @error('errand_details')<div class="err">{{ $message }}</div>@enderror
                    </div>
                    <div class="row2">
                        <div class="field" style="margin-bottom:0">
                            <label class="lbl" for="pickup_location">Get it from <span style="font-weight:400;color:var(--muted)">(optional)</span></label>
                            <input class="input" id="pickup_location" name="pickup_location" maxlength="120" value="{{ old('pickup_location') }}" placeholder="e.g. 7-Eleven near the Main Gate">
                            @error('pickup_location')<div class="err">{{ $message }}</div>@enderror
                        </div>
                        <div class="field" style="margin-bottom:0">
                            <label class="lbl" for="budget">Item budget <span style="font-weight:400;color:var(--muted)">(optional)</span></label>
                            <input class="input" id="budget" name="budget" type="number" min="0" max="5000" step="1" value="{{ old('budget') }}" placeholder="₱ most you'll pay for the item">
                            @error('budget')<div class="err">{{ $message }}</div>@enderror
                        </div>
                    </div>
                    <p class="hint">The cost of the item itself is paid straight to your runner when they hand it over. Your payment below is only the runner fee.</p>
                </div>
            </section>

            <section class="panel">
                <div class="panel-head"><h2>📍 Delivery</h2></div>
                <div class="form-pad">
                    <div class="field" style="margin-bottom:0">
                        <label class="lbl" for="delivery_location">Where should we bring it?</label>
                        <input class="input" id="delivery_location" name="delivery_location" list="places" maxlength="120"
                               value="{{ old('delivery_location', $defaultLocation) }}" placeholder="e.g. Annex Building, Room 211" required>
                        <datalist id="places">@foreach($locations as $place)<option value="{{ $place }}">@endforeach</datalist>
                        @error('delivery_location')<div class="err">{{ $message }}</div>@enderror
                    </div>
                </div>
            </section>
        </div>

        <aside class="panel summary">
            <h2>Runner fee</h2>
            <div class="field">
                <label class="lbl" for="fee">What will you pay the runner? (₱{{ $minFee }}–₱{{ $maxFee }})</label>
                <input class="input" id="fee" name="fee" type="number" min="{{ $minFee }}" max="{{ $maxFee }}" step="1" value="{{ old('fee', 40) }}" required>
                <p class="hint" style="margin-top:6px">A fair fee gets your errand picked up faster.</p>
                @error('fee')<div class="err">{{ $message }}</div>@enderror
            </div>

            <div class="lbl" style="font-weight:700;margin-bottom:8px">Pay the fee with</div>
            <div class="pay-options">
                <label class="pay">
                    <input type="radio" name="payment_method" value="cod" {{ old('payment_method', 'cod') === 'cod' ? 'checked' : '' }}>
                    <span class="ico">💵</span><span><b>Cash</b><small>Pay the runner on arrival.</small></span><span class="dot"></span>
                </label>
                <label class="pay">
                    <input type="radio" name="payment_method" value="wallet" {{ old('payment_method') === 'wallet' ? 'checked' : '' }}>
                    <span class="ico">👛</span><span><b>UniFetch Wallet</b><small>Balance: {{ \App\Support\Money::format($walletBalance) }}</small></span><span class="dot"></span>
                </label>
            </div>
            @error('payment_method')<div class="err">{{ $message }}</div>@enderror

            <button class="btn btn-primary btn-block" type="submit" style="margin-top:18px">Request custom order</button>
            <p class="hint">You can cancel while we're still looking for a runner.</p>
        </aside>
    </form>
</main>
@endsection
