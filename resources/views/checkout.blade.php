@extends('layouts.shop')

@section('title', 'Checkout')
@section('nav', 'menu')
@section('inline-errors', '1')

@section('content')
<main class="wrap page">
    <div class="steps-nav">
        <span class="done"><span class="num">✓</span> Cart</span> ›
        <span class="on"><span class="num">2</span> Checkout</span> ›
        <span><span class="num">3</span> Done</span>
    </div>

    <div class="page-head">
        <div>
            <div class="eyebrow">Almost there</div>
            <h1>Checkout</h1>
            <p>Tell us where to bring your order and how you'll pay.</p>
        </div>
        <a class="link-arrow" href="{{ route('cart') }}">← Back to cart</a>
    </div>

    <form class="checkout-layout" method="POST" action="{{ route('orders.store') }}" novalidate
          data-quote="{{ route('quote') }}" data-subtotal="{{ $subtotal }}" data-from="{{ $fromIds }}">
        @csrf
        @foreach($items as $item)
            <input type="hidden" name="items[]" value="{{ $item->id }}">
        @endforeach

        <div style="display:grid;gap:24px">

            <section class="panel">
                <div class="panel-head"><h2>📍 Delivery details</h2></div>
                <div class="form-pad">
                    <div class="row2">
                        <div class="field">
                            <label class="lbl" for="location_id">Where should we deliver?</label>
                            <select class="input" id="location_id" name="location_id" required>
                                <option value="">Pick a campus place…</option>
                                @foreach($places as $place)
                                    <option value="{{ $place->id }}" {{ (string) old('location_id', $defaultPlace) === (string) $place->id ? 'selected' : '' }}>{{ $place->name }}</option>
                                @endforeach
                            </select>
                            @error('location_id')<div class="err">{{ $message }}</div>@enderror
                        </div>
                        <div class="field">
                            <label class="lbl" for="delivery_details">Room or details <span style="font-weight:400;color:var(--muted)">(optional)</span></label>
                            <input class="input" id="delivery_details" name="delivery_details" value="{{ old('delivery_details') }}" maxlength="80" placeholder="e.g. Room 211, left entrance">
                            @error('delivery_details')<div class="err">{{ $message }}</div>@enderror
                        </div>
                    </div>

                    <div class="field" style="margin-bottom:0">
                        <label class="lbl" for="notes">Notes for the runner <span style="font-weight:400;color:var(--muted)">(optional)</span></label>
                        <textarea class="input" id="notes" name="notes" maxlength="255"
                                  placeholder="e.g. No onions please. I'll be at the left side entrance.">{{ old('notes') }}</textarea>
                        @error('notes')<div class="err">{{ $message }}</div>@enderror
                    </div>
                </div>
            </section>

            <section class="panel">
                <div class="panel-head"><h2>💳 Payment method</h2></div>
                <div class="form-pad">
                    <div class="pay-options">
                        <label class="pay">
                            <input type="radio" name="payment_method" value="cod" {{ old('payment_method', 'cod') === 'cod' ? 'checked' : '' }}>
                            <span class="ico">💵</span>
                            <span><b>Cash on Delivery</b><small>Pay the runner in cash when your order arrives.</small></span>
                            <span class="dot"></span>
                        </label>
                        @php($enough = $walletBalance >= $subtotal + $quote['total_fees'])
                        <label class="pay {{ $enough ? '' : 'disabled' }}">
                            <input type="radio" name="payment_method" value="wallet" {{ old('payment_method') === 'wallet' ? 'checked' : '' }} {{ $enough ? '' : 'disabled' }}>
                            <span class="ico">👛</span>
                            <span><b>UniFetch Wallet</b>
                                <small>Balance: {{ \App\Support\Money::format($walletBalance) }}.
                                @if($enough) Paid instantly, no cash needed. @else Not enough for this order. <a href="{{ route('wallet') }}" style="color:var(--orange);font-weight:600">Add money</a> @endif</small></span>
                            <span class="dot"></span>
                        </label>
                    </div>
                    @error('payment_method')<div class="err">{{ $message }}</div>@enderror
                </div>
            </section>
        </div>

        <aside class="panel summary">
            <h2>Your order</h2>
            <div class="sum-items">
                @foreach($items as $item)
                    @php($p = $item->product)
                    <div class="sum-item">
                        <div class="mini" style="background: linear-gradient(145deg, {{ $p->bg_color }}, #fff 140%)">{{ $p->emoji }}</div>
                        <div class="grow">
                            <b>{{ $p->name }}</b>
                            <small>{{ $p->vendor->name }} · × {{ $item->quantity }}</small>
                        </div>
                        <b>{{ \App\Support\Money::format($item->line_total) }}</b>
                    </div>
                @endforeach
            </div>

            <div class="sum-line"><span>Subtotal</span><span>{{ \App\Support\Money::format($subtotal) }}</span></div>
            <div class="sum-line"><span>Runner fee <small style="color:var(--muted)">(<span data-q="distance">{{ $defaultPlace ? $quote['meters'] . ' m' : 'pick a place' }}</span>)</small></span><span data-q="runner">{{ \App\Support\Money::format($quote['runner_fee']) }}</span></div>
            <div class="sum-line"><span>Service fee</span><span data-q="service">{{ \App\Support\Money::format($quote['service_fee']) }}</span></div>
            <div class="sum-line total"><span>Total</span><span data-q="total">{{ \App\Support\Money::format($subtotal + $quote['total_fees']) }}</span></div>
            <p class="hint" style="margin-top:8px">Runner fee: ₱{{ \App\Support\Pricing::BASE_FEE }} plus ₱{{ \App\Support\Pricing::STEP_FEE }} for every {{ \App\Support\Pricing::STEP_METERS }} m from the shop to you.</p>

            <button class="btn btn-primary btn-block" type="submit" style="margin-top:18px">Place order</button>
            <p class="hint">Items you didn't tick stay in your cart.</p>
        </aside>
    </form>
</main>
@endsection

@push('scripts')
<script src="{{ asset('js/quote.js') }}"></script>
@endpush
