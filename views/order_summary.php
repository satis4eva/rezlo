<?php /** @var array $o @var array $items */ ?>
<div class="order-box">
  <table class="table">
    <thead><tr><th scope="col">Product</th><th scope="col" class="num">Qty</th><th scope="col" class="num">Unit ex VAT</th><th scope="col" class="num">Line ex VAT</th></tr></thead>
    <tbody>
    <?php foreach ($items as $i): ?>
      <tr><td><?= e($i['name']) ?><br><small><?= e($i['sku']) ?></small></td><td class="num"><?= (int) $i['qty'] ?> <?= e($i['unit']) ?></td><td class="num"><?= money((int) $i['unit_price_pence']) ?></td><td class="num"><?= money((int) $i['line_pence']) ?></td></tr>
    <?php endforeach; ?>
    </tbody>
    <tfoot>
      <tr><td colspan="3">Subtotal</td><td class="num"><?= money((int) $o['subtotal_pence']) ?></td></tr>
      <tr><td colspan="3">Delivery</td><td class="num"><?= (int) $o['delivery_pence'] ? money((int) $o['delivery_pence']) : 'Free' ?></td></tr>
      <tr><td colspan="3">VAT</td><td class="num"><?= money((int) $o['vat_pence']) ?></td></tr>
      <tr class="total"><td colspan="3">Total</td><td class="num"><?= money((int) $o['total_pence']) ?></td></tr>
    </tfoot>
  </table>
  <div class="order-meta">
    <div><h3>Deliver to</h3><p><?= nl2br(e(implode("\n", array_filter([$o['ship_name'], $o['ship_company'], $o['ship_address1'], $o['ship_address2'], $o['ship_city'], $o['ship_postcode'], $o['ship_phone']])))) ?></p></div>
    <div><h3>Details</h3><p>Placed <?= e(date('j M Y, H:i', strtotime($o['created_at']))) ?><br>Status: <?= e(status_label($o['status'])) ?><?php if ($o['po_number']): ?><br>Your PO: <?= e($o['po_number']) ?><?php endif; ?><?php if ($o['delivery_pref']): ?><br>Preferred delivery: <?= e(date('j M Y', strtotime($o['delivery_pref']))) ?><?php endif; ?></p></div>
  </div>
</div>
