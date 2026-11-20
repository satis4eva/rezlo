<section class="page-head">
  <div class="wrap">
    <h1>Installation, decorating and project support</h1>
    <p class="lede">As well as supplying materials, Rezlo carries out floor covering, wall covering, painting and finishing work, and supports contractors with procurement on larger projects. We work mainly across Hampshire, Berkshire, Surrey and the South of England.</p>
  </div>
</section>
<section class="section section-tight">
  <div class="wrap services">
    <?php foreach (services_list() as $key => [$name, $desc, $sw]): ?>
    <article class="service" id="<?= e($key) ?>">
      <?= swatch($sw, 'service-swatch') ?>
      <div>
        <h2><?= e($name) ?></h2>
        <p><?= e($desc) ?></p>
        <a href="/quote?service=<?= urlencode($name) ?>">Ask for a quote for <?= e(lcfirst($name)) ?></a>
      </div>
    </article>
    <?php endforeach; ?>
  </div>
</section>
<section class="section section-alt">
  <div class="wrap split">
    <div>
      <h2>How a services job runs</h2>
      <p>Every job is priced in writing before work starts. Services are carried out under our <a href="/services-terms">terms for installation and support services</a>.</p>
    </div>
    <ol class="steps">
      <li><h3>Tell us about the job</h3><p>Send drawings, a schedule or photos with the area, finish and timescale.</p></li>
      <li><h3>Survey and quote</h3><p>We measure where needed and send a fixed written quote with materials listed.</p></li>
      <li><h3>Book in</h3><p>Accept the quote and agree dates. Deposits apply to some larger jobs.</p></li>
      <li><h3>Complete and sign off</h3><p>We finish, snag with you and leave the area clean, ready for handover.</p></li>
    </ol>
  </div>
</section>
