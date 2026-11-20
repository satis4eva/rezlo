<section class="page-head">
  <div class="wrap account-head">
    <div>
      <h1><?= e($c['company_name']) ?></h1>
      <p class="lede">Signed in as <?= e($c['contact_name']) ?> (<?= e($c['email']) ?>)</p>
    </div>
    <form method="post" action="/logout"><?= csrf_field() ?><button class="btn btn-ghost" type="submit">Sign out</button></form>
  </div>
</section>
<section class="section section-tight">
  <div class="wrap">
    <?php if ($c['status'] === 'pending'): ?>
    <div class="panel panel-note"><h2>Your account is being checked</h2><p>We usually approve trade accounts within one working day and will email you when you can order online. You can still build an order and send it as a quote request.</p></div>
    <?php elseif (in_array($c['status'], ['rejected', 'suspended'], true)): ?>
    <div class="panel panel-note"><h2>Online ordering isn't available on this account</h2><p>Call <?= e(config('company.phone')) ?> or email <?= e(config('company.email')) ?> and we'll help.</p></div>
    <?php endif; ?>
    <div class="account-grid">
      <div class="panel">
        <h2>Account</h2>
        <dl class="specs">
          <div><dt>Status</dt><dd><?= e(ucfirst($c['status'])) ?></dd></div>
          <div><dt>Credit terms</dt><dd><?= $c['credit_terms'] ? '30 days, limit ' . money((int) $c['credit_limit_pence']) : 'Card or bank transfer' ?></dd></div>
          <?php if ($c['credit_terms']): ?><div><dt>Outstanding on account</dt><dd><?= money($outstanding) ?></dd></div><?php endif; ?>
        </dl>
        <a href="/account/details">Edit details and password</a>
      </div>
      <div class="panel account-orders">
        <h2>Orders</h2>
        <?php if (!$orders): ?>
          <p>No orders yet. <a href="/shop">Browse the catalogue</a> to place your first one.</p>
        <?php else: ?>
        <table class="table">
          <thead><tr><th scope="col">Order</th><th scope="col">Date</th><th scope="col">Your PO</th><th scope="col">Status</th><th scope="col" class="num">Total</th></tr></thead>
          <tbody>
          <?php foreach ($orders as $o): ?>
            <tr><td><a href="/account/orders/<?= e($o['ref']) ?>"><?= e($o['ref']) ?></a></td><td><?= e(date('j M Y', strtotime($o['created_at']))) ?></td><td><?= e($o['po_number']) ?></td><td><span class="pill pill-<?= e($o['status']) ?>"><?= e(status_label($o['status'])) ?></span></td><td class="num"><?= money((int) $o['total_pence']) ?></td></tr>
          <?php endforeach; ?>
          </tbody>
        </table>
        <?php endif; ?>
      </div>
    </div>
  </div>
</section>
