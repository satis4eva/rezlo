<?php $c = current_customer(); ?>
<section class="page-head">
  <div class="wrap">
    <h1>Request a quote</h1>
    <p class="lede">For pallet quantities, whole-project supply, delivery outside mainland UK, or installation work. We reply within one working day.</p>
  </div>
</section>
<section class="section section-tight">
  <div class="wrap narrow">
    <form method="post" action="/quote" class="form-card">
      <?= csrf_field() ?>
      <div class="hp"><label for="website">Leave this empty</label><input id="website" name="website" tabindex="-1" autocomplete="off"></div>
      <?php if ($lines): ?>
      <fieldset>
        <legend>Products in your order</legend>
        <ul class="summary-lines">
          <?php foreach ($lines as $l): ?><li><span><?= (int) $l['qty'] ?> × <?= e($l['product']['name']) ?></span><span><?= money($l['line']) ?> ex VAT</span></li><?php endforeach; ?>
        </ul>
        <label class="check"><input type="checkbox" name="include_cart" value="1" checked> <span>Include these products in my quote request</span></label>
      </fieldset>
      <?php endif; ?>
      <div class="grid-2">
        <div class="field"><label for="name">Name</label><input id="name" name="name" required value="<?= old('name', $c['contact_name'] ?? '') ?>"></div>
        <div class="field"><label for="company">Company</label><input id="company" name="company" value="<?= old('company', $c['company_name'] ?? '') ?>"></div>
      </div>
      <div class="grid-2">
        <div class="field"><label for="email">Email</label><input id="email" name="email" type="email" required value="<?= old('email', $c['email'] ?? '') ?>"></div>
        <div class="field"><label for="phone">Phone</label><input id="phone" name="phone" type="tel" value="<?= old('phone', $c['phone'] ?? '') ?>"></div>
      </div>
      <div class="grid-2">
        <div class="field"><label for="postcode">Site postcode</label><input id="postcode" name="postcode" value="<?= old('postcode') ?>"></div>
        <div class="field"><label for="service">What do you need?</label>
          <select id="service" name="service">
            <?php $opts = array_merge(['Bulk or pallet supply', 'Project supply with phased deliveries'], array_column(services_list(), 0), ['Something else']); $sel = $_SESSION['old_now']['service'] ?? ($service ?: ''); foreach ($opts as $o): ?>
            <option <?= $sel === $o ? 'selected' : '' ?>><?= e($o) ?></option>
            <?php endforeach; ?>
          </select>
        </div>
      </div>
      <div class="field"><label for="message">Details</label><textarea id="message" name="message" rows="6" placeholder="Products, quantities or areas in m², finishes, timescale, and any site access details"><?= old('message') ?></textarea></div>
      <p class="note">Got drawings or a schedule? Mention it here and we'll reply with an email address to send them to.</p>
      <button class="btn btn-primary" type="submit">Send quote request</button>
    </form>
  </div>
</section>
