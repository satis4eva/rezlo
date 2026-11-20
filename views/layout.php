<?php $cust = current_customer(); $path = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH); ?>
<!DOCTYPE html>
<html lang="en-GB">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title><?= e($title ?? 'Rezlo') ?> | Rezlo trade supplies</title>
<meta name="description" content="<?= e($meta ?? 'Trade flooring, tiles, wall coverings and paint in bulk from Rezlo, Basingstoke.') ?>">
<link rel="canonical" href="<?= e(abs_url($path)) ?>">
<meta property="og:title" content="<?= e($title ?? 'Rezlo') ?>">
<meta property="og:description" content="<?= e($meta ?? '') ?>">
<meta property="og:type" content="website">
<link rel="icon" href="/assets/img/favicon.svg" type="image/svg+xml">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Archivo:wdth,wght@62..125,400..800&display=swap" rel="stylesheet">
<link rel="stylesheet" href="<?= asset('css/style.css') ?>">
<script type="application/ld+json"><?= json_encode([
    '@context' => 'https://schema.org', '@type' => 'Organization', 'name' => config('company.name'),
    'url' => abs_url('/'), 'email' => config('company.email'), 'telephone' => config('company.phone_e164'),
    'address' => ['@type' => 'PostalAddress', 'streetAddress' => config('company.address')[0], 'addressLocality' => config('company.address')[1], 'postalCode' => config('company.address')[3], 'addressCountry' => 'GB'],
], JSON_UNESCAPED_SLASHES) ?></script>
</head>
<body>
<a class="skip" href="#main">Skip to content</a>
<div class="topline">
  <div class="wrap topline-inner">
    <span>Trade prices shown ex VAT. Free mainland delivery on orders over <?= money_whole((int) config('shop.free_delivery_pence')) ?>.</span>
    <a href="tel:<?= e(config('company.phone_e164')) ?>"><?= e(config('company.phone')) ?></a>
  </div>
</div>
<header class="site-header">
  <div class="wrap header-inner">
    <a href="/" class="logo" aria-label="Rezlo home"><?php partial('logo'); ?></a>
    <button class="nav-toggle" aria-expanded="false" aria-controls="site-nav">Menu</button>
    <nav id="site-nav" class="site-nav" aria-label="Main">
      <a href="/shop" <?= str_starts_with($path, '/shop') || str_starts_with($path, '/product') ? 'aria-current="page"' : '' ?>>Shop</a>
      <a href="/services" <?= $path === '/services' ? 'aria-current="page"' : '' ?>>Services</a>
      <a href="/trade" <?= $path === '/trade' ? 'aria-current="page"' : '' ?>>Trade accounts</a>
      <a href="/quote" <?= $path === '/quote' ? 'aria-current="page"' : '' ?>>Get a quote</a>
      <a href="/contact" <?= $path === '/contact' ? 'aria-current="page"' : '' ?>>Contact</a>
    </nav>
    <div class="header-actions">
      <form class="search" action="/shop" method="get" role="search">
        <label class="sr-only" for="q">Search products</label>
        <input id="q" name="q" type="search" placeholder="Search SKU or product" value="<?= e($_GET['q'] ?? '') ?>">
      </form>
      <a class="account-link" href="<?= $cust ? '/account' : '/login' ?>"><?= $cust ? e(explode(' ', (string) $cust['contact_name'])[0]) : 'Sign in' ?></a>
      <a class="cart-link" href="/cart">Order<?php if (cart_count()): ?> <span class="badge"><?= cart_count() ?></span><?php endif; ?></a>
    </div>
  </div>
</header>

<main id="main">
  <?php if ($f = flashes()): ?>
  <div class="wrap flashes" role="status">
    <?php foreach ($f as [$type, $msg]): ?><div class="flash flash-<?= e($type) ?>"><?= e($msg) ?></div><?php endforeach; ?>
  </div>
  <?php endif; ?>
  <?= $content ?>
</main>

<footer class="site-footer">
  <div class="wrap footer-grid">
    <div>
      <div class="logo logo-footer"><?php partial('logo'); ?></div>
      <p>Floor, wall and paint supplies for the trade, with installation and project support from Basingstoke.</p>
      <p><a href="tel:<?= e(config('company.phone_e164')) ?>"><?= e(config('company.phone')) ?></a><br><a href="mailto:<?= e(config('company.email')) ?>"><?= e(config('company.email')) ?></a></p>
    </div>
    <div>
      <h2>Shop</h2>
      <ul><?php foreach (categories() as $c): ?><li><a href="/shop/<?= e($c['slug']) ?>"><?= e($c['name']) ?></a></li><?php endforeach; ?></ul>
    </div>
    <div>
      <h2>Trade</h2>
      <ul>
        <li><a href="/trade">Trade accounts</a></li>
        <li><a href="/quote">Request a quote</a></li>
        <li><a href="/services">Installation services</a></li>
        <li><a href="/delivery">Delivery</a></li>
        <li><a href="/returns">Returns</a></li>
        <li><a href="/about">About Rezlo</a></li>
      </ul>
    </div>
    <div>
      <h2>Legal</h2>
      <ul>
        <li><a href="/terms">Terms of sale</a></li>
        <li><a href="/services-terms">Services terms</a></li>
        <li><a href="/website-terms">Website terms</a></li>
        <li><a href="/privacy">Privacy</a></li>
        <li><a href="/cookies">Cookies</a></li>
      </ul>
    </div>
  </div>
  <div class="wrap footer-base">
    <p><?= e(company_line()) ?></p>
    <?php if (config('portal.powered_by')): ?><p>Powered by <a href="https://dbsgroup.io" rel="noopener">dbsgroup.io</a></p><?php endif; ?>
  </div>
</footer>
<script src="<?= asset('js/app.js') ?>" defer></script>
</body>
</html>
