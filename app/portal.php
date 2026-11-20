<?php
declare(strict_types=1);

/**
 * DBS Group master portal link (platform.dbsgroup.io).
 *
 * Outbound: business events are written to the `outbox` table and POSTed to
 * the portal as signed JSON (X-DBS-Signature = HMAC-SHA256 of the body).
 * Failed deliveries retry with back-off from cron.php.
 *
 * Inbound: the portal can read and update this shop through /api/v1/*
 * using the bearer token in config('portal.inbound_key'). See app/api.php.
 */

function portal_enabled(): bool
{
    return (bool) config('portal.enabled') && config('portal.base_url') && config('portal.api_key');
}

function portal_event(string $event, array $data): void
{
    insert('outbox', [
        'event' => $event,
        'payload' => json_encode($data, JSON_UNESCAPED_SLASHES),
        'attempts' => 0,
        'next_attempt_at' => now(),
        'created_at' => now(),
    ]);
    $GLOBALS['PORTAL_PENDING'] = true;
}

function portal_endpoint(): string
{
    return rtrim((string) config('portal.base_url'), '/') . str_replace('{brand}', rawurlencode((string) config('portal.brand')), (string) config('portal.events_path'));
}

function portal_send(array $row): array
{
    $envelope = [
        'id'          => 'rezlo_evt_' . $row['id'],
        'brand'       => config('portal.brand'),
        'event'       => $row['event'],
        'occurred_at' => date('c', strtotime($row['created_at'])),
        'data'        => json_decode($row['payload'], true),
    ];
    $body = json_encode($envelope, JSON_UNESCAPED_SLASHES);
    $ch = curl_init(portal_endpoint());
    curl_setopt_array($ch, [
        CURLOPT_POST           => true,
        CURLOPT_POSTFIELDS     => $body,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT        => 8,
        CURLOPT_CONNECTTIMEOUT => 4,
        CURLOPT_HTTPHEADER     => [
            'Content-Type: application/json',
            'Authorization: Bearer ' . config('portal.api_key'),
            'X-DBS-Brand: ' . config('portal.brand'),
            'X-DBS-Event: ' . $row['event'],
            'X-DBS-Signature: sha256=' . hash_hmac('sha256', $body, (string) config('portal.signing_secret')),
            'Idempotency-Key: ' . $envelope['id'],
        ],
    ]);
    $resp = curl_exec($ch);
    $status = (int) curl_getinfo($ch, CURLINFO_RESPONSE_CODE);
    $err = curl_error($ch);
    curl_close($ch);
    return ['ok' => $status >= 200 && $status < 300, 'status' => $status, 'error' => $err ?: ($status >= 300 ? substr((string) $resp, 0, 400) : '')];
}

function portal_flush(int $limit = 20): array
{
    if (!portal_enabled()) {
        return ['sent' => 0, 'failed' => 0, 'skipped' => 'portal disabled'];
    }
    $sent = $failed = 0;
    $due = rows('SELECT * FROM outbox WHERE sent_at IS NULL AND attempts < 12 AND next_attempt_at <= ? ORDER BY id LIMIT ' . (int) $limit, [now()]);
    foreach ($due as $row) {
        $r = portal_send($row);
        if ($r['ok']) {
            update('outbox', ['sent_at' => now(), 'attempts' => $row['attempts'] + 1, 'last_error' => null], 'id = :id', ['id' => $row['id']]);
            $sent++;
        } else {
            $attempts = (int) $row['attempts'] + 1;
            $delay = min(3600 * 6, 60 * (2 ** $attempts)); // 2m, 4m, 8m … capped at 6h
            update('outbox', [
                'attempts' => $attempts,
                'last_error' => 'HTTP ' . $r['status'] . ' ' . $r['error'],
                'next_attempt_at' => date('Y-m-d H:i:s', time() + $delay),
            ], 'id = :id', ['id' => $row['id']]);
            $failed++;
        }
    }
    return ['sent' => $sent, 'failed' => $failed];
}

function portal_order_payload(array $o): array
{
    $c = $o['customer_id'] ? row('SELECT company_name, email FROM customers WHERE id = ?', [$o['customer_id']]) : null;
    return [
        'ref'            => $o['ref'],
        'status'         => $o['status'],
        'payment_method' => $o['payment_method'],
        'payment_status' => $o['payment_status'],
        'currency'       => config('shop.currency', 'GBP'),
        'subtotal'       => (int) $o['subtotal_pence'],
        'delivery'       => (int) $o['delivery_pence'],
        'vat'            => (int) $o['vat_pence'],
        'total'          => (int) $o['total_pence'],
        'amounts_in'     => 'minor_units',
        'customer'       => ['id' => $o['customer_id'], 'email' => $o['email'], 'company' => $c['company_name'] ?? $o['ship_company']],
        'po_number'      => $o['po_number'],
        'items'          => array_map(fn($i) => ['sku' => $i['sku'], 'name' => $i['name'], 'qty' => (int) $i['qty'], 'unit_price' => (int) $i['unit_price_pence'], 'line_total' => (int) $i['line_pence']], order_items((int) $o['id'])),
        'created_at'     => $o['created_at'],
        'paid_at'        => $o['paid_at'],
    ];
}

function portal_customer_payload(array $c): array
{
    return [
        'id' => (int) $c['id'], 'email' => $c['email'], 'contact_name' => $c['contact_name'],
        'company_name' => $c['company_name'], 'company_number' => $c['company_number'], 'vat_number' => $c['vat_number'],
        'business_type' => $c['business_type'], 'phone' => $c['phone'], 'postcode' => $c['postcode'],
        'status' => $c['status'], 'credit_terms' => (bool) $c['credit_terms'], 'credit_limit' => (int) $c['credit_limit_pence'],
        'created_at' => $c['created_at'],
    ];
}

/** Try to deliver new events after the response has been sent, so pages stay fast. */
register_shutdown_function(function () {
    if (empty($GLOBALS['PORTAL_PENDING']) || !portal_enabled()) {
        return;
    }
    if (function_exists('fastcgi_finish_request')) {
        fastcgi_finish_request();
    }
    try {
        portal_flush(5);
    } catch (Throwable $e) {
        // left in the outbox for cron.php to retry
    }
});
