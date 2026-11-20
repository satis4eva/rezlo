<p class="crumbs"><a href="/admin/products">Products</a> / <?= $p ? e($p['sku']) : 'New' ?></p>
<h1><?= e($title) ?></h1>
<form method="post" action="/admin/products/save" enctype="multipart/form-data" class="form-card wide-form">
  <?= csrf_field() ?>
  <input type="hidden" name="id" value="<?= (int) ($p['id'] ?? 0) ?>">
  <div class="grid-3">
    <div class="field"><label for="name">Name</label><input id="name" name="name" required value="<?= e($p['name'] ?? '') ?>"></div>
    <div class="field"><label for="sku">SKU</label><input id="sku" name="sku" required value="<?= e($p['sku'] ?? '') ?>"></div>
    <div class="field"><label for="category_id">Category</label><select id="category_id" name="category_id"><?php foreach ($cats as $c): ?><option value="<?= (int) $c['id'] ?>" <?= (int) ($p['category_id'] ?? 0) === (int) $c['id'] ? 'selected' : '' ?>><?= e($c['name']) ?></option><?php endforeach; ?></select></div>
  </div>
  <div class="field"><label for="short">Short description</label><input id="short" name="short" maxlength="255" value="<?= e($p['short'] ?? '') ?>"></div>
  <div class="field"><label for="description">Full description</label><textarea id="description" name="description" rows="5"><?= e($p['description'] ?? '') ?></textarea></div>
  <div class="grid-3">
    <div class="field"><label for="price">List price per unit, £ ex VAT</label><input id="price" name="price" type="number" step="0.01" min="0.01" required value="<?= isset($p) ? number_format($p['price_pence'] / 100, 2, '.', '') : '' ?>"></div>
    <div class="field"><label for="unit">Sold per (unit)</label><input id="unit" name="unit" value="<?= e($p['unit'] ?? 'box') ?>" placeholder="box, bag, tub, roll, m²"></div>
    <div class="field"><label for="pack_note">Pack size</label><input id="pack_note" name="pack_note" value="<?= e($p['pack_note'] ?? '') ?>" placeholder="2.2 m² per box"></div>
  </div>
  <div class="grid-3">
    <div class="field"><label for="moq">Minimum order quantity</label><input id="moq" name="moq" type="number" min="1" value="<?= (int) ($p['moq'] ?? 1) ?>"></div>
    <div class="field"><label for="step">Sold in multiples of</label><input id="step" name="step" type="number" min="1" value="<?= (int) ($p['step'] ?? 1) ?>"></div>
    <div class="field"><label for="pallet_qty">Units per pallet</label><input id="pallet_qty" name="pallet_qty" type="number" min="0" value="<?= (int) ($p['pallet_qty'] ?? 0) ?>"></div>
  </div>
  <div class="field">
    <label for="tiers">Bulk price breaks — one per line as <em>quantity = price</em></label>
    <textarea id="tiers" name="tiers" rows="4" placeholder="20 = 29.90&#10;60 = 27.50"><?php foreach ($tiers as $t) echo (int) $t['min_qty'] . ' = ' . number_format($t['price_pence'] / 100, 2, '.', '') . "\n"; ?></textarea>
    <p class="note">The list price applies from the minimum order quantity. Each break applies to the whole line once the quantity reaches it.</p>
  </div>
  <div class="grid-3">
    <div class="field"><label for="stock">Stock</label><input id="stock" name="stock" type="number" value="<?= (int) ($p['stock'] ?? 0) ?>"></div>
    <div class="field"><label for="lead_time">Lead time</label><input id="lead_time" name="lead_time" value="<?= e($p['lead_time'] ?? '2–4 working days') ?>"></div>
    <div class="field"><label for="slug">Web address</label><input id="slug" name="slug" value="<?= e($p['slug'] ?? '') ?>" placeholder="made from the name"></div>
  </div>
  <div class="grid-3">
    <div class="field"><label for="image">Photo (JPG, PNG or WebP)</label><input id="image" name="image" type="file" accept="image/jpeg,image/png,image/webp">
      <?php if (!empty($p['image'])): ?><label class="check"><input type="checkbox" name="remove_image" value="1"> <span>Remove current photo</span></label><?php endif; ?></div>
    <div class="field"><label for="swatch">Swatch when no photo</label><input id="swatch" name="swatch" value="<?= e($p['swatch'] ?? 'paint:#e9e6dc') ?>"><p class="note">oak, tile, paint, carpet, vinyl, paper, bag, tub or roll, then a colour, e.g. <code>oak:#b8895a</code></p></div>
    <div class="field"><?= swatch((string) ($p['swatch'] ?? ''), 'edit-swatch', (string) ($p['image'] ?? '')) ?></div>
  </div>
  <label class="check"><input type="checkbox" name="active" value="1" <?= ($p['active'] ?? 1) ? 'checked' : '' ?>> <span>Show on the website</span></label>
  <label class="check"><input type="checkbox" name="featured" value="1" <?= ($p['featured'] ?? 0) ? 'checked' : '' ?>> <span>Feature on the home page</span></label>
  <div class="row-actions"><button class="btn btn-primary" type="submit">Save product</button><?php if ($p): ?><a href="/product/<?= e($p['slug']) ?>" target="_blank">View on site</a><?php endif; ?></div>
</form>
