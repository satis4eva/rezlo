<?php
$cust = current_customer();
$showPrice = config('shop.show_prices_to_guests') || $cust;
$startQty = normalise_qty($p, (int) $p['moq']);
?>
<section class="section section-tight">
  <div class="wrap">
    <nav class="crumbs" aria-label="Breadcrumb"><a href="/">Home</a> / <a href="/shop">Shop</a> / <a href="/shop/<?= e($p['category_slug']) ?>"><?= e($p['category_name']) ?></a></nav>
    <div class="product">
      <div class="product-media">
        <?= swatch((string) $p['swatch'], 'product-swatch', (string) $p['image']) ?>
      </div>
      <div class="product-info">
        <h1><?= e($p['name']) ?></h1>
        <p class="product-short"><?= e($p['short']) ?></p>
        <dl class="specs">
          <div><dt>SKU</dt><dd><?= e($p['sku']) ?></dd></div>
          <div><dt>Sold per</dt><dd><?= e($p['unit']) ?><?= $p['pack_note'] ? ', ' . e($p['pack_note']) : '' ?></dd></div>
          <div><dt>Minimum order</dt><dd><?= (int) $p['moq'] ?> <?= e(plural_unit($p['unit'])) ?><?= (int) $p['step'] > 1 ? ', in multiples of ' . (int) $p['step'] : '' ?></dd></div>
          <?php if ((int) $p['pallet_qty']): ?><div><dt>Full pallet</dt><dd><?= (int) $p['pallet_qty'] ?> <?= e(plural_unit($p['unit'])) ?></dd></div><?php endif; ?>
          <div><dt>Lead time</dt><dd><?= (int) $p['stock'] > 0 ? e($p['lead_time']) : 'Made to order — call for lead time' ?></dd></div>
        </dl>

        <?php if ($showPrice): ?>
        <div class="buy">
          <p class="buy-price"><strong data-unit-price><?= money(unit_price($p, $startQty)) ?></strong> per <?= e($p['unit']) ?> ex VAT</p>
          <?php if (count($tiers) > 1): partial('tape', ['tiers' => $tiers, 'unit' => plural_unit($p['unit']), 'qty' => $startQty, 'interactive' => true]); endif; ?>
          <form method="post" action="/cart/add" class="buy-form" data-moq="<?= (int) $p['moq'] ?>" data-step="<?= (int) $p['step'] ?>">
            <?= csrf_field() ?>
            <input type="hidden" name="product_id" value="<?= (int) $p['id'] ?>">
            <input type="hidden" name="return" value="product">
            <label for="qty">Quantity (<?= e(plural_unit($p['unit'])) ?>)</label>
            <div class="qty-row">
              <input id="qty" name="qty" type="number" inputmode="numeric" min="<?= (int) $p['moq'] ?>" step="<?= (int) $p['step'] ?>" value="<?= $startQty ?>" required>
              <button class="btn btn-primary" type="submit">Add to order</button>
            </div>
            <p class="buy-line" data-line-total>Line total <?= money(unit_price($p, $startQty) * $startQty) ?> ex VAT</p>
          </form>
          <?php if ($cust && !customer_can_order($cust)): ?><p class="note">Your trade account is waiting for approval. You can build an order now and send it as a quote request.</p><?php endif; ?>
          <?php if (!$cust): ?><p class="note"><a href="/register">Open a trade account</a> to order online, or <a href="/quote">request a quote</a>.</p><?php endif; ?>
        </div>
        <?php else: ?>
        <div class="buy"><p><a class="btn btn-primary" href="/login">Sign in for trade prices</a></p></div>
        <?php endif; ?>

        <div class="prose product-desc">
          <h2>Details</h2>
          <p><?= nl2br(e($p['description'])) ?></p>
          <p>Need more than a pallet, a colour match or a split delivery? <a href="/quote">Ask for a project price</a>.</p>
        </div>
      </div>
    </div>
  </div>
</section>
<?php if ($related): ?>
<section class="section section-alt">
  <div class="wrap">
    <div class="section-head"><h2>Also in <?= e($p['category_name']) ?></h2><a href="/shop/<?= e($p['category_slug']) ?>">See all</a></div>
    <div class="pgrid"><?php foreach ($related as $r) partial('product_card', ['p' => $r]); ?></div>
  </div>
</section>
<?php endif; ?>
<script type="application/ld+json"><?= json_encode([
    '@context' => 'https://schema.org', '@type' => 'Product', 'name' => $p['name'], 'sku' => $p['sku'], 'description' => $p['short'],
    'offers' => ['@type' => 'AggregateOffer', 'priceCurrency' => 'GBP', 'lowPrice' => number_format(lowest_price($p) / 100, 2, '.', ''), 'highPrice' => number_format($p['price_pence'] / 100, 2, '.', ''), 'availability' => 'https://schema.org/InStock'],
], JSON_UNESCAPED_SLASHES) ?></script>
