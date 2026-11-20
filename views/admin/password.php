<h1>Change your password</h1>
<form method="post" action="/admin/password" class="form-card narrow-form">
  <?= csrf_field() ?>
  <div class="field"><label for="password">New password (10+ characters)</label><input id="password" name="password" type="password" minlength="10" required autocomplete="new-password"></div>
  <div class="field"><label for="password_confirm">Confirm</label><input id="password_confirm" name="password_confirm" type="password" minlength="10" required autocomplete="new-password"></div>
  <button class="btn btn-primary" type="submit">Save password</button>
</form>
