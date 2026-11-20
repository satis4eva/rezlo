<section class="page-head"><div class="wrap"><h1>Checkout</h1></div></section>
<section class="section section-tight">
  <div class="wrap">
    <form method="post" action="/checkout" class="checkout">
      <?= csrf_field() ?>
      <div class="checkout-main">
        <fieldset>
          <legend>Delivery</legend>
          <div class="grid-2">
            <div class="field"><label for="ship_name">Site contact name</label><input id="ship_name" name="ship_name" required value="<?= old('ship_name', $c['contact_name']) ?>" autocomplete="name"></div>
            <div class="field"><label for="ship_phone">Site phone</label><input id="ship_phone" name="ship_phone" type="tel" required value="<?= old('ship_phone', $c['phone']) ?>" autocomplete="tel"></div>
          </div>
          <div class="field"><label for="ship_company">Company</label><input id="ship_company" name="ship_company" value="<?= old('ship_company', $c['company_name']) ?>" autocomplete="organization"></div>
          <div class="field"><label for="ship_address1">Address line 1</label><input id="ship_address1" name="ship_address1" required value="<?= old('ship_address1', $c['address1']) ?>" autocomplete="address-line1"></div>
          <div class="field"><label for="ship_address2">Address line 2 <span class="opt">optional</span></label><input id="ship_address2" name="ship_address2" value="<?= old('ship_address2', $c['address2']) ?>" autocomplete="address-line2"></div>
          <div class="grid-2">
            <div class="field"><label for="ship_city">Town or city</label><input id="ship_city" name="ship_city" required value="<?= old('ship_city', $c['city']) ?>" autocomplete="address-level2"></div>
            <div class="field"><label for="ship_postcode">Postcode</label><input id="ship_postcode" name="ship_postcode" required value="<?= old('ship_postcode', $c['postcode']) ?>" autocomplete="postal-code"></div>
          </div>
          <div class="grid-2">
            <div class="field"><label for="delivery_pref">Preferred delivery date <span class="opt">optional</span></label><input id="delivery_pref" name="delivery_pref" type="date" min="<?= date('Y-m-d', strtotime('+2 weekdays')) ?>" value="<?= old('delivery_pref') ?>"></div>
            <div class="field"><label for="po_number">Your PO number <span class="opt">optional</span></label><input id="po_number" name="po_number" value="<?= old('po_number') ?>"></div>
          </div>
          <div class="field"><label for="notes">Delivery notes <span class="opt">optional</span></label><textarea id="notes" name="notes" rows="3" placeholder="Access, tail-lift needed, site hours, restrictions"><?= old('notes') ?></textarea></div>
        </fieldset>

        <fieldset>
          <legend>Payment</legend>
          <?php if (!$methods): ?><p class="flash flash-info">Online payment isn't available for this order yet. <a href="/quote?from=cart">Send it as a quote request</a> and we'll confirm the order and payment with you directly.</p><?php endif; ?>
          <?php $sel = $_SESSION['old_now']['payment_method'] ?? array_key_first($methods); foreach ($methods as $k => $label): ?>
          <label class="radio"><input type="radio" name="payment_method" value="<?= e($k) ?>" <?= $sel === $k ? 'checked' : '' ?>> <span><?= e($label) ?></span></label>
          <?php endforeach; ?>
          <?php if (isset($methods['card'])): ?><p class="note">Card payments are taken on the secure DPayments payment page. We never see or store your card details.</p><?php endif; ?>
        </fieldset>

        <label class="check"><input type="checkbox" name="accept_terms" value="1" required> <span>I confirm I'm buying for business purposes and accept the <a href="/terms" target="_blank">Trade Terms and Conditions of Sale</a>, <a href="/delivery" target="_blank">delivery</a> and <a href="/returns" target="_blank">returns</a> policies.</span></label>
      </div>

      <aside class="summary" aria-label="Order summary">
        <h2>Your order</h2>
        <ul class="summary-lines">
          <?php foreach ($lines as $l): ?>
          <li><span><?= (int) $l['qty'] ?> × <?= e($l['product']['name']) ?></span><span><?= money($l['line']) ?></span></li>
          <?php endforeach; ?>
        </ul>
        <dl>
          <div><dt>Subtotal</dt><dd><?= money($totals['subtotal']) ?></dd></div>
          <div><dt>Delivery</dt><dd><?= $totals['delivery'] ? money($totals['delivery']) : 'Free' ?></dd></div>
          <div><dt>VAT (20%)</dt><dd><?= money($totals['vat']) ?></dd></div>
          <div class="total"><dt>Total</dt><dd><?= money($totals['total']) ?></dd></div>
        </dl>
        <button class="btn btn-primary btn-block" type="submit" <?= $methods ? '' : 'disabled' ?>>Place order</button>
        <p class="note">Highlands, islands, Northern Ireland and offshore postcodes are quoted separately.</p>
      </aside>
    </form>
  </div>
</section>
