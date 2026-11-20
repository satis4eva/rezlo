<?php $a = current_admin(); $path = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH); ?>
<!DOCTYPE html>
<html lang="en-GB">
<head>
<meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="robots" content="noindex, nofollow">
<title><?= e($title ?? 'Admin') ?> | Rezlo admin</title>
<link rel="icon" href="/assets/img/favicon.svg" type="image/svg+xml">
<link rel="preconnect" href="https://fonts.googleapis.com"><link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Archivo:wdth,wght@62..125,400..800&display=swap" rel="stylesheet">
<link rel="stylesheet" href="<?= asset('css/style.css') ?>">
</head>
<body class="admin">
<?php if ($a): ?>
<aside class="admin-nav">
  <a href="/admin" class="logo"><?php partial('logo'); ?></a>
  <nav aria-label="Admin">
    <?php foreach (['/admin' => 'Dashboard', '/admin/orders' => 'Orders', '/admin/products' => 'Products', '/admin/categories' => 'Categories', '/admin/customers' => 'Trade customers', '/admin/enquiries' => 'Enquiries', '/admin/integrations' => 'Payments and portal'] as $href => $label): ?>
    <a href="<?= $href ?>" <?= ($href === '/admin' ? $path === '/admin' : str_starts_with($path, $href)) ? 'aria-current="page"' : '' ?>><?= $label ?></a>
    <?php endforeach; ?>
  </nav>
  <div class="admin-me">
    <span><?= e($a['email']) ?></span>
    <a href="/admin/password">Change password</a>
    <a href="/" target="_blank">View site</a>
    <form method="post" action="/admin/logout"><?= csrf_field() ?><button class="link-btn" type="submit">Sign out</button></form>
  </div>
</aside>
<?php endif; ?>
<main class="admin-main" id="main">
  <?php foreach (flashes() as [$type, $msg]): ?><div class="flash flash-<?= e($type) ?>"><?= e($msg) ?></div><?php endforeach; ?>
  <?= $content ?>
</main>
</body>
</html>
