<section class="page-head">
  <div class="wrap">
    <h1>Contact Rezlo</h1>
    <p class="lede">Questions about an order, an account or a product? We reply to messages within one working day.</p>
  </div>
</section>
<section class="section section-tight">
  <div class="wrap contact">
    <div class="contact-details">
      <h2>Phone</h2><p><a href="tel:<?= e(config('company.phone_e164')) ?>"><?= e(config('company.phone')) ?></a><br>Monday to Friday, 8am to 5pm</p>
      <h2>Email</h2><p><a href="mailto:<?= e(config('company.email')) ?>"><?= e(config('company.email')) ?></a></p>
      <h2>Registered office</h2><p><?= implode('<br>', array_map('e', array_slice(config('company.address'), 0, 4))) ?></p>
      <p class="note">This is our office address. Goods are delivered from our suppliers' warehouses and can't be collected here.</p>
    </div>
    <form method="post" action="/contact" class="form-card">
      <?= csrf_field() ?>
      <h2>Send a message</h2>
      <div class="hp"><label for="website">Leave this empty</label><input id="website" name="website" tabindex="-1" autocomplete="off"></div>
      <div class="grid-2">
        <div class="field"><label for="name">Name</label><input id="name" name="name" required value="<?= old('name') ?>" autocomplete="name"></div>
        <div class="field"><label for="company">Company <span class="opt">optional</span></label><input id="company" name="company" value="<?= old('company') ?>" autocomplete="organization"></div>
      </div>
      <div class="grid-2">
        <div class="field"><label for="email">Email</label><input id="email" name="email" type="email" required value="<?= old('email') ?>" autocomplete="email"></div>
        <div class="field"><label for="phone">Phone <span class="opt">optional</span></label><input id="phone" name="phone" type="tel" value="<?= old('phone') ?>" autocomplete="tel"></div>
      </div>
      <div class="field"><label for="message">Message</label><textarea id="message" name="message" rows="6" required><?= old('message') ?></textarea></div>
      <p class="note">We use your details to reply to you, as explained in our <a href="/privacy">privacy policy</a>.</p>
      <button class="btn btn-primary" type="submit">Send message</button>
    </form>
  </div>
</section>
