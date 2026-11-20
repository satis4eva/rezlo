<h1>Dashboard</h1>
<?php if (dp_mode() === 'simulate'): ?><div class="flash flash-info">Card payments are in test mode. Add your DPayments keys in config.php and set the mode to sandbox, then live. <a href="/admin/integrations">Check the setup</a></div><?php endif; ?>
<div class="stats">
  <?php foreach ($stats as $label => $v): ?><div class="stat"><span class="stat-v"><?= e((string) $v) ?></span><span class="stat-l"><?= e($label) ?></span></div><?php endforeach; ?>
</div>
<div class="admin-cols">
  <section class="panel">
    <h2>Latest orders</h2>
    <?php if (!$orders): ?><p>No orders yet.</p><?php else: ?>
    <table class="table"><thead><tr><th>Order</th><th>Customer</th><th>Status</th><th class="num">Total</th></tr></thead><tbody>
    <?php foreach ($orders as $o): ?><tr><td><a href="/admin/orders/<?= e($o['ref']) ?>"><?= e($o['ref']) ?></a></td><td><?= e($o['ship_company'] ?: $o['email']) ?></td><td><span class="pill pill-<?= e($o['status']) ?>"><?= e(status_label($o['status'])) ?></span></td><td class="num"><?= money((int) $o['total_pence']) ?></td></tr><?php endforeach; ?>
    </tbody></table><?php endif; ?>
  </section>
  <section class="panel">
    <h2>Accounts waiting for approval</h2>
    <?php if (!$pending): ?><p>None right now.</p><?php else: ?>
    <ul class="plain"><?php foreach ($pending as $c): ?><li><a href="/admin/customers/<?= (int) $c['id'] ?>"><?= e($c['company_name']) ?></a> <small><?= e($c['business_type']) ?>, <?= e(date('j M', strtotime($c['created_at']))) ?></small></li><?php endforeach; ?></ul><?php endif; ?>
  </section>
</div>
