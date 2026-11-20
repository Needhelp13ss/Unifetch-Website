@extends('layouts.shop')

@section('title', 'My Cart')
@section('nav', 'menu')

@section('content')
<main class="wrap page">
    <div class="steps-nav">
        <span class="on"><span class="num">1</span> Cart</span> ›
        <span><span class="num">2</span> Checkout</span> ›
        <span><span class="num">3</span> Done</span>
    </div>

    <div class="page-head">
        <div>
            <div class="eyebrow">Your cart</div>
            <h1>What are you buying today?</h1>
            <p>Tick the items you want to check out now. Anything you leave unticked stays in your cart for later.</p>
        </div>
        <a class="link-arrow" href="{{ route('menu') }}">← Keep browsing</a>
    </div>

    @if($items->isEmpty())
        <div class="empty">
            <div class="big">🛒</div>
            <h3>Your cart is empty</h3>
            <p>Add something to get started.</p>
            <a class="btn btn-primary" href="{{ route('menu') }}">Browse the menu</a>
        </div>
    @else
        <div class="cart-layout" id="cart" data-fee="{{ $deliveryFee }}">

            {{-- Items --}}
            <section class="panel">
                <div class="panel-head">
                    <label class="check">
                        <input type="checkbox" id="pickAll" checked>
                        <span class="box"><svg class="icon"><use href="#i-check"/></svg></span>
                        <span>Select all (<span id="itemsCount">{{ $items->count() }}</span>)</span>
                    </label>
                    <span class="count-note" id="selNote"></span>
                </div>

                @foreach($items as $item)
                    @php($p = $item->product)
                    <div class="cart-row" data-id="{{ $item->id }}" data-price="{{ $p->price }}"
                         data-update="{{ route('cart.update', $item) }}" data-remove="{{ route('cart.destroy', $item) }}">

                        <label class="check" aria-label="Select {{ $p->name }}">
                            <input type="checkbox" class="pick" name="items[]" value="{{ $item->id }}" form="checkoutForm" checked>
                            <span class="box"><svg class="icon"><use href="#i-check"/></svg></span>
                        </label>

                        <div class="mini" style="background: linear-gradient(145deg, {{ $p->bg_color }}, #fff 140%)">
                            @if($p->image)<img src="{{ asset($p->image) }}" alt="">@else<span>{{ $p->emoji }}</span>@endif
                        </div>

                        <div>
                            <h3>{{ $p->name }}</h3>
                            <div class="sub">{{ $p->vendor->name }}</div>
                            <div class="unit">{{ \App\Support\Money::format($p->price) }} each</div>
                            @unless($p->is_available)<div class="err">Not available right now</div>@endunless
                        </div>

                        <div class="qty-cell">
                            <div class="qty" role="group" aria-label="Quantity">
                                <button type="button" class="minus" aria-label="Decrease" {{ $item->quantity <= 1 ? 'disabled' : '' }}>−</button>
                                <span class="q">{{ $item->quantity }}</span>
                                <button type="button" class="plus" aria-label="Increase">+</button>
                            </div>
                        </div>

                        <div class="row-end">
                            <span class="line-total">{{ \App\Support\Money::format($item->line_total) }}</span>
                            <button type="button" class="remove" aria-label="Remove {{ $p->name }}" title="Remove">
                                <svg class="icon"><use href="#i-trash"/></svg>
                            </button>
                        </div>
                    </div>
                @endforeach
            </section>

            {{-- Summary: totals follow what is ticked --}}
            <aside class="panel summary">
                <h2>Order summary</h2>
                <div class="sum-line"><span>Selected items</span><span id="sumCount">0</span></div>
                <div class="sum-line"><span>Subtotal</span><span id="sumSub">₱0</span></div>
                <div class="sum-line"><span>Delivery fee</span><span id="sumFee">₱0</span></div>
                <div class="sum-line total"><span>Total</span><span id="sumTotal">₱0</span></div>

                <form id="checkoutForm" method="POST" action="{{ route('cart.checkout') }}" style="margin-top:18px">
                    @csrf
                    <button class="btn btn-primary btn-block" id="goBtn" type="submit">Proceed to checkout</button>
                </form>
                <p class="hint" id="goHint">Tick at least one item to continue.</p>
            </aside>
        </div>
    @endif
</main>
@endsection

@push('scripts')
<script>
(function () {
    var root = document.getElementById('cart');
    if (!root) return;
    var fee = Number(root.dataset.fee);
    var rows = function () { return Array.prototype.slice.call(root.querySelectorAll('.cart-row')); };
    var pickAll = document.getElementById('pickAll');

    function recalc() {
        var count = 0, sub = 0, selected = 0;
        rows().forEach(function (row) {
            var on = row.querySelector('.pick').checked;
            row.classList.toggle('unticked', !on);
            if (on) {
                selected++;
                var q = Number(row.querySelector('.q').textContent);
                count += q;
                sub += q * Number(row.dataset.price);
            }
        });
        var total = rows().length;
        document.getElementById('sumCount').textContent = count;
        document.getElementById('sumSub').textContent = UF.peso(sub);
        document.getElementById('sumFee').textContent = selected ? UF.peso(fee) : UF.peso(0);
        document.getElementById('sumTotal').textContent = UF.peso(selected ? sub + fee : 0);
        document.getElementById('goBtn').disabled = selected === 0;
        document.getElementById('goHint').style.display = selected === 0 ? 'block' : 'none';
        document.getElementById('itemsCount').textContent = total;
        document.getElementById('selNote').textContent = selected + ' of ' + total + ' ticked';
        pickAll.checked = total > 0 && selected === total;
    }

    pickAll.addEventListener('change', function () {
        rows().forEach(function (r) { r.querySelector('.pick').checked = pickAll.checked; });
        recalc();
    });

    rows().forEach(function (row) {
        var qEl = row.querySelector('.q'), minus = row.querySelector('.minus'), plus = row.querySelector('.plus');
        row.querySelector('.pick').addEventListener('change', recalc);

        function setQty(n) {
            if (n < 1 || n > 20) return;
            minus.disabled = plus.disabled = true;
            UF.send(row.dataset.update, 'PATCH', { quantity: n }).then(function (res) {
                if (res.ok) {
                    qEl.textContent = res.quantity;
                    row.querySelector('.line-total').textContent = res.line_total_text;
                    UF.setCount(res.count);
                } else {
                    UF.toast('Could not change the quantity.', true);
                }
            }).catch(function () { UF.toast('Connection problem. Try again.', true); })
              .finally(function () {
                  minus.disabled = Number(qEl.textContent) <= 1;
                  plus.disabled = false;
                  recalc();
              });
        }

        minus.addEventListener('click', function () { setQty(Number(qEl.textContent) - 1); });
        plus.addEventListener('click', function () { setQty(Number(qEl.textContent) + 1); });

        row.querySelector('.remove').addEventListener('click', function () {
            UF.send(row.dataset.remove, 'DELETE', {}).then(function (res) {
                if (!res.ok) return UF.toast('Could not remove that item.', true);
                UF.setCount(res.count);
                row.remove();
                if (!rows().length) { location.reload(); return; }
                recalc();
                UF.toast('Removed from your cart.');
            });
        });
    });

    recalc();
})();
</script>
@endpush
