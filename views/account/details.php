<section class="page-head"><div class="wrap"><nav class="crumbs"><a href="/account">Your account</a> / Details</nav><h1>Account details</h1></div></section>
<section class="section section-tight">
  <div class="wrap narrow">
    <form method="post" action="/account/details" class="form-card">
      <?= csrf_field() ?>
      <div class="grid-2">
        <div class="field"><label for="contact_name">Your name</label><input id="contact_name" name="contact_name" required value="<?= e($c['contact_name']) ?>"></div>
        <div class="field"><label for="phone">Phone</label><input id="phone" name="phone" type="tel" value="<?= e($c['phone']) ?>"></div>
      </div>
      <div class="field"><label for="company_name">Company</label><input id="company_name" name="company_name" required value="<?= e($c['company_name']) ?>"></div>
      <div class="grid-2">
        <div class="field"><label for="company_number">Company number</label><input id="company_number" name="company_number" value="<?= e($c['company_number']) ?>"></div>
        <div class="field"><label for="vat_number">VAT number</label><input id="vat_number" name="vat_number" value="<?= e($c['vat_number']) ?>"></div>
      </div>
      <div class="field"><label for="business_type">Type of business</label><select id="business_type" name="business_type"><?php foreach (BUSINESS_TYPES as $t): ?><option <?= $c['business_type'] === $t ? 'selected' : '' ?>><?= e($t) ?></option><?php endforeach; ?></select></div>
      <div class="field"><label for="address1">Address</label><input id="address1" name="address1" value="<?= e($c['address1']) ?>"></div>
      <div class="field"><label for="address2" class="sr-only">Address line 2</label><input id="address2" name="address2" value="<?= e($c['address2']) ?>"></div>
      <div class="grid-2">
        <div class="field"><label for="city">Town or city</label><input id="city" name="city" value="<?= e($c['city']) ?>"></div>
        <div class="field"><label for="postcode">Postcode</label><input id="postcode" name="postcode" required value="<?= e($c['postcode']) ?>"></div>
      </div>
      <div class="field"><label for="password">New password <span class="opt">leave blank to keep your current one</span></label><input id="password" name="password" type="password" minlength="8" autocomplete="new-password"></div>
      <label class="check"><input type="checkbox" name="marketing" value="1" <?= $c['marketing'] ? 'checked' : '' ?>> <span>Email me trade offers and price updates</span></label>
      <p class="note">To change the email on your account, contact us.</p>
      <button class="btn btn-primary" type="submit">Save details</button>
    </form>
  </div>
</section>
