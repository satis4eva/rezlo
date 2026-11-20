<div class="admin-head"><h1>Products</h1><a class="btn btn-primary" href="/admin/products/new">Add product</a></div>
<table class="table">
  <thead><tr><th></th><th>Product</th><th>Category</th><th class="num">Price ex VAT</th><th class="num">MOQ</th><th class="num">Stock</th><th>Status</th></tr></thead>
  <tbody>
  <?php foreach ($products as $p): ?>
  <tr class="<?= $p['active'] ? '' : 'is-off' ?>"><td><?= swatch((string) $p['swatch'], 'mini-swatch', (string) $p['image']) ?></td><td><a href="/admin/products/<?= (int) $p['id'] ?>"><?= e($p['name']) ?></a><br><small><?= e($p['sku']) ?></small></td><td><?= e($p['category_name']) ?></td><td class="num"><?= money((int) $p['price_pence']) ?> / <?= e($p['unit']) ?></td><td class="num"><?= (int) $p['moq'] ?></td><td class="num <?= $p['stock'] < 20 ? 'warn' : '' ?>"><?= (int) $p['stock'] ?></td><td><?= $p['active'] ? 'Live' : 'Hidden' ?><?= $p['featured'] ? ', featured' : '' ?></td></tr>
  <?php endforeach; ?>
  </tbody>
</table>
