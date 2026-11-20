(function () {
  // Mobile menu
  var toggle = document.querySelector('.nav-toggle');
  var nav = document.getElementById('site-nav');
  if (toggle && nav) {
    toggle.addEventListener('click', function () {
      var open = nav.classList.toggle('is-open');
      toggle.setAttribute('aria-expanded', open ? 'true' : 'false');
    });
  }

  // Product page: move along the tape as quantity changes
  var form = document.querySelector('.buy-form');
  var tape = document.querySelector('.tape-live');
  if (!form) return;
  var qtyInput = form.querySelector('input[name="qty"]');
  var priceEl = document.querySelector('[data-unit-price]');
  var lineEl = form.querySelector('[data-line-total]');
  var tiers = tape ? JSON.parse(tape.getAttribute('data-tiers')) : null;
  var moq = parseInt(form.dataset.moq, 10) || 1;
  var step = parseInt(form.dataset.step, 10) || 1;
  var fmt = function (p) { return '£' + (p / 100).toLocaleString('en-GB', { minimumFractionDigits: 2, maximumFractionDigits: 2 }); };
  var basePrice = priceEl ? Math.round(parseFloat(priceEl.textContent.replace(/[£,]/g, '')) * 100) : 0;

  function update() {
    var q = parseInt(qtyInput.value, 10) || 0;
    var eff = Math.max(q, moq);
    eff = Math.ceil(eff / step) * step;
    var price = basePrice, active = 0;
    if (tiers) {
      tiers.forEach(function (t, i) { if (eff >= t.min_qty) { price = t.price_pence; active = i; } });
      var marks = tape.querySelectorAll('.tape-marks li');
      marks.forEach(function (li, i) { li.classList.toggle('is-active', i === active); });
      tape.querySelector('.tape-fill').style.setProperty('--to', ((active + 0.5) / tiers.length * 100) + '%');
    }
    if (priceEl) priceEl.textContent = fmt(price);
    if (lineEl) {
      var txt = 'Line total ' + fmt(price * eff) + ' ex VAT';
      if (eff !== q && q > 0) txt += ' (rounded to ' + eff + ')';
      var next = tiers && tiers[active + 1];
      if (next) txt += '. Order ' + (next.min_qty - eff) + ' more for ' + fmt(next.price_pence) + ' each.';
      lineEl.textContent = txt;
    }
  }
  qtyInput.addEventListener('input', update);
  update();
})();
