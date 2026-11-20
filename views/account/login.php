<section class="section">
  <div class="wrap auth">
    <form method="post" action="/login" class="form-card">
      <?= csrf_field() ?>
      <h1>Sign in</h1>
      <div class="field"><label for="email">Email</label><input id="email" name="email" type="email" required value="<?= old('email') ?>" autocomplete="email"></div>
      <div class="field"><label for="password">Password</label><input id="password" name="password" type="password" required autocomplete="current-password"></div>
      <button class="btn btn-primary btn-block" type="submit">Sign in</button>
      <p class="note"><a href="/forgot-password">Forgotten your password?</a></p>
    </form>
    <div class="auth-side">
      <h2>New to Rezlo?</h2>
      <p>Open a free trade account to order online at trade prices, pay on account and reorder in one click.</p>
      <a class="btn btn-ghost" href="/register">Apply for a trade account</a>
    </div>
  </div>
</section>
