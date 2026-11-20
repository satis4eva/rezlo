<section class="page-head">
  <div class="wrap">
    <h1>Apply for a trade account</h1>
    <p class="lede">Free for UK businesses. We usually approve accounts within one working day. Already have one? <a href="/login">Sign in</a>.</p>
  </div>
</section>
<section class="section section-tight">
  <div class="wrap narrow">
    <form method="post" action="/register" class="form-card">
      <?= csrf_field() ?>
      <fieldset>
        <legend>Your business</legend>
        <div class="field"><label for="company_name">Company or trading name</label><input id="company_name" name="company_name" required value="<?= old('company_name') ?>" autocomplete="organization"></div>
        <div class="grid-2">
          <div class="field"><label for="company_number">Company number <span class="opt">if limited</span></label><input id="company_number" name="company_number" value="<?= old('company_number') ?>"></div>
          <div class="field"><label for="vat_number">VAT number <span class="opt">if registered</span></label><input id="vat_number" name="vat_number" value="<?= old('vat_number') ?>"></div>
        </div>
        <div class="field"><label for="business_type">Type of business</label>
          <select id="business_type" name="business_type"><?php foreach (BUSINESS_TYPES as $t): ?><option <?= ($_SESSION['old_now']['business_type'] ?? '') === $t ? 'selected' : '' ?>><?= e($t) ?></option><?php endforeach; ?></select>
        </div>
        <div class="field"><label for="address1">Business address</label><input id="address1" name="address1" required value="<?= old('address1') ?>" autocomplete="address-line1"></div>
        <div class="field"><label for="address2" class="sr-only">Address line 2</label><input id="address2" name="address2" value="<?= old('address2') ?>" autocomplete="address-line2" placeholder="Address line 2 (optional)"></div>
        <div class="grid-2">
          <div class="field"><label for="city">Town or city</label><input id="city" name="city" required value="<?= old('city') ?>" autocomplete="address-level2"></div>
          <div class="field"><label for="postcode">Postcode</label><input id="postcode" name="postcode" required value="<?= old('postcode') ?>" autocomplete="postal-code"></div>
        </div>
      </fieldset>
      <fieldset>
        <legend>Your login</legend>
        <div class="grid-2">
          <div class="field"><label for="contact_name">Your name</label><input id="contact_name" name="contact_name" required value="<?= old('contact_name') ?>" autocomplete="name"></div>
          <div class="field"><label for="phone">Phone</label><input id="phone" name="phone" type="tel" required value="<?= old('phone') ?>" autocomplete="tel"></div>
        </div>
        <div class="field"><label for="email">Email</label><input id="email" name="email" type="email" required value="<?= old('email') ?>" autocomplete="email"></div>
        <div class="grid-2">
          <div class="field"><label for="password">Password</label><input id="password" name="password" type="password" minlength="8" required autocomplete="new-password"></div>
          <div class="field"><label for="password_confirm">Confirm password</label><input id="password_confirm" name="password_confirm" type="password" minlength="8" required autocomplete="new-password"></div>
        </div>
      </fieldset>
      <label class="check"><input type="checkbox" name="accept_terms" value="1" required> <span>I'm applying on behalf of a business and accept the <a href="/terms" target="_blank">Trade Terms and Conditions of Sale</a> and <a href="/privacy" target="_blank">privacy policy</a>.</span></label>
      <label class="check"><input type="checkbox" name="marketing" value="1" <?= !empty($_SESSION['old_now']['marketing']) ? 'checked' : '' ?>> <span>Email me trade offers and price updates. You can unsubscribe at any time.</span></label>
      <button class="btn btn-primary" type="submit">Apply for an account</button>
    </form>
  </div>
</section>
