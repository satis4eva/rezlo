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

    $body = [
        'merchant_id'      => config('dpayments.merchant_id'),
        'amount'           => (int) $order['total_pence'],
        'currency'         => config('shop.currency', 'GBP'),
        'reference'        => $order['ref'],
        'description'      => 'Rezlo order ' . $order['ref'],
        'customer'         => ['email' => $order['email'], 'name' => $order['ship_name'], 'company' => $order['ship_company']],
        'billing_address'  => ['line1' => $order['ship_address1'], 'line2' => $order['ship_address2'], 'city' => $order['ship_city'], 'postcode' => $order['ship_postcode'], 'country' => 'GB'],
        'return_url'       => $returnUrl,
        'cancel_url'       => abs_url('/checkout?cancelled=' . urlencode($order['ref'])),
        'notification_url' => abs_url('/webhooks/dpayments'),
        'metadata'         => ['order_ref' => $order['ref'], 'brand' => config('portal.brand', 'rezlo')],
    ];
    $res = http_json('POST', dp_base() . config('dpayments.create_path'), $body, dp_headers());
    dp_log((int) $order['id'], 'session.create_response', $res);
    $id = $res['data'][config('dpayments.id_field')] ?? null;
    $redirect = $res['data'][config('dpayments.redirect_field')] ?? null;
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
    $expected = hash_hmac('sha256', $raw, $secret);
    // Accept "abc123", "sha256=abc123" or "t=...,v1=abc123" formats.
    foreach (preg_split('/[,\s]+/', $header) as $part) {
        $sig = preg_replace('/^(sha256=|v1=)/', '', trim($part));
        if ($sig !== '' && hash_equals($expected, $sig)) {
            return true;
        }
    }
    return false;
}

/** Applies a webhook / simulated event to its order. */
function dp_handle_event(array $event, string $source = 'webhook'): array
{
    $obj = $event['data']['object'] ?? $event['data'] ?? $event;
    $ref = $obj['metadata']['order_ref'] ?? $obj['reference'] ?? $event['reference'] ?? null;
    $order = $ref ? order_by_ref((string) $ref) : null;
    dp_log($order ? (int) $order['id'] : null, (string) ($event['type'] ?? 'event'), $event, $source);
    if (!$order) {
        return ['handled' => false, 'reason' => 'order not found'];
    }
    $statusRaw = (string) ($obj[config('dpayments.status_field')] ?? $obj['status'] ?? '');
    if ($statusRaw === '' && isset($event['type'])) {
        $statusRaw = str_contains($event['type'], 'succeed') || str_contains($event['type'], 'paid') ? 'paid' : (str_contains($event['type'], 'fail') ? 'failed' : '');
    }
    $result = dp_classify($statusRaw);
    $amount = isset($obj['amount']) ? (int) $obj['amount'] : null;
    if ($result === 'paid') {
        if ($amount !== null && $amount !== (int) $order['total_pence']) {
            dp_log((int) $order['id'], 'amount_mismatch', ['expected' => $order['total_pence'], 'got' => $amount], $source);
            return ['handled' => false, 'reason' => 'amount mismatch'];
        }
        order_mark_paid($order, 'dpayments', (string) ($obj['id'] ?? ''));
    } elseif ($result === 'failed' && $order['payment_status'] !== 'paid') {
        update('orders', ['status' => 'payment_failed', 'payment_status' => 'failed', 'updated_at' => now()], 'id = :id', ['id' => $order['id']]);
    }
    return ['handled' => true, 'result' => $result, 'order' => $order['ref']];
}
