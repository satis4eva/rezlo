<!DOCTYPE html>
<html lang="en-GB">
<head>
<meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
<title>Pro forma invoice <?= e($o['ref']) ?></title>
<link rel="stylesheet" href="<?= asset('css/style.css') ?>">
</head>
<body class="doc">
<div class="doc-page">
  <header class="doc-head">
    <div class="logo"><?php partial('logo'); ?></div>
    <div>
      <h1>Pro forma invoice</h1>
      <p><?= e($o['ref']) ?><br><?= e(date('j F Y', strtotime($o['created_at']))) ?></p>
    </div>
  </header>
  <div class="doc-parties">
    <div><h2>From</h2><p><?= e(config('company.name')) ?><br><?= implode('<br>', array_map('e', array_slice(config('company.address'), 0, 4))) ?><br><?= e(config('company.email')) ?><?php if (config('company.vat_number')): ?><br>VAT <?= e(config('company.vat_number')) ?><?php endif; ?></p></div>
    <div><h2>To</h2><p><?= nl2br(e(implode("\n", array_filter([$o['ship_company'], $o['ship_name'], $o['ship_address1'], $o['ship_address2'], $o['ship_city'], $o['ship_postcode']])))) ?><?php if ($o['po_number']): ?><br>PO: <?= e($o['po_number']) ?><?php endif; ?></p></div>
  </div>
  <?php partial('order_summary', ['o' => $o, 'items' => $items]); ?>
  <div class="doc-pay">
    <h2>How to pay</h2>
    <p><?= nl2br(e(bank_details_text())) ?><br>Reference: <?= e($o['ref']) ?></p>
    <p>This is a request for payment, not a VAT invoice. A VAT invoice is issued on dispatch. Goods remain our property until paid for in full under our Trade Terms and Conditions of Sale (<?= e(abs_url('/terms')) ?>).</p>
  </div>
  <footer class="doc-foot"><?= e(company_line()) ?></footer>
  <p class="no-print"><button class="btn btn-primary" onclick="window.print()">Print or save as PDF</button></p>
</div>
</body>
</html>
