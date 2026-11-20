<?php $hero = $featured[0] ?? null; ?>
<section class="hero">
  <div class="wrap hero-grid">
    <div class="hero-copy">
      <h1>Flooring, tiles, wall coverings and paint for the trade, by the box or by the pallet.</h1>
      <p class="lede">Rezlo supplies contractors, decorators and fit-out firms with finishing materials at trade prices. The more you order, the lower the unit price, and orders over <?= money_whole((int) config('shop.free_delivery_pence')) ?> ship free across mainland UK.</p>
      <div class="hero-actions">
        <a class="btn btn-primary" href="/shop">Browse the catalogue</a>
        <a class="btn btn-ghost" href="/register">Open a trade account</a>
      </div>
    </div>
    <div class="board" aria-hidden="true">
      <?= swatch('oak:#b8895a', 'board-a') ?>
      <?= swatch('tile:#cfcac1', 'board-b') ?>
      <?= swatch('paint:#4f6b5c', 'board-c') ?>
      <?= swatch('carpet:#4a4e52', 'board-d') ?>
      <?= swatch('paper:#d8d2c4', 'board-e') ?>
    </div>
  </div>
  <?php if ($hero): ?>
  <div class="wrap hero-tape">
    <p class="hero-tape-label">Price breaks on <a href="/product/<?= e($hero['slug']) ?>"><?= e($hero['name']) ?></a>, per <?= e($hero['unit']) ?> ex VAT</p>
    <?php partial('tape', ['tiers' => product_tiers($hero), 'unit' => plural_unit($hero['unit']), 'qty' => 0]); ?>
  </div>
  <?php endif; ?>
</section>

<section class="section">
  <div class="wrap">
    <div class="section-head">
      <h2>Shop by material</h2>
      <a href="/shop">All products</a>
    </div>
    <div class="cats">
      <?php foreach (categories() as $i => $c): ?>
      <a class="cat<?= $i === 0 ? ' cat-lead' : '' ?>" href="/shop/<?= e($c['slug']) ?>">
        <?= swatch((string) $c['swatch'], 'cat-swatch') ?>
        <span class="cat-text">
          <span class="cat-name"><?= e($c['name']) ?></span>
          <span class="cat-count"><?= (int) ($counts[$c['id']] ?? 0) ?> product lines</span>
        </span>
      </a>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<section class="section section-alt">
  <div class="wrap">
    <div class="section-head">
      <h2>Regular trade lines</h2>
      <a href="/shop">See the full range</a>
    </div>
    <div class="pgrid">
      <?php foreach ($featured as $p) partial('product_card', ['p' => $p]); ?>
    </div>
  </div>
</section>

<section class="section">
  <div class="wrap split">
    <div>
      <h2>How trade ordering works</h2>
      <p>Anyone can browse prices. To order online you need an approved trade account, which we usually confirm within one working day.</p>
    </div>
    <ol class="steps">
      <li><h3>Apply for an account</h3><p>Tell us about your business. Add your company and VAT numbers if you have them.</p></li>
      <li><h3>Get approved</h3><p>We check your details and email you. Regular customers can apply for 30-day credit.</p></li>
      <li><h3>Order in bulk</h3><p>Price breaks apply automatically as quantities go up. Pay by card, bank transfer or on account.</p></li>
      <li><h3>Delivered to site</h3><p>Pallet and kerbside delivery across mainland UK, usually in 2 to 4 working days.</p></li>
    </ol>
  </div>
</section>

<section class="section section-dark">
  <div class="wrap split">
    <div>
      <h2>Need it fitted as well?</h2>
      <p>Our own teams install flooring, hang wall coverings, tile and decorate, and we can manage supply for whole projects.</p>
      <a class="btn btn-primary" href="/services">See our services</a>
    </div>
    <ul class="service-list">
      <?php foreach (services_list() as $s): ?><li><?= e($s[0]) ?></li><?php endforeach; ?>
    </ul>
  </div>
</section>

<section class="section">
  <div class="wrap contact-band">
    <div>
      <h2>Ordering more than a pallet?</h2>
      <p>Send us your take-off or schedule and we'll price the whole job, including phased deliveries.</p>
    </div>
    <div class="contact-band-actions">
      <a class="btn btn-primary" href="/quote">Request a quote</a>
      <a class="btn btn-ghost" href="tel:<?= e(config('company.phone_e164')) ?>">Call <?= e(config('company.phone')) ?></a>
    </div>
  </div>
</section>
