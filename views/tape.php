<?php
/** @var array $tiers  @var string $unit  @var int $qty  @var bool $interactive */
$qty = $qty ?? 0;
$active = 0;
foreach ($tiers as $i => $t) { if ($qty >= $t['min_qty']) $active = $i; }
$base = $tiers[0]['price_pence'];
?>
<div class="tape<?= !empty($interactive) ? ' tape-live' : '' ?>" data-tiers='<?= e(json_encode($tiers)) ?>'>
  <div class="tape-body" aria-hidden="true"><span class="tape-fill" style="--to:<?= round(($active + 0.5) / count($tiers) * 100, 2) ?>%"></span></div>
  <ol class="tape-marks" style="--n:<?= count($tiers) ?>">
    <?php foreach ($tiers as $i => $t): ?>
    <li class="<?= $i === $active ? 'is-active' : '' ?>" data-min="<?= (int) $t['min_qty'] ?>">
      <span class="tape-qty"><?= (int) $t['min_qty'] ?>+ <?= e($unit) ?></span>
      <span class="tape-price"><?= money((int) $t['price_pence']) ?></span>
      <?php if ($i > 0): ?><span class="tape-save">save <?= round((1 - $t['price_pence'] / $base) * 100) ?>%</span><?php else: ?><span class="tape-save">list</span><?php endif; ?>
    </li>
    <?php endforeach; ?>
  </ol>
</div>
