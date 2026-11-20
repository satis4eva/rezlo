<!DOCTYPE html>
<html lang="en-GB">
<head>
<meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
<title>DPayments test checkout</title>
<meta name="robots" content="noindex">
<link rel="stylesheet" href="<?= asset('css/style.css') ?>">
</head>
<body class="sim">
<div class="sim-card">
  <p class="sim-badge">Test mode — no money will move</p>
  <h1>DPayments checkout</h1>
  <p>Paying <strong><?= e(config('company.name')) ?></strong></p>
  <p class="sim-amount"><?= money((int) $o['total_pence']) ?></p>
  <p>Order <?= e($o['ref']) ?></p>
  <p class="note">This page stands in for the DPayments hosted payment page until live API keys are added in config.php. Choose an outcome to test the order flow, webhook handling and portal events.</p>
  <form method="post" action="/pay/simulate" class="sim-actions">
    <?= csrf_field() ?>
    <input type="hidden" name="order" value="<?= e($o['ref']) ?>">
    <input type="hidden" name="session" value="<?= e($session) ?>">
    <button class="btn btn-primary" name="outcome" value="approve">Approve payment</button>
    <button class="btn btn-ghost" name="outcome" value="decline">Decline payment</button>
  </form>
</div>
</body>
</html>
