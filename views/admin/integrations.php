<h1>Payments and DBS portal</h1>
<div class="admin-cols">
  <section class="panel">
    <h2>DPayments card payments</h2>
    <dl class="specs">
      <div><dt>Mode</dt><dd><?= e(dp_mode()) ?><?= dp_mode() === 'simulate' ? ' (built-in test checkout)' : '' ?></dd></div>
      <div><dt>API</dt><dd><?= dp_mode() === 'simulate' ? '—' : e(dp_base()) ?></dd></div>
      <div><dt>Merchant ID</dt><dd><?= config('dpayments.merchant_id') ? 'Set' : 'Not set' ?></dd></div>
      <div><dt>API key</dt><dd><?= config('dpayments.api_key') ? 'Set' : 'Not set' ?></dd></div>
      <div><dt>Webhook secret</dt><dd><?= config('dpayments.webhook_secret') ? 'Set' : 'Not set' ?></dd></div>
      <div><dt>Webhook URL to give DPayments</dt><dd><code><?= e(abs_url('/webhooks/dpayments')) ?></code></dd></div>
    </dl>
    <p class="note">Keys are set in config.php on the server, never in the admin, so they can't leak through a browser. Rezlo follows the DBS Commerce DPayments contract (docs/dpayments-gateway-api.md on master.dbsgroup.io).</p>
    <form method="post"><?= csrf_field() ?><input type="hidden" name="action" value="dp_ping"><button class="btn btn-ghost" type="submit">Test DPayments connection</button></form>
  </section>
  <section class="panel">
    <h2>DBS Group master portal</h2>
    <dl class="specs">
      <div><dt>Link</dt><dd><?= portal_enabled() ? 'On' : 'Off' ?></dd></div>
      <div><dt>Portal</dt><dd><?= e(config('portal.base_url')) ?></dd></div>
      <div><dt>Brand ID</dt><dd><?= e(config('portal.brand')) ?></dd></div>
      <div><dt>Events sent to</dt><dd><code><?= e(portal_endpoint()) ?></code></dd></div>
      <div><dt>Portal reads Rezlo at</dt><dd><code><?= e(abs_url('/api/v1/')) ?></code></dd></div>
      <div><dt>Inbound key</dt><dd><?= config('portal.inbound_key') ? 'Set' : 'Not set' ?></dd></div>
      <div><dt>Master portal health check</dt><dd><?= config('portal.dbs_public_key') ? 'Enabled (' . e(abs_url('/api/platform/status')) . ')' : 'Add dbs_public_key in config.php' ?></dd></div>
      <div><dt>Events waiting</dt><dd><?= $pendingEvents ?></dd></div>
    </dl>
    <div class="row-actions">
      <form method="post"><?= csrf_field() ?><input type="hidden" name="action" value="ping"><button class="btn btn-primary" type="submit">Send test event</button></form>
      <form method="post"><?= csrf_field() ?><input type="hidden" name="action" value="flush"><button class="btn btn-ghost" type="submit">Retry waiting events</button></form>
    </div>
  </section>
</div>
<section class="panel">
  <h2>Event outbox</h2>
  <?php if (!$outbox): ?><p>No events yet.</p><?php else: ?>
  <table class="table"><thead><tr><th>#</th><th>Event</th><th>Created</th><th>Attempts</th><th>Result</th></tr></thead><tbody>
  <?php foreach ($outbox as $r): ?><tr><td><?= (int) $r['id'] ?></td><td><?= e($r['event']) ?></td><td><?= e($r['created_at']) ?></td><td><?= (int) $r['attempts'] ?></td><td><?= $r['sent_at'] ? 'Delivered ' . e($r['sent_at']) : e($r['last_error'] ?: 'Waiting') ?></td></tr><?php endforeach; ?>
  </tbody></table><?php endif; ?>
</section>
<section class="panel">
  <h2>Recent payment activity</h2>
  <?php if (!$log): ?><p>Nothing yet.</p><?php else: ?>
  <table class="table"><thead><tr><th>When</th><th>Source</th><th>Event</th><th>Order</th></tr></thead><tbody>
  <?php foreach ($log as $l): ?><tr><td><?= e($l['created_at']) ?></td><td><?= e($l['source']) ?></td><td><?= e($l['event']) ?></td><td><?= $l['order_id'] ? e((string) val('SELECT ref FROM orders WHERE id = ?', [$l['order_id']])) : '—' ?></td></tr><?php endforeach; ?>
  </tbody></table><?php endif; ?>
</section>
