<section class="section">
  <div class="wrap narrow">
    <form method="post" action="/reset-password" class="form-card">
      <?= csrf_field() ?>
      <input type="hidden" name="token" value="<?= e($token) ?>">
      <h1>Choose a new password</h1>
      <div class="field"><label for="password">New password</label><input id="password" name="password" type="password" minlength="8" required autocomplete="new-password"></div>
      <div class="field"><label for="password_confirm">Confirm new password</label><input id="password_confirm" name="password_confirm" type="password" minlength="8" required autocomplete="new-password"></div>
      <button class="btn btn-primary" type="submit">Save new password</button>
    </form>
  </div>
</section>
