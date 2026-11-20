<?php
$paid = $o['payment_status'] === 'paid';
$needsCard = dp_card_available() && !$paid && in_array($o['status'], ['pending_payment', 'payment_failed'], true);
?>
<section class="page-head">
  <div class="wrap">
    <?php if ($paid): ?>
      <h1>Order <?= e($o['ref']) ?> is paid</h1>
      <p class="lede">Thanks. We've emailed a confirmation to <?= e($o['email']) ?> and we'll let you know when it's dispatched.</p>
    <?php elseif ($o['status'] === 'awaiting_transfer'): ?>
      <h1>Order <?= e($o['ref']) ?> is placed</h1>
      <p class="lede">Pay by bank transfer using the reference <strong><?= e($o['ref']) ?></strong>. We dispatch as soon as the payment reaches us.</p>
    <?php elseif ($o['status'] === 'on_account'): ?>
      <h1>Order <?= e($o['ref']) ?> is placed on your account</h1>
      <p class="lede">We'll invoice you on your 30-day terms. A confirmation is on its way to <?= e($o['email']) ?>.</p>
    <?php elseif ($o['status'] === 'payment_failed'): ?>
      <h1>The card payment didn't go through</h1>
      <p class="lede">Order <?= e($o['ref']) ?> is saved. Try the payment again, or call <?= e(config('company.phone')) ?> to pay by bank transfer.</p>
    <?php else: ?>
      <h1>We're confirming your payment</h1>
      <p class="lede">This usually takes a few seconds. Refresh this page, or check your email for confirmation.</p>
    <?php endif; ?>
  </div>
</section>
<section class="section section-tight">
  <div class="wrap narrow">
    <?php if ($o['status'] === 'awaiting_transfer'): $b = config('shop.bank_transfer'); ?>
    <div class="panel">
      <h2>Bank details</h2>
      <dl class="specs">
        <div><dt>Account name</dt><dd><?= e($b['account_name']) ?></dd></div>
        <div><dt>Sort code</dt><dd><?= e($b['sort_code'] ?: 'Sent by email') ?></dd></div>
        <div><dt>Account number</dt><dd><?= e($b['account_number'] ?: 'Sent by email') ?></dd></div>
        <div><dt>Reference</dt><dd><?= e($o['ref']) ?></dd></div>
        <div><dt>Amount</dt><dd><?= money((int) $o['total_pence']) ?></dd></div>
      </dl>
      <a class="btn btn-ghost" href="/order/<?= e($o['ref']) ?>/proforma" target="_blank">Print pro forma invoice</a>
    </div>
    <?php endif; ?>
    <?php if ($needsCard): ?>
    <form method="post" action="/order/<?= e($o['ref']) ?>/pay" class="panel"><?= csrf_field() ?><button class="btn btn-primary" type="submit">Pay <?= money((int) $o['total_pence']) ?> by card</button></form>
    <?php endif; ?>
    <?php partial('order_summary', ['o' => $o, 'items' => $items]); ?>
    <p><a href="/account">Go to your account</a></p>
  </div>
</section>
