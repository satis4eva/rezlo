<p class="crumbs"><a href="/admin/customers">Trade customers</a> / <?= e($c['company_name']) ?></p>
<h1><?= e($c['company_name']) ?> <span class="pill pill-<?= e($c['status']) ?>"><?= e(ucfirst($c['status'])) ?></span></h1>
<div class="admin-cols">
  <section class="panel">
    <h2>Details</h2>
    <dl class="specs">
      <div><dt>Contact</dt><dd><?= e($c['contact_name']) ?></dd></div>
      <div><dt>Email</dt><dd><a href="mailto:<?= e($c['email']) ?>"><?= e($c['email']) ?></a></dd></div>
      <div><dt>Phone</dt><dd><?= e($c['phone']) ?></dd></div>
      <div><dt>Company number</dt><dd><?php if ($c['company_number']): ?><a href="https://find-and-update.company-information.service.gov.uk/company/<?= e(urlencode($c['company_number'])) ?>" target="_blank" rel="noopener"><?= e($c['company_number']) ?></a><?php else: ?>—<?php endif; ?></dd></div>
      <div><dt>VAT number</dt><dd><?= e($c['vat_number'] ?: '—') ?></dd></div>
      <div><dt>Business type</dt><dd><?= e($c['business_type']) ?></dd></div>
      <div><dt>Address</dt><dd><?= e(implode(', ', array_filter([$c['address1'], $c['address2'], $c['city'], $c['postcode']]))) ?></dd></div>
      <div><dt>Marketing emails</dt><dd><?= $c['marketing'] ? 'Yes' : 'No' ?></dd></div>
      <div><dt>Applied</dt><dd><?= e($c['created_at']) ?></dd></div>
    </dl>
  </section>
  <section class="panel">
    <h2>Account settings</h2>
    <form method="post" class="stack"><?= csrf_field() ?>
      <label for="status">Status</label>
      <select id="status" name="status"><?php foreach (['pending', 'approved', 'rejected', 'suspended'] as $s): ?><option value="<?= $s ?>" <?= $c['status'] === $s ? 'selected' : '' ?>><?= ucfirst($s) ?></option><?php endforeach; ?></select>
      <label class="check"><input type="checkbox" name="credit_terms" value="1" <?= $c['credit_terms'] ? 'checked' : '' ?>> <span>Allow 30-day account terms</span></label>
      <label for="credit_limit">Credit limit, £ inc VAT</label>
      <input id="credit_limit" name="credit_limit" type="number" step="0.01" min="0" value="<?= number_format($c['credit_limit_pence'] / 100, 2, '.', '') ?>">
      <p class="note">Outstanding on account: <?= money($outstanding) ?>. Approving sends the customer an email.</p>
      <button class="btn btn-primary" type="submit">Save</button>
    </form>
  </section>
</div>
<section class="panel">
  <h2>Orders</h2>
  <?php if (!$orders): ?><p>No orders yet.</p><?php else: ?>
  <table class="table"><thead><tr><th>Order</th><th>Date</th><th>Status</th><th class="num">Total</th></tr></thead><tbody>
  <?php foreach ($orders as $o): ?><tr><td><a href="/admin/orders/<?= e($o['ref']) ?>"><?= e($o['ref']) ?></a></td><td><?= e(date('j M Y', strtotime($o['created_at']))) ?></td><td><?= e(status_label($o['status'])) ?></td><td class="num"><?= money((int) $o['total_pence']) ?></td></tr><?php endforeach; ?>
  </tbody></table><?php endif; ?>
</section>
