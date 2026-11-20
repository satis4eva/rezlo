<h1>Enquiries and quote requests</h1>
<?php if (!$items): ?><p class="empty">No enquiries yet.</p><?php else: ?>
<div class="enq-list">
<?php foreach ($items as $q): ?>
  <article class="panel enq">
    <header>
      <h2><?= e($q['name']) ?><?= $q['company'] ? ', ' . e($q['company']) : '' ?></h2>
      <p><small><?= e($q['ref']) ?> · <?= e($q['kind'] === 'quote' ? 'Quote request' : 'Message') ?> · <?= e(date('j M Y H:i', strtotime($q['created_at']))) ?></small></p>
    </header>
    <p><a href="mailto:<?= e($q['email']) ?>?subject=<?= rawurlencode('Re: ' . $q['ref']) ?>"><?= e($q['email']) ?></a><?= $q['phone'] ? ' · ' . e($q['phone']) : '' ?><?= $q['postcode'] ? ' · ' . e($q['postcode']) : '' ?></p>
    <?php if ($q['service']): ?><p><strong><?= e($q['service']) ?></strong></p><?php endif; ?>
    <?php if ($q['message']): ?><p><?= nl2br(e($q['message'])) ?></p><?php endif; ?>
    <?php if ($q['items_json']): ?><ul class="plain"><?php foreach (json_decode($q['items_json'], true) as $i): ?><li><?= (int) $i['qty'] ?> × <?= e($i['name']) ?> (<?= e($i['sku']) ?>), list <?= money((int) $i['list_unit_price']) ?> each</li><?php endforeach; ?></ul><?php endif; ?>
    <form method="post" action="/admin/enquiries/<?= (int) $q['id'] ?>" class="inline-form"><?= csrf_field() ?>
      <label class="sr-only" for="s<?= (int) $q['id'] ?>">Status</label>
      <select id="s<?= (int) $q['id'] ?>" name="status"><?php foreach (['new', 'replied', 'quoted', 'won', 'lost', 'closed'] as $s): ?><option <?= $q['status'] === $s ? 'selected' : '' ?>><?= $s ?></option><?php endforeach; ?></select>
      <button class="btn btn-ghost" type="submit">Update</button>
    </form>
  </article>
<?php endforeach; ?>
</div>
<?php endif; ?>
