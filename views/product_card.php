<?php /** @var array $p */ $tiersCount = count(product_tiers($p)); ?>
<article class="pcard">
  <a href="/product/<?= e($p['slug']) ?>" class="pcard-link">
    <?= swatch((string) $p['swatch'], 'pcard-swatch', (string) $p['image']) ?>
    <div class="pcard-body">
      <h3><?= e($p['name']) ?></h3>
      <p class="pcard-pack"><?= e($p['pack_note']) ?></p>
      <?php if (config('shop.show_prices_to_guests') || current_customer()): ?>
      <p class="pcard-price">
        <strong><?= money((int) $p['price_pence']) ?></strong> <span>per <?= e($p['unit']) ?> ex VAT</span>
      </p>
      <?php if ($tiersCount > 1): ?><p class="pcard-break">Down to <?= money(lowest_price($p)) ?> in bulk</p><?php endif; ?>
      <?php else: ?>
      <p class="pcard-price"><span>Sign in for trade prices</span></p>
      <?php endif; ?>
    </div>
  </a>
</article>
