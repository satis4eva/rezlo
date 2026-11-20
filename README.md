# Rezlo Ltd — trade wholesale website

Wholesale e-commerce site for **Rezlo Ltd** (rezloltd.co.uk): floor coverings, tiles, wall coverings, paint, adhesives and sundries sold in bulk to trade customers, plus installation and project support services.

- PHP 8.1+ with SQLite (default) or MySQL. No build step, no Composer packages.
- Card payments through **DPayments** (hosted checkout + signed webhooks).
- Linked to the **DBS Group master portal** (platform.dbsgroup.io): signed event push, plus an inbound API the portal can read and update.

## What's in it

| Area | Features |
|---|---|
| Catalogue | Categories, search, sort, product pages with material swatches or uploaded photos |
| Bulk pricing | Per-product price breaks, minimum order quantity, pack multiples, pallet quantity; live “tape” showing which break applies |
| Trade accounts | Application with company/VAT number, admin approval, 30-day credit terms with credit limit, password reset, reorder |
| Checkout | Card (DPayments), bank transfer with pro forma invoice, pay on account; PO numbers, preferred delivery date, £100 minimum order, free delivery from £500, remote postcodes routed to quote |
| Quotes | Quote requests (with cart contents), contact form, honeypot spam trap |
| Admin `/admin` | Dashboard, orders (status, mark paid, notes), products and price breaks, categories, customer approval and credit, enquiries, payments/portal status and event outbox |
| Legal | Trade terms of sale, services terms, website terms, privacy (UK GDPR), cookies, delivery, returns |
| Ops | Order/enquiry/account emails, CSRF protection, login throttling, sitemap.xml, robots.txt, schema.org markup |

## Deploy on Hostinger

1. **Connect the repo.** hPanel → Websites → rezloltd.co.uk → Advanced → **Git**. Repository `https://github.com/satis4eva/rezlo.git`, branch `main`, install path empty (deploys into `public_html`). Turn on auto-deployment and add the webhook to GitHub if you want pushes to deploy automatically.
2. **PHP version.** hPanel → Advanced → PHP Configuration → PHP 8.2 or 8.3. Make sure `pdo_sqlite` (or `pdo_mysql`), `curl` and `mbstring` are enabled (they are by default).
3. **Create `config.php`.** Either run `php install.php <db_name> <db_password> <admin_password>` once on the server (it writes a production config with MySQL and generated secrets), or copy `config.sample.php` to `config.php` in File Manager. Then fill in:
   - `app.secret`, `app.admin_password`, `app.cron_key` — long random strings
   - `company.company_number` (and `vat_number`, `ico_number` if you have them)
   - `shop.bank_transfer` sort code and account number
   - DPayments and portal keys (see below)
   `config.php` is git-ignored, so deployments never overwrite it and keys never reach GitHub.
4. **SSL.** hPanel → Security → SSL → install the free certificate. `.htaccess` then forces `https://rezloltd.co.uk`.
5. **Email.** Create the `info@rezloltd.co.uk` mailbox in hPanel → Emails so PHP mail sends from your own domain. Add the SPF/DKIM records Hostinger suggests so order emails don't land in spam.
6. **Cron.** hPanel → Advanced → Cron Jobs → every 5 minutes: `php /home/<user>/public_html/cron.php`. This retries any portal events that couldn't be delivered first time.
7. **First login.** Go to `/admin`, sign in with `admin_email` / `admin_password` from config, and change the password when prompted.
8. **Replace the sample catalogue.** All 24 starter products and prices are samples — edit, replace or hide them in Admin → Products.

The database is created automatically on first visit in `data/rezlo.sqlite` (blocked from the web by `.htaccess`). To use MySQL instead, create a database in hPanel and set `db.driver = 'mysql'` with its credentials.

## DPayments

Set in `config.php`:

```php
'dpayments' => [
  'mode' => 'sandbox',            // 'simulate' → 'sandbox' → 'live'
  'merchant_id' => '...',
  'api_key' => '...',
  'webhook_secret' => '...',
]
```

- **Simulate mode** (default) runs a built-in test payment page so the whole order flow works before keys arrive. No money moves.
- Rezlo follows the DBS Commerce DPayments contract (`docs/dpayments-gateway-api.md` on master.dbsgroup.io).
- **Flow:** `POST {api_base}/v1/checkout/sessions` (with `Idempotency-Key`) → redirect buyer to the returned `url` → DPayments calls `https://rezloltd.co.uk/webhooks/dpayments` and returns the buyer to `/checkout/return?order=REF`. Either marks the order paid (idempotent, and the amount must match the order total).
- **Request body sent:** `merchant_id, reference, amount (pence), currency, description, line_items[{name,quantity,unit_amount}]` (products ex VAT plus a VAT line), `discount_amount, shipping_amount, success_url, cancel_url, webhook_url, metadata`. Header `Authorization: Bearer <api_key>`.
- **Webhook:** `X-DPayments-Signature: t=<unix>,v1=<hex HMAC-SHA256 of "<t>.<raw body>">`, rejected if older than 10 minutes. Handles `payment.succeeded` (amount must match), `payment.failed`, `payment.expired`, `payment.refunded`, reading `data.reference` and `data.amount`.
- **Connection test:** `GET /v1/ping` from Admin → Payments and portal → Test DPayments connection.
- If the real DPayments API uses different paths or field names, change `create_path`, `retrieve_path`, `redirect_field`, `id_field`, `status_field`, `paid_statuses` in config — no code changes needed. The client is in `app/dpayments.php`.

## DBS Group master portal

```php
'portal' => [
  'enabled' => true,
  'base_url' => 'https://platform.dbsgroup.io',
  'brand' => 'rezlo',
  'api_key' => '...',         // Rezlo → portal
  'signing_secret' => '...',  // HMAC for outgoing events
  'inbound_key' => '...',     // portal → Rezlo
]
```

### Store health on master.dbsgroup.io

Rezlo answers the master portal's signed health check at `GET /api/platform/status?t=<unix>&sig=<Ed25519 sig of "status:<host>:<t>">`, the same contract as DBS Commerce stores. Set `portal.dbs_public_key` in `config.php` to the DBS licence public key. Add Rezlo in the master portal under **Clients** with domain `rezloltd.co.uk` and press **Check now**.

### Events Rezlo pushes to the portal

`POST {base_url}/api/brands/rezlo/events` with headers `Authorization: Bearer <api_key>`, `X-DBS-Brand`, `X-DBS-Event`, `X-DBS-Signature: sha256=<HMAC of body>`, `Idempotency-Key`.

```json
{ "id": "rezlo_evt_12", "brand": "rezlo", "event": "order.paid",
  "occurred_at": "2026-11-20T21:11:34+00:00", "data": { ... } }
```

Events: `customer.registered`, `customer.updated`, `customer.status_changed`, `order.created`, `order.paid`, `order.status_changed`, `enquiry.created`, `product.updated`, `portal.ping`. Any 2xx response counts as delivered; failures retry with back-off (2 min doubling, capped at 6 h, 12 attempts). Amounts are in pence.

### API the portal can call on Rezlo

Base `https://rezloltd.co.uk/api/v1`, header `Authorization: Bearer <inbound_key>`.

| Method | Path | Purpose |
|---|---|---|
| GET | `/health` | Connectivity check |
| GET | `/summary?since=YYYY-MM-DD` | Orders, paid revenue, outstanding credit, customers, enquiries |
| GET | `/orders?since=&limit=` | Orders with items |
| GET | `/orders/{ref}` | One order with delivery address |
| POST | `/orders/{ref}/status` | `{"status":"paid"}` or `processing`, `dispatched`, `delivered`, `cancelled`, `refunded` … |
| GET | `/customers?status=` | Trade customers |
| POST | `/customers/{id}/status` | `{"status":"approved","credit_terms":true,"credit_limit":500000}` |
| GET | `/products` | Catalogue with price breaks |
| POST | `/products/{sku}` | `{"price":3250,"stock":120,"active":true}` |
| GET | `/enquiries` | Quote requests and messages |

Admin → **Payments and portal** shows the connection status, lets you send a test event and lists the outbox.

## Live setup (done 20 Nov 2026)

- Hostinger website `rezloltd.co.uk` on account `u594118951`, deployed from `satis4eva/rezlo` `main` with auto-deploy.
- MySQL database `u594118951_rezlo`; `config.php` created on the server by `install.php` (not in Git, survives deploys).
- Cron every 5 minutes runs `cron.php`.
- Card payments and bank transfer stay hidden at checkout until DPayments keys and bank details are set in `config.php`.

## Local development

```bash
cp config.sample.php config.php   # set env=development, url=http://localhost:8000, mail.transport=log
php -S localhost:8000 index.php
```

## Before launch

- Fill in the company number, bank details and real DPayments/portal keys.
- Replace sample products and prices with the real range and photos.
- **Have a solicitor review the legal pages** (`views/legal/`). They're written for a UK business-to-business seller but are a starting point, not legal advice. Check the returns restocking fee, credit terms, delivery charges and liability caps match how you actually trade.
- If you add analytics or marketing tags, update the cookie policy and add a consent banner first.
