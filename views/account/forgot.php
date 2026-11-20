<section class="section">
  <div class="wrap narrow">
    <form method="post" action="/forgot-password" class="form-card">
      <?= csrf_field() ?>
      <h1>Reset your password</h1>
      <p>Enter the email on your trade account and we'll send you a link to choose a new password.</p>
      <div class="field"><label for="email">Email</label><input id="email" name="email" type="email" required autocomplete="email"></div>
      <button class="btn btn-primary" type="submit">Send reset link</button>
    </form>
  </div>
</section>
