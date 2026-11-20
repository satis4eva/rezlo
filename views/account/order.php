<section class="page-head">
  <div class="wrap account-head">
    <div>
      <nav class="crumbs"><a href="/account">Your account</a> / <?= e($o['ref']) ?></nav>
      <h1>Order <?= e($o['ref']) ?></h1>
    </div>
    <div class="row-actions">
      <form method="post" action="/account/orders/<?= e($o['ref']) ?>/reorder"><?= csrf_field() ?><button class="btn btn-ghost" type="submit">Reorder these items</button></form>
      <?php if ($o['payment_method'] === 'bank_transfer' && $o['payment_status'] !== 'paid'): ?><a class="btn btn-ghost" href="/order/<?= e($o['ref']) ?>/proforma" target="_blank">Pro forma invoice</a><?php endif; ?>
      <?php if ($o['payment_status'] !== 'paid' && in_array($o['status'], ['pending_payment', 'payment_failed', 'awaiting_transfer'], true)): ?>
      <form method="post" action="/order/<?= e($o['ref']) ?>/pay"><?= csrf_field() ?><button class="btn btn-primary" type="submit">Pay by card</button></form>
      <?php endif; ?>
    </div>
  </div>
</section>
<section class="section section-tight"><div class="wrap narrow"><?php partial('order_summary', ['o' => $o, 'items' => $items]); ?></div></section>
