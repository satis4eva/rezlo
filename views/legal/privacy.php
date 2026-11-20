<?php $co = config('company'); ?>
<p>This policy explains how <?= e($co['name']) ?> collects and uses personal information when you use rezloltd.co.uk, open a trade account, buy from us, ask for a quote or contact us. We are the controller of your personal information under the UK General Data Protection Regulation (UK GDPR) and the Data Protection Act 2018.</p>
<?php if (config('company.ico_number')): ?><p>Our ICO registration number is <?= e(config('company.ico_number')) ?>.</p><?php endif; ?>

<h2>Contact us</h2>
<p><?= e($co['name']) ?>, <?= e(implode(', ', array_slice($co['address'], 0, 4))) ?>. Email <a href="mailto:<?= e($co['email']) ?>"><?= e($co['email']) ?></a> or call <?= e($co['phone']) ?>.</p>

<h2>What we collect</h2>
<ul>
  <li><strong>Account details:</strong> your name, job role, business name, company and VAT numbers, business type, address, email, phone number and password (stored in encrypted form).</li>
  <li><strong>Order details:</strong> products ordered, delivery addresses and site contacts, purchase order numbers, invoices, payments and correspondence.</li>
  <li><strong>Payment details:</strong> card payments are handled by our payment provider DPayments. We receive confirmation that a payment succeeded and a payment reference, but not your full card number.</li>
  <li><strong>Enquiries:</strong> what you tell us when you request a quote or contact us, including site details and attachments you send.</li>
  <li><strong>Credit information:</strong> if you apply for credit terms, information from credit reference agencies and trade references.</li>
  <li><strong>Technical information:</strong> your IP address, browser type and the pages you visit, held in server logs for security. See our <a href="/cookies">cookie policy</a>.</li>
</ul>

<h2>How and why we use it</h2>
<table class="table">
  <thead><tr><th scope="col">Purpose</th><th scope="col">Lawful basis</th></tr></thead>
  <tbody>
    <tr><td>Opening and running your trade account, processing orders, delivery, invoicing and payments</td><td>Performing our contract with you, or taking steps at your request before entering one</td></tr>
    <tr><td>Checking businesses that apply for accounts and credit, and preventing fraud</td><td>Our legitimate interest in protecting our business</td></tr>
    <tr><td>Answering enquiries and preparing quotations</td><td>Our legitimate interest in responding to customers, or steps before a contract</td></tr>
    <tr><td>Keeping accounting and tax records</td><td>Legal obligation</td></tr>
    <tr><td>Sending trade offers and price updates by email</td><td>Your consent, which you can withdraw at any time. For corporate email addresses we may rely on our legitimate interest, and you can opt out at any time.</td></tr>
    <tr><td>Keeping our website secure and working properly</td><td>Our legitimate interest in running a secure service</td></tr>
    <tr><td>Making and defending legal claims</td><td>Our legitimate interest</td></tr>
  </tbody>
</table>

<h2>Who we share it with</h2>
<p>We share personal information only where needed for the purposes above, with:</p>
<ul>
  <li><strong>DPayments</strong>, our payment provider, to take card payments;</li>
  <li><strong>DBS Group (dbsgroup.io)</strong>, which provides and supports our website platform and business management systems, acting on our instructions;</li>
  <li>our website and email hosting providers;</li>
  <li>suppliers, manufacturers and couriers who deliver goods directly to you;</li>
  <li>credit reference agencies and debt collection agencies, if you apply for credit or don't pay;</li>
  <li>our accountants, insurers and legal advisers;</li>
  <li>HMRC, the police and other authorities, where the law requires it.</li>
</ul>
<p>We do not sell your personal information.</p>

<h2>International transfers</h2>
<p>Our service providers may store or process information outside the UK. Where they do, we make sure there are appropriate safeguards in place, such as a UK adequacy decision or the UK International Data Transfer Agreement or Addendum.</p>

<h2>How long we keep it</h2>
<ul>
  <li>Order, invoice and payment records: 6 years from the end of the financial year they relate to, as required for tax purposes.</li>
  <li>Trade account details: while your account is open, and for 6 years after your last order.</li>
  <li>Enquiries and quotations that don't lead to an order: 2 years.</li>
  <li>Marketing preferences: until you opt out, and then a suppression record so we don't contact you again.</li>
  <li>Server logs: up to 90 days.</li>
</ul>

<h2>Your rights</h2>
<p>You have the right to ask for a copy of your personal information, to have it corrected or deleted, to restrict or object to how we use it, and to have it transferred to you or another organisation. Where we rely on your consent, you can withdraw it at any time. Some rights have exceptions, for example we must keep invoice records for tax purposes.</p>
<p>To make a request, email <a href="mailto:<?= e($co['email']) ?>"><?= e($co['email']) ?></a>. We will respond within one month.</p>

<h2>Security</h2>
<p>We use encrypted connections (HTTPS), store passwords in hashed form, limit access to personal information to people who need it, and never store full card details.</p>

<h2>Complaints</h2>
<p>If you are unhappy with how we have handled your information, please contact us first. You also have the right to complain to the Information Commissioner's Office (ICO) at <a href="https://ico.org.uk" rel="noopener">ico.org.uk</a> or on 0303 123 1113.</p>

<h2>Changes</h2>
<p>We may update this policy. The current version is always on this page, with the date it was last updated.</p>
