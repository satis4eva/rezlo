<p class="crumbs"><a href="/admin/orders">Orders</a> / <?= e($o['ref']) ?></p>
<h1>Order <?= e($o['ref']) ?> <span class="pill pill-<?= e($o['status']) ?>"><?= e(status_label($o['status'])) ?></span></h1>
<div class="admin-cols">
  <div><?php partial('order_summary', ['o' => $o, 'items' => $items]); ?>
    <?php if ($o['notes']): ?><section class="panel"><h2>Notes</h2><p><?= nl2br(e($o['notes'])) ?></p></section><?php endif; ?>
  </div>
  <div>
    <section class="panel">
      <h2>Update</h2>
      <form method="post" class="stack"><?= csrf_field() ?><input type="hidden" name="action" value="status">
        <label for="status">Status</label>
        <select id="status" name="status"><?php foreach (ORDER_STATUSES as $s): ?><option value="<?= $s ?>" <?= $o['status'] === $s ? 'selected' : '' ?>><?= e(status_label($s)) ?></option><?php endforeach; ?></select>
        <button class="btn btn-primary" type="submit">Save status</button>
        <p class="note">Dispatched, delivered and cancelled email the customer.</p>
      </form>
      <?php if ($o['payment_status'] !== 'paid'): ?>
      <form method="post" class="stack"><?= csrf_field() ?><input type="hidden" name="action" value="mark_paid"><button class="btn btn-ghost" type="submit">Mark as paid (bank transfer received)</button></form>
      <?php endif; ?>
      <form method="post" class="stack"><?= csrf_field() ?><input type="hidden" name="action" value="note"><label for="note">Add internal note</label><textarea id="note" name="note" rows="2" required></textarea><button class="btn btn-ghost" type="submit">Add note</button></form>
    </section>
    <section class="panel">
      <h2>Payment</h2>
      <dl class="specs">
        <div><dt>Method</dt><dd><?= e(str_replace('_', ' ', (string) $o['payment_method'])) ?></dd></div>
        <div><dt>Status</dt><dd><?= e($o['payment_status']) ?></dd></div>
        <div><dt>Gateway ref</dt><dd><?= e($o['gateway_ref'] ?: '—') ?></dd></div>
        <div><dt>Paid at</dt><dd><?= e($o['paid_at'] ?: '—') ?></dd></div>
      </dl>
      <p><a href="/order/<?= e($o['ref']) ?>/proforma" target="_blank">Pro forma invoice</a></p>
    </section>
    <?php if ($customer): ?>
    <section class="panel"><h2>Customer</h2><p><a href="/admin/customers/<?= (int) $customer['id'] ?>"><?= e($customer['company_name']) ?></a><br><?= e($customer['contact_name']) ?><br><?= e($customer['email']) ?><br><?= e($customer['phone']) ?></p></section>
    <?php endif; ?>
    <?php if ($log): ?>
    <section class="panel"><h2>Payment log</h2><ul class="plain log"><?php foreach ($log as $l): ?><li><small><?= e($l['created_at']) ?> · <?= e($l['source']) ?></small><br><?= e($l['event']) ?></li><?php endforeach; ?></ul></section>
    <?php endif; ?>
  </div>
</div>
