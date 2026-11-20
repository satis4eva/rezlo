<section class="page-head">
  <div class="wrap">
    <h1>Trade accounts</h1>
    <p class="lede">Rezlo sells to businesses: contractors, decorators, tilers, fit-out firms, housing and facilities teams, and merchants. A free trade account lets you order online at trade prices.</p>
    <p><a class="btn btn-primary" href="/register">Apply for a trade account</a></p>
  </div>
</section>
<section class="section section-tight">
  <div class="wrap faq">
    <div>
      <h2>What you get</h2>
      <ul class="ticks">
        <li>Trade prices shown ex VAT, with price breaks that apply automatically as quantities go up</li>
        <li>Pay by card, bank transfer or, once approved, on 30-day account terms</li>
        <li>Your PO numbers on every order and invoice</li>
        <li>Reorder past orders in one click</li>
        <li>Project pricing for pallet and multi-drop orders</li>
      </ul>
    </div>
    <div>
      <h2>Common questions</h2>
      <details><summary>Who can open an account?</summary><p>Any UK business buying for trade use, including sole traders. We may ask for your company number, VAT number or proof of trading.</p></details>
      <details><summary>How long does approval take?</summary><p>Usually within one working day. You can browse prices and send quote requests while you wait.</p></details>
      <details><summary>How do price breaks work?</summary><p>Each product shows its price breaks as a scale on the product page. When your quantity reaches the next break, the lower unit price applies to the whole line.</p></details>
      <details><summary>Can I get credit terms?</summary><p>Yes, once you've traded with us. We carry out a credit check and set a limit. Invoices are due 30 days from the invoice date.</p></details>
      <details><summary>What is the minimum order?</summary><p><?= money_whole((int) config('shop.min_order_pence')) ?> ex VAT per order. Delivery is free to mainland UK addresses on orders over <?= money_whole((int) config('shop.free_delivery_pence')) ?> ex VAT, and <?= money_whole((int) config('shop.delivery_pence')) ?> ex VAT below that.</p></details>
    </div>
  </div>
</section>
