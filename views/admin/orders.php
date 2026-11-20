<h1>Orders</h1>
<form class="filters" method="get">
  <label class="sr-only" for="q">Search</label><input id="q" name="q" placeholder="Order ref, email, company or PO" value="<?= e($qs) ?>">
  <label class="sr-only" for="status">Status</label>
  <select id="status" name="status"><option value="">All statuses</option><?php foreach (ORDER_STATUSES as $s): ?><option value="<?= $s ?>" <?= $status === $s ? 'selected' : '' ?>><?= e(status_label($s)) ?></option><?php endforeach; ?></select>
  <button class="btn btn-ghost" type="submit">Filter</button>
</form>
<?php if (!$orders): ?><p class="empty">No orders match.</p><?php else: ?>
<table class="table">
  <thead><tr><th>Order</th><th>Date</th><th>Customer</th><th>Payment</th><th>Status</th><th class="num">Total</th></tr></thead>
  <tbody>
  <?php foreach ($orders as $o): ?>
  <tr><td><a href="/admin/orders/<?= e($o['ref']) ?>"><?= e($o['ref']) ?></a></td><td><?= e(date('j M Y H:i', strtotime($o['created_at']))) ?></td><td><?= e($o['ship_company'] ?: $o['ship_name']) ?><br><small><?= e($o['email']) ?></small></td><td><?= e(str_replace('_', ' ', (string) $o['payment_method'])) ?><br><small><?= e($o['payment_status']) ?></small></td><td><span class="pill pill-<?= e($o['status']) ?>"><?= e(status_label($o['status'])) ?></span></td><td class="num"><?= money((int) $o['total_pence']) ?></td></tr>
  <?php endforeach; ?>
  </tbody>
</table>
<?php endif; ?>
