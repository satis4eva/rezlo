<div class="admin-login">
  <form method="post" action="/admin/login" class="form-card">
    <?= csrf_field() ?>
    <div class="logo"><?php partial('logo'); ?></div>
    <h1>Admin sign in</h1>
    <div class="field"><label for="email">Email</label><input id="email" name="email" type="email" required autocomplete="username"></div>
    <div class="field"><label for="password">Password</label><input id="password" name="password" type="password" required autocomplete="current-password"></div>
    <button class="btn btn-primary btn-block" type="submit">Sign in</button>
  </form>
</div>
