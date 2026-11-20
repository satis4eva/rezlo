<?php $cust = current_customer(); ?>
<section class="page-head">
  <div class="wrap"><h1>Your order</h1></div>
</section>
<section class="section section-tight">
  <div class="wrap">
  <?php if (!$lines): ?>
    <div class="empty">
      <h2>Your order is empty</h2>
      <p>Add products from the catalogue. Bulk price breaks are applied here as your quantities go up.</p>
      <a class="btn btn-primary" href="/shop">Browse the catalogue</a>
    </div>
  <?php else: ?>
    <div class="cart">
      <form method="post" action="/cart/update" class="cart-lines">
        <?= csrf_field() ?>
        <table class="table">
          <thead><tr><th scope="col">Product</th><th scope="col">Quantity</th><th scope="col" class="num">Unit ex VAT</th><th scope="col" class="num">Line ex VAT</th><th scope="col"><span class="sr-only">Remove</span></th></tr></thead>
          <tbody>
          <?php foreach ($lines as $l): $p = $l['product']; ?>
            <tr>
              <td>
                <div class="cart-prod">
                  <?= swatch((string) $p['swatch'], 'cart-swatch', (string) $p['image']) ?>
                  <div>
                    <a href="/product/<?= e($p['slug']) ?>"><?= e($p['name']) ?></a>
                    <small><?= e($p['sku']) ?> · <?= e($p['pack_note']) ?></small>
                    <?php if ($l['next']): ?><small class="hint">Add <?= (int) $l['next']['more'] ?> more to pay <?= money((int) $l['next']['price_pence']) ?> each</small><?php endif; ?>
                  </div>
                </div>
              </td>
              <td><label class="sr-only" for="q<?= (int) $p['id'] ?>">Quantity of <?= e($p['name']) ?></label>
                <input class="qty-input" id="q<?= (int) $p['id'] ?>" type="number" name="qty[<?= (int) $p['id'] ?>]" value="<?= (int) $l['qty'] ?>" min="0" step="<?= (int) $p['step'] ?>"> <span class="unit"><?= e(plural_unit($p['unit'])) ?></span></td>
              <td class="num"><?= money($l['unit']) ?><?php if ($l['unit'] < $l['base']): ?><br><s><?= money($l['base']) ?></s><?php endif; ?></td>
              <td class="num"><?= money($l['line']) ?></td>
              <td><button class="link-btn" name="remove" value="<?= (int) $p['id'] ?>">Remove</button></td>
            </tr>
          <?php endforeach; ?>
          </tbody>
        </table>
        <div class="cart-actions">
          <button class="btn btn-ghost" type="submit">Update quantities</button>
          <a href="/shop">Continue shopping</a>
        </div>
      </form>

      <aside class="summary" aria-label="Order summary">
        <h2>Summary</h2>
        <dl>
          <div><dt>Subtotal</dt><dd><?= money($totals['subtotal']) ?></dd></div>
          <?php if ($totals['savings'] > 0): ?><div class="save"><dt>Bulk savings included</dt><dd><?= money($totals['savings']) ?></dd></div><?php endif; ?>
          <div><dt>Delivery</dt><dd><?= $totals['delivery'] ? money($totals['delivery']) : 'Free' ?></dd></div>
          <div><dt>VAT (20%)</dt><dd><?= money($totals['vat']) ?></dd></div>
          <div class="total"><dt>Total</dt><dd><?= money($totals['total']) ?></dd></div>
        </dl>
        <?php if ($totals['to_free'] > 0): ?><p class="note">Add <?= money($totals['to_free']) ?> ex VAT for free mainland delivery.</p><?php endif; ?>
        <?php if ($totals['below_minimum']): ?><p class="note note-warn">The minimum trade order is <?= money_whole((int) config('shop.min_order_pence')) ?> ex VAT.</p><?php endif; ?>

        <?php if (!$cust): ?>
          <a class="btn btn-primary btn-block" href="/login">Sign in to check out</a>
          <a class="btn btn-ghost btn-block" href="/register">Open a trade account</a>
        <?php elseif (!customer_can_order($cust)): ?>
          <p class="note">Your trade account is waiting for approval, so online checkout isn't open yet.</p>
        <?php else: ?>
          <a class="btn btn-primary btn-block <?= $totals['below_minimum'] ? 'is-disabled' : '' ?>" href="<?= $totals['below_minimum'] ? '#' : '/checkout' ?>" <?= $totals['below_minimum'] ? 'aria-disabled="true"' : '' ?>>Go to checkout</a>
        <?php endif; ?>
        <a class="btn btn-ghost btn-block" href="/quote?from=cart">Send as a quote request</a>
      </aside>
    </div>
  <?php endif; ?>
  </div>
</section>
