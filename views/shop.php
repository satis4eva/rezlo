<section class="page-head">
  <div class="wrap">
    <nav class="crumbs" aria-label="Breadcrumb"><a href="/">Home</a> / <a href="/shop">Shop</a><?php if ($cat): ?> / <span><?= e($cat['name']) ?></span><?php endif; ?></nav>
    <h1><?= e($cat['name'] ?? ($search ? 'Results for “' . $search . '”' : 'Trade catalogue')) ?></h1>
    <p class="lede"><?= e($cat['description'] ?? 'All prices are per unit, ex VAT. Bulk price breaks apply automatically in your order.') ?></p>
  </div>
</section>
<section class="section section-tight">
  <div class="wrap">
    <div class="shop-bar">
      <nav class="cat-tabs" aria-label="Categories">
        <a href="/shop" <?= !$cat ? 'aria-current="page"' : '' ?>>All</a>
        <?php foreach (categories() as $c): ?>
        <a href="/shop/<?= e($c['slug']) ?>" <?= ($cat['id'] ?? 0) == $c['id'] ? 'aria-current="page"' : '' ?>><?= e($c['name']) ?></a>
        <?php endforeach; ?>
      </nav>
      <form method="get" class="sort">
        <?php if ($search): ?><input type="hidden" name="q" value="<?= e($search) ?>"><?php endif; ?>
        <label for="sort">Sort</label>
        <select id="sort" name="sort" onchange="this.form.submit()">
          <?php foreach (['featured' => 'Popular first', 'price' => 'Price, low to high', 'price_desc' => 'Price, high to low', 'name' => 'Name'] as $k => $v): ?>
          <option value="<?= $k ?>" <?= $sort === $k ? 'selected' : '' ?>><?= $v ?></option>
          <?php endforeach; ?>
        </select>
      </form>
    </div>
    <?php if ($products): ?>
    <p class="result-count"><?= count($products) ?> product<?= count($products) === 1 ? '' : 's' ?></p>
    <div class="pgrid">
      <?php foreach ($products as $p) partial('product_card', ['p' => $p]); ?>
    </div>
    <?php else: ?>
    <div class="empty">
      <h2>Nothing matches that search</h2>
      <p>Try a SKU, a material such as “oak” or “adhesive”, or ask us — we source plenty of lines that aren't listed online.</p>
      <a class="btn btn-primary" href="/quote">Ask us to source it</a>
    </div>
    <?php endif; ?>
  </div>
</section>
