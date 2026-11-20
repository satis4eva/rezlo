<?php
declare(strict_types=1);

/**
 * Inbound API for the DBS Group master portal.
 * Auth: Authorization: Bearer <config portal.inbound_key>
 * Amounts are in pence (minor units).
 */

function api_auth(): void
{
    $key = (string) config('portal.inbound_key');
    $hdr = $_SERVER['HTTP_AUTHORIZATION'] ?? $_SERVER['REDIRECT_HTTP_AUTHORIZATION'] ?? '';
    $token = preg_replace('/^Bearer\s+/i', '', (string) $hdr);
    if ($key === '' || !hash_equals($key, $token)) {
        json_response(['error' => 'unauthorized'], 401);
    }
}

function api_body(): array
{
    $d = json_decode((string) file_get_contents('php://input'), true);
    return is_array($d) ? $d : $_POST;
}

function api_health(): void
{
    api_auth();
    json_response(['ok' => true, 'brand' => config('portal.brand'), 'site' => abs_url('/'), 'payments_mode' => dp_mode(), 'time' => date('c')]);
}

function api_summary(): void
{
    api_auth();
    $since = (string) input('since', date('Y-m-d', strtotime('-30 days')));
    json_response([
        'brand' => config('portal.brand'),
        'since' => $since,
        'orders' => (int) val('SELECT COUNT(*) FROM orders WHERE created_at >= ?', [$since]),
        'paid_orders' => (int) val("SELECT COUNT(*) FROM orders WHERE payment_status = 'paid' AND created_at >= ?", [$since]),
        'gross_paid' => (int) val("SELECT COALESCE(SUM(total_pence),0) FROM orders WHERE payment_status = 'paid' AND created_at >= ?", [$since]),
        'net_paid_ex_vat' => (int) val("SELECT COALESCE(SUM(subtotal_pence + delivery_pence),0) FROM orders WHERE payment_status = 'paid' AND created_at >= ?", [$since]),
        'on_account_outstanding' => (int) val("SELECT COALESCE(SUM(total_pence),0) FROM orders WHERE payment_method = 'account' AND payment_status != 'paid' AND status NOT IN ('cancelled','refunded')"),
        'customers' => (int) val('SELECT COUNT(*) FROM customers'),
        'customers_pending' => (int) val("SELECT COUNT(*) FROM customers WHERE status = 'pending'"),
        'enquiries_new' => (int) val("SELECT COUNT(*) FROM enquiries WHERE status = 'new'"),
        'currency' => 'GBP',
    ]);
}

function api_orders(): void
{
    api_auth();
    $since = (string) input('since', '1970-01-01');
    $limit = min(500, max(1, (int) input('limit', 100)));
    $orders = rows('SELECT * FROM orders WHERE COALESCE(updated_at, created_at) >= ? ORDER BY id DESC LIMIT ' . $limit, [$since]);
    json_response(['data' => array_map('portal_order_payload', $orders)]);
}

function api_order(string $ref): void
{
    api_auth();
    $o = order_by_ref($ref) ?? json_response(['error' => 'not_found'], 404);
    json_response(['data' => portal_order_payload($o) + ['delivery_address' => [
        'name' => $o['ship_name'], 'company' => $o['ship_company'], 'phone' => $o['ship_phone'],
        'line1' => $o['ship_address1'], 'line2' => $o['ship_address2'], 'city' => $o['ship_city'], 'postcode' => $o['ship_postcode'],
    ]]]);
}

function api_order_status(string $ref): void
{
    api_auth();
    $o = order_by_ref($ref) ?? json_response(['error' => 'not_found'], 404);
    $b = api_body();
    $status = (string) ($b['status'] ?? '');
    if ($status === 'paid') {
        order_mark_paid($o, 'portal');
    } elseif (in_array($status, ORDER_STATUSES, true)) {
        order_set_status($o, $status);
    } else {
        json_response(['error' => 'invalid_status', 'allowed' => ORDER_STATUSES], 422);
    }
    json_response(['data' => portal_order_payload(order_by_ref($ref))]);
}

function api_customers(): void
{
    api_auth();
    $status = (string) input('status');
    $list = $status ? rows('SELECT * FROM customers WHERE status = ? ORDER BY id DESC', [$status]) : rows('SELECT * FROM customers ORDER BY id DESC LIMIT 1000');
    json_response(['data' => array_map('portal_customer_payload', $list)]);
}

function api_customer_status(string $id): void
{
    api_auth();
    $c = row('SELECT * FROM customers WHERE id = ?', [(int) $id]) ?? json_response(['error' => 'not_found'], 404);
    $b = api_body();
    if (isset($b['status'])) {
        if (!in_array($b['status'], ['pending', 'approved', 'rejected', 'suspended'], true)) {
            json_response(['error' => 'invalid_status'], 422);
        }
        customer_set_status($c, $b['status']);
    }
    if (isset($b['credit_terms']) || isset($b['credit_limit'])) {
        update('customers', [
            'credit_terms' => !empty($b['credit_terms']) ? 1 : 0,
            'credit_limit_pence' => (int) ($b['credit_limit'] ?? $c['credit_limit_pence']),
        ], 'id = :id', ['id' => $c['id']]);
    }
    json_response(['data' => portal_customer_payload(row('SELECT * FROM customers WHERE id = ?', [$c['id']]))]);
}

function api_products(): void
{
    api_auth();
    $list = rows('SELECT p.*, c.slug AS category FROM products p LEFT JOIN categories c ON c.id = p.category_id ORDER BY p.id');
    json_response(['data' => array_map(fn($p) => [
        'sku' => $p['sku'], 'name' => $p['name'], 'category' => $p['category'], 'unit' => $p['unit'], 'pack' => $p['pack_note'],
        'price' => (int) $p['price_pence'], 'moq' => (int) $p['moq'], 'step' => (int) $p['step'], 'stock' => (int) $p['stock'],
        'active' => (bool) $p['active'], 'tiers' => product_tiers($p), 'url' => abs_url('/product/' . $p['slug']),
    ], $list)]);
}

/** Update price, stock or active flag by SKU. Body: {"price": 3250, "stock": 120, "active": true} */
function api_product_update(string $sku): void
{
    api_auth();
    $p = row('SELECT * FROM products WHERE sku = ?', [strtoupper($sku)]) ?? json_response(['error' => 'not_found'], 404);
    $b = api_body();
    $f = [];
    if (isset($b['price'])) $f['price_pence'] = max(1, (int) $b['price']);
    if (isset($b['stock'])) $f['stock'] = (int) $b['stock'];
    if (isset($b['active'])) $f['active'] = $b['active'] ? 1 : 0;
    if ($f) update('products', $f, 'id = :id', ['id' => $p['id']]);
    $p = row('SELECT * FROM products WHERE id = ?', [$p['id']]);
    json_response(['data' => ['sku' => $p['sku'], 'price' => (int) $p['price_pence'], 'stock' => (int) $p['stock'], 'active' => (bool) $p['active']]]);
}

function api_enquiries(): void
{
    api_auth();
    json_response(['data' => rows('SELECT id, ref, kind, name, company, email, phone, postcode, service, message, status, created_at FROM enquiries ORDER BY id DESC LIMIT 500')]);
}

/**
 * Store health for the DBS master portal (master.dbsgroup.io).
 * The portal signs "status:<host>:<unix time>" with the DBS Ed25519 key; only it can read this.
 */
function api_platform_status(): void
{
    $pub = base64_decode((string) config('portal.dbs_public_key'), true);
    $t = (int) ($_GET['t'] ?? 0);
    $host = strtolower((string) ($_SERVER['HTTP_HOST'] ?? ''));
    $sigB64 = strtr((string) ($_GET['sig'] ?? ''), '-_', '+/');
    $sig = base64_decode($sigB64 . str_repeat('=', (4 - strlen($sigB64) % 4) % 4), true) ?: '';
    $ok = $pub && strlen($pub) === SODIUM_CRYPTO_SIGN_PUBLICKEYBYTES && $t && abs(time() - $t) < 300
        && strlen($sig) === SODIUM_CRYPTO_SIGN_BYTES
        && sodium_crypto_sign_verify_detached($sig, "status:$host:$t", $pub);
    if (!$ok) {
        json_response(['error' => 'forbidden'], 403);
    }
    header('Cache-Control: no-store');
    $since = date('Y-m-d', strtotime('-30 days'));
    $paid = "payment_status = 'paid'";
    $payments = [];
    if (dp_card_available()) $payments[] = 'dpayments';
    if (config('shop.bank_transfer.account_number')) $payments[] = 'bank transfer';
    $payments[] = 'on account';
    json_response([
        'brand' => config('company.trading_name', 'Rezlo') . ' (' . config('company.name') . ')',
        'version' => 'rezlo-1.1',
        'php' => PHP_VERSION,
        'licence_ok' => true,
        'licence_msg' => 'Custom DBS build (no licence key needed)',
        'plan' => 'custom',
        'demo' => false,
        'orders_30d' => (int) val('SELECT COUNT(*) FROM orders WHERE created_at >= ?', [$since]),
        'revenue_30d' => (int) val("SELECT COALESCE(SUM(total_pence),0) FROM orders WHERE $paid AND created_at >= ?", [$since]) / 100,
        'revenue_total' => (int) val("SELECT COALESCE(SUM(total_pence),0) FROM orders WHERE $paid") / 100,
        'last_order_at' => val('SELECT MAX(created_at) FROM orders') ?: null,
        'products' => (int) val('SELECT COUNT(*) FROM products WHERE active = 1'),
        'reviews_pending' => 0,
        'creators' => 0,
        'users' => (int) val('SELECT COUNT(*) FROM admins'),
        'setup_done' => true,
        'payments' => implode(', ', $payments),
        'connections' => ['dpayments' => dp_card_available(), 'portal_events' => portal_enabled(), 'email' => config('mail.transport') === 'mail'],
        'modules' => ['wholesale', 'trade-accounts', 'bulk-pricing', 'quotes', 'credit-accounts'],
        'trade_accounts_pending' => (int) val("SELECT COUNT(*) FROM customers WHERE status = 'pending'"),
        'enquiries_new' => (int) val("SELECT COUNT(*) FROM enquiries WHERE status = 'new'"),
    ]);
}
