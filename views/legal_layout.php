<?php ob_start(); ?>
<section class="page-head">
  <div class="wrap">
    <h1><?= e($title) ?></h1>
    <p class="lede">Last updated <?= e($updated) ?></p>
  </div>
</section>
<section class="section section-tight">
  <div class="wrap legal">
    <nav class="legal-nav" aria-label="Legal documents">
      <?php foreach ($legalTitles as $slug => $t): ?>
      <a href="/<?= $slug ?>" <?= $slug === $doc ? 'aria-current="page"' : '' ?>><?= e($t) ?></a>
      <?php endforeach; ?>
      <button class="btn btn-ghost print-btn" onclick="window.print()">Print this page</button>
    </nav>
    <article class="prose legal-body">
      <?= $content ?>
    </article>
  </div>
</section>
<?php $content = ob_get_clean(); require ROOT . '/views/layout.php'; ?>
