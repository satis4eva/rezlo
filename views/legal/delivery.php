<?php $co = config('company'); ?>
<p>This policy forms part of our <a href="/terms">trade terms and conditions of sale</a>.</p>

<h2>Where we deliver</h2>
<p>We deliver to business and site addresses in mainland England, Wales and Scotland. Delivery to the Scottish Highlands and islands, Northern Ireland, the Isle of Man, the Isles of Scilly and the Channel Islands is priced on request — <a href="/quote">ask for a quote</a> and we'll include delivery.</p>
<p>Our Basingstoke address is a registered office only. Goods are delivered directly from our warehouse partners and can't be collected from it.</p>

<h2>Charges</h2>
<table class="table">
  <tbody>
    <tr><th scope="row">Orders of <?= money_whole((int) config('shop.free_delivery_pence')) ?> ex VAT and over</th><td>Free mainland delivery</td></tr>
    <tr><th scope="row">Orders under <?= money_whole((int) config('shop.free_delivery_pence')) ?> ex VAT</th><td><?= money_whole((int) config('shop.delivery_pence')) ?> ex VAT per order</td></tr>
    <tr><th scope="row">Minimum order</th><td><?= money_whole((int) config('shop.min_order_pence')) ?> ex VAT</td></tr>
    <tr><th scope="row">Timed, Saturday, tail-lift or crane delivery, or split drops</th><td>Quoted on request</td></tr>
  </tbody>
</table>

<h2>When it arrives</h2>
<p>Most stocked lines are delivered in 2 to 4 working days after we confirm your order and, for card or bank transfer orders, receive payment. Lead times for each product are shown on its page. Special orders and large pallet orders may take longer, and we'll confirm the date with you. Delivery dates are estimates and are not guaranteed unless we agree a fixed date in writing.</p>
<p>Choose a preferred delivery date at checkout and we'll do our best to meet it. We'll contact your site contact with the delivery day.</p>

<h2>Access and unloading</h2>
<ul>
  <li>Pallet deliveries are made by heavy goods vehicles with a tail-lift and pallet truck, to kerbside or the nearest hard, level, accessible point.</li>
  <li>The driver can't take goods upstairs, into buildings or over rough ground.</li>
  <li>Please tell us about access restrictions, low bridges, narrow roads, weight limits, site booking systems or loading hours when you order.</li>
  <li>Someone must be on site to receive and sign for the goods.</li>
</ul>

<h2>Failed deliveries</h2>
<p>If a driver can't deliver because nobody is there, access isn't suitable or the site refuses the delivery, we may charge for the failed attempt, redelivery and any storage.</p>

<h2>Checking your goods</h2>
<p>Check the number of items and the condition of packaging before signing. If anything looks damaged, sign for it as “damaged” and describe the damage. Tell us about any damage, shortage or wrong item within 48 hours, by email to <a href="mailto:<?= e($co['email']) ?>"><?= e($co['email']) ?></a> with your order number and photos. Please check batch and shade numbers before installing. Don't install goods that are damaged or don't match.</p>

<h2>Pallets</h2>
<p>Pallets are non-returnable unless we tell you otherwise.</p>
