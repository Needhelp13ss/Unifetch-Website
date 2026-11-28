{{-- Chat between the customer and the runner. --}}
<section class="panel chat" id="chat" style="margin-top:24px"
         data-feed="{{ route('orders.chat.index', $order) }}" data-send="{{ route('orders.chat.store', $order) }}">
    <div class="panel-head"><h2>💬 Chat with {{ $me->id === $order->user_id ? ($order->runner?->name ?? 'your runner') : $order->user->name }}</h2></div>
    <div class="chat-log" id="chatLog" aria-live="polite"><p class="chat-empty" id="chatEmpty">No messages yet. Say hi!</p></div>
    @if($order->chatOpen())
        <form class="chat-form" id="chatForm" autocomplete="off">
            <input class="input" id="chatBody" maxlength="500" placeholder="Type a message…" required>
            <button class="btn btn-primary" type="submit">Send</button>
        </form>
    @else
        <p class="chat-closed">This chat is closed because the order is finished.</p>
    @endif
</section>

@push('scripts')
<script>
(function () {
    var root = document.getElementById('chat'), log = document.getElementById('chatLog');
    var last = 0, busy = false;
    function esc(t) { var d = document.createElement('div'); d.textContent = t; return d.innerHTML; }
    function pull() {
        if (busy) return; busy = true;
        fetch(root.dataset.feed + '?after=' + last, { headers: { 'Accept': 'application/json' } })
            .then(function (r) { return r.json(); })
            .then(function (j) {
                if (j.messages.length) {
                    var empty = document.getElementById('chatEmpty'); if (empty) empty.remove();
                    var stick = log.scrollTop + log.clientHeight >= log.scrollHeight - 40;
                    j.messages.forEach(function (m) {
                        last = Math.max(last, m.id);
                        var el = document.createElement('div');
                        el.className = 'bubble ' + (m.mine ? 'mine' : 'theirs');
                        el.innerHTML = '<span>' + esc(m.body) + '</span><small>' + (m.mine ? 'You' : esc(m.name)) + ' · ' + m.day + ', ' + m.time + '</small>';
                        log.appendChild(el);
                    });
                    if (stick || last === j.messages[j.messages.length - 1].id) log.scrollTop = log.scrollHeight;
                }
            }).catch(function () {}).finally(function () { busy = false; });
    }
    var form = document.getElementById('chatForm');
    if (form) form.addEventListener('submit', function (e) {
        e.preventDefault();
        var input = document.getElementById('chatBody'), text = input.value.trim();
        if (!text) return;
        input.value = '';
        UF.send(root.dataset.send, 'POST', { body: text }).then(function (j) {
            if (!j.ok) { UF.toast(j.message || 'Message not sent.', true); input.value = text; } else { pull(); }
        });
    });
    pull(); setInterval(pull, 4000);
})();
</script>
@endpush
