<h1>Trade customers</h1>
<nav class="cat-tabs"><?php foreach (['' => 'All', 'pending' => 'Waiting for approval', 'approved' => 'Approved', 'rejected' => 'Rejected', 'suspended' => 'Suspended'] as $k => $v): ?><a href="/admin/customers<?= $k ? '?status=' . $k : '' ?>" <?= $status === $k ? 'aria-current="page"' : '' ?>><?= $v ?></a><?php endforeach; ?></nav>
<?php if (!$customers): ?><p class="empty">No customers here yet.</p><?php else: ?>
<table class="table">
  <thead><tr><th>Company</th><th>Contact</th><th>Type</th><th>Status</th><th>Credit</th><th>Joined</th></tr></thead>
  <tbody>
  <?php foreach ($customers as $c): ?>
  <tr><td><a href="/admin/customers/<?= (int) $c['id'] ?>"><?= e($c['company_name']) ?></a><br><small><?= e($c['company_number']) ?></small></td><td><?= e($c['contact_name']) ?><br><small><?= e($c['email']) ?></small></td><td><?= e($c['business_type']) ?></td><td><span class="pill pill-<?= e($c['status']) ?>"><?= e(ucfirst($c['status'])) ?></span></td><td><?= $c['credit_terms'] ? money((int) $c['credit_limit_pence']) : '—' ?></td><td><?= e(date('j M Y', strtotime($c['created_at']))) ?></td></tr>
  <?php endforeach; ?>
  </tbody>
</table>
<?php endif; ?>
