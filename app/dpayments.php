<?php
declare(strict_types=1);

/**
 * DPayments hosted checkout.
 *
 * Flow: create a checkout session for the order → redirect the buyer to the
 * DPayments payment page → DPayments sends a signed webhook to
 * /webhooks/dpayments and returns the buyer to /checkout/return. Either one
 * marks the order paid (idempotently).
 *
 * Endpoint paths and response field names live in config('dpayments') so they
 * can be matched to the DPayments API reference without touching this file.
 * mode = 'simulate' runs a built-in test checkout with no real money.
 */

function http_json(string $method, string $url, ?array $body = null, array $headers = [], int $timeout = 20): array
{
    $ch = curl_init($url);
    $h = array_merge(['Accept: application/json'], $headers);
    if ($body !== null) {
        $payload = json_encode($body, JSON_UNESCAPED_SLASHES);
        $h[] = 'Content-Type: application/json';
        curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
    }
    curl_setopt_array($ch, [
        CURLOPT_CUSTOMREQUEST  => $method,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HTTPHEADER     => $h,
        CURLOPT_TIMEOUT        => $timeout,
        CURLOPT_CONNECTTIMEOUT => min(10, $timeout),
    ]);
    $raw = curl_exec($ch);
    $status = (int) curl_getinfo($ch, CURLINFO_RESPONSE_CODE);
    $err = curl_error($ch);
    curl_close($ch);
    if ($raw === false) {
        return ['ok' => false, 'status' => 0, 'error' => $err, 'data' => null];
    }
    $data = json_decode((string) $raw, true);
    return ['ok' => $status >= 200 && $status < 300, 'status' => $status, 'error' => $status >= 300 ? (string) $raw : '', 'data' => $data];
}

function dp_mode(): string
{
    return (string) config('dpayments.mode', 'simulate');
}

/** Card payments are offered when real DPayments keys are set, or in test mode on a development copy only. */
function dp_card_available(): bool
{
    if (dp_mode() === 'simulate') {
        return config('app.env') === 'development';
    }
    return config('dpayments.merchant_id') && config('dpayments.api_key') && config('dpayments.webhook_secret');
}

function dp_base(): string
{
    return rtrim((string) (dp_mode() === 'live' ? config('dpayments.api_base_live') : config('dpayments.api_base_sandbox')), '/');
}

function dp_headers(): array
{
    return [
        'Authorization: Bearer ' . config('dpayments.api_key'),
        'X-Merchant-Id: ' . config('dpayments.merchant_id'),
    ];
}

function dp_log(?int $orderId, string $event, $payload, string $source = 'dpayments'): void
{
    insert('payment_log', [
        'order_id' => $orderId, 'source' => $source, 'event' => $event,
        'payload' => is_string($payload) ? $payload : json_encode($payload, JSON_UNESCAPED_SLASHES),
        'created_at' => now(),
    ]);
}

/** @return array{id:string, redirect:string} */
function dp_create_session(array $order): array
{
    $returnUrl = abs_url('/checkout/return?order=' . urlencode($order['ref']));
    if (dp_mode() === 'simulate') {
        $id = 'sim_' . bin2hex(random_bytes(8));
        dp_log((int) $order['id'], 'session.created', ['id' => $id, 'mode' => 'simulate']);
        return ['id' => $id, 'redirect' => '/pay/simulate?session=' . $id . '&order=' . urlencode($order['ref'])];
    }

    $lines = [];
    foreach (order_items((int) $order['id']) as $i) {
        $lines[] = ['name' => $i['name'] . ' (' . $i['sku'] . ')', 'quantity' => (int) $i['qty'], 'unit_amount' => (int) $i['unit_price_pence']];
    }
    if ((int) $order['vat_pence'] > 0) {
        $lines[] = ['name' => 'VAT at ' . round(config('shop.vat_rate') * 100) . '%', 'quantity' => 1, 'unit_amount' => (int) $order['vat_pence']];
    }
    // Contract: amount = sum(line_items) - discount_amount + shipping_amount (see docs on master.dbsgroup.io)
    $body = [
        'merchant_id'              => config('dpayments.merchant_id'),
        'reference'                => $order['ref'],
        'amount'                   => (int) $order['total_pence'],
        'currency'                 => config('shop.currency', 'GBP'),
        'description'              => 'Rezlo order ' . $order['ref'],
        'line_items'               => $lines,
        'discount_amount'          => 0,
        'shipping_amount'          => (int) $order['delivery_pence'],
        'collect_shipping_address' => false,
        'collect_phone'            => false,
        'customer'                 => ['email' => $order['email'], 'name' => $order['ship_name']],
        'success_url'              => $returnUrl,
        'cancel_url'               => abs_url('/checkout?cancelled=' . urlencode($order['ref'])),
        'webhook_url'              => abs_url('/webhooks/dpayments'),
        'metadata'                 => ['platform' => 'rezlo', 'store' => parse_url(abs_url('/'), PHP_URL_HOST), 'order_ref' => $order['ref']],
    ];
    $res = http_json('POST', dp_base() . config('dpayments.create_path'), $body, array_merge(dp_headers(), ['Idempotency-Key: ' . $order['ref'] . '-' . substr((string) $order['updated_at'], -8)]));
    dp_log((int) $order['id'], 'session.create_response', $res);
    $id = $res['data'][config('dpayments.id_field')] ?? null;
    $redirect = $res['data'][config('dpayments.redirect_field')] ?? $res['data']['url'] ?? $res['data']['checkout_url'] ?? null;
    if (!$res['ok'] || !$id || !$redirect) {
        throw new RuntimeException('DPayments did not return a checkout session (HTTP ' . $res['status'] . ').');
    }
    return ['id' => (string) $id, 'redirect' => (string) $redirect];
}

/** Returns 'paid', 'failed' or 'pending' for a checkout session. */
function dp_session_status(string $sessionId): string
{
    if (str_starts_with($sessionId, 'sim_')) {
        $log = row("SELECT event FROM payment_log WHERE source = 'simulate' AND payload LIKE ? ORDER BY id DESC", ['%' . $sessionId . '%']);
        return $log ? ($log['event'] === 'payment.succeeded' ? 'paid' : 'failed') : 'pending';
    }
    $path = str_replace('{id}', rawurlencode($sessionId), (string) config('dpayments.retrieve_path'));
    $res = http_json('GET', dp_base() . $path, null, dp_headers());
    return dp_classify((string) ($res['data'][config('dpayments.status_field')] ?? ''));
}

function dp_classify(string $status): string
{
    $s = strtolower($status);
    if (in_array($s, array_map('strtolower', config('dpayments.paid_statuses', [])), true)) {
        return 'paid';
    }
    if (in_array($s, array_map('strtolower', config('dpayments.failed_statuses', [])), true)) {
        return 'failed';
    }
    return 'pending';
}

function dp_verify_signature(string $raw, string $header): bool
{
    $secret = (string) config('dpayments.webhook_secret');
    if ($secret === '' || $header === '') {
        return false;
    }
    // Contract: X-DPayments-Signature: t=<unix seconds>,v1=<hex HMAC-SHA256 of "<t>.<raw body>">, max 10 minutes old
    $parts = [];
    foreach (explode(',', $header) as $p) {
        [$k, $v] = array_pad(explode('=', trim($p), 2), 2, '');
        $parts[$k] = $v;
    }
    $t = (int) ($parts['t'] ?? 0);
    if (!$t || abs(time() - $t) > 600) {
        return false;
    }
    return hash_equals(hash_hmac('sha256', $t . '.' . $raw, $secret), (string) ($parts['v1'] ?? ''));
}

/** GET {API}/v1/ping with the key: true when DPayments accepts the credentials. */
function dp_ping(): array
{
    if (dp_mode() === 'simulate') {
        return ['ok' => false, 'error' => 'Test mode: add keys and set mode to sandbox or live first.'];
    }
    $r = http_json('GET', dp_base() . config('dpayments.ping_path', '/v1/ping'), null, dp_headers(), 10);
    return ['ok' => $r['ok'], 'error' => $r['ok'] ? '' : ('HTTP ' . $r['status'] . ' ' . substr((string) $r['error'], 0, 200))];
}

/** Applies a webhook / simulated event to its order. */
function dp_handle_event(array $event, string $source = 'webhook'): array
{
    $type = (string) ($event['type'] ?? '');
    $d = $event['data'] ?? [];
    $ref = $d['reference'] ?? ($d['metadata']['order_ref'] ?? null);
    $order = $ref ? order_by_ref((string) $ref) : null;
    dp_log($order ? (int) $order['id'] : null, $type ?: 'event', $event, $source);
    if (!$order) {
        return ['received' => true, 'handled' => false, 'reason' => 'order not found'];
    }
    if ($type === 'payment.succeeded') {
        if ((int) ($d['amount'] ?? -1) !== (int) $order['total_pence']) {
            dp_log((int) $order['id'], 'amount_mismatch', ['expected' => $order['total_pence'], 'got' => $d['amount'] ?? null], $source);
            return ['received' => true, 'handled' => false, 'reason' => 'amount mismatch', '_status' => 422];
        }
        order_mark_paid($order, 'dpayments', (string) ($d['session_id'] ?? $order['gateway_ref']));
        return ['received' => true, 'handled' => true, 'result' => 'paid', 'order' => $order['ref']];
    }
    if (in_array($type, ['payment.failed', 'payment.expired'], true) && $order['payment_status'] !== 'paid') {
        update('orders', ['status' => 'payment_failed', 'payment_status' => 'failed', 'updated_at' => now()], 'id = :id', ['id' => $order['id']]);
        return ['received' => true, 'handled' => true, 'result' => 'failed', 'order' => $order['ref']];
    }
    if ($type === 'payment.refunded') {
        update('orders', ['payment_status' => 'refunded', 'updated_at' => now()], 'id = :id', ['id' => $order['id']]);
        order_set_status(order_by_ref($order['ref']), 'refunded');
        return ['received' => true, 'handled' => true, 'result' => 'refunded', 'order' => $order['ref']];
    }
    return ['received' => true, 'handled' => false, 'reason' => 'ignored event'];
}
