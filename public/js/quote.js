// Live fee quote. A form with data-quote asks /quote when the places change and fills the fee boxes.
(function () {
    var form = document.querySelector('form[data-quote]');
    if (!form) return;

    var url = form.dataset.quote;
    var subtotal = Number(form.dataset.subtotal || 0);
    var toSel = form.querySelector('[name=location_id]');
    var fromSel = form.querySelector('[name=pickup_id]');
    var fee = form.querySelector('#fee'); // custom order / errand: the customer may offer more
    var fixedFrom = (form.dataset.from || '').split(',').filter(Boolean);
    var runnerFee = Number(form.dataset.base || 7), service = Number(form.dataset.service || 3);
    var timer;

    function peso(n) { return '₱' + (Math.round(n * 100) / 100).toLocaleString('en-PH', { maximumFractionDigits: 2 }); }
    function set(key, text) {
        form.querySelectorAll('[data-q=' + key + ']').forEach(function (el) { el.textContent = text; });
    }
    function render(q) {
        runnerFee = q.runner_fee; service = q.service_fee;
        var offered = runnerFee;
        if (fee) {
            fee.min = runnerFee;
            if (!fee.value || Number(fee.value) < runnerFee || fee.dataset.auto === '1') {
                fee.value = runnerFee; fee.dataset.auto = '1';
            }
            offered = Number(fee.value) || runnerFee;
        }
        set('distance', q.ready ? q.meters + ' m' : 'pick a place');
        set('runner', peso(offered));
        set('min', peso(runnerFee));
        set('service', peso(service));
        set('total', peso(subtotal + offered + service));
    }
    function ask() {
        var p = new URLSearchParams();
        if (toSel && toSel.value) p.append('to', toSel.value);
        var from = fromSel ? (fromSel.value ? [fromSel.value] : []) : fixedFrom;
        from.forEach(function (id) { p.append('from[]', id); });
        fetch(url + '?' + p.toString(), { headers: { 'Accept': 'application/json' } })
            .then(function (r) { return r.json(); }).then(render).catch(function () {});
    }
    function later() { clearTimeout(timer); timer = setTimeout(ask, 120); }

    if (toSel) toSel.addEventListener('change', later);
    if (fromSel) fromSel.addEventListener('change', later);
    if (fee) fee.addEventListener('input', function () { fee.dataset.auto = '0'; render({ runner_fee: runnerFee, service_fee: service, meters: 0, ready: !!(toSel && toSel.value) }); later(); });
    ask();
})();
