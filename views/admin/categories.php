<h1>Categories</h1>
<table class="table">
  <thead><tr><th>Order</th><th>Name</th><th>Description</th><th>Swatch</th><th></th></tr></thead>
  <tbody>
  <?php foreach ($cats as $c): ?>
  <tr><td colspan="5">
    <form method="post" class="inline-form"><?= csrf_field() ?><input type="hidden" name="id" value="<?= (int) $c['id'] ?>">
      <input name="sort" type="number" value="<?= (int) $c['sort'] ?>" aria-label="Order" class="w-s">
      <input name="name" value="<?= e($c['name']) ?>" aria-label="Name">
      <input name="description" value="<?= e($c['description']) ?>" aria-label="Description" class="w-l">
      <input name="swatch" value="<?= e($c['swatch']) ?>" aria-label="Swatch">
      <button class="btn btn-ghost" type="submit">Save</button>
    </form>
  </td></tr>
  <?php endforeach; ?>
  </tbody>
</table>
<h2>Add a category</h2>
<form method="post" class="inline-form"><?= csrf_field() ?>
  <input name="sort" type="number" value="<?= count($cats) + 1 ?>" aria-label="Order" class="w-s">
  <input name="name" placeholder="Name" aria-label="Name" required>
  <input name="description" placeholder="Description" aria-label="Description" class="w-l">
  <input name="swatch" placeholder="tile:#cccccc" aria-label="Swatch">
  <button class="btn btn-primary" type="submit">Add category</button>
</form>
