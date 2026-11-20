<?php
declare(strict_types=1);

function product_by_slug(string $slug): ?array
{
    return row('SELECT p.*, c.name AS category_name, c.slug AS category_slug FROM products p LEFT JOIN categories c ON c.id = p.category_id WHERE p.slug = ? AND p.active = 1', [$slug]);
}

/** Price breaks including the base price at the MOQ, ascending by quantity. */
function product_tiers(array $p): array
{
    static $cache = [];
    $id = (int) $p['id'];
    if (!isset($cache[$id])) {
        $tiers = [['min_qty' => (int) $p['moq'], 'price_pence' => (int) $p['price_pence']]];
        foreach (rows('SELECT min_qty, price_pence FROM price_tiers WHERE product_id = ? ORDER BY min_qty', [$id]) as $t) {
            if ((int) $t['min_qty'] > (int) $p['moq']) {
                $tiers[] = ['min_qty' => (int) $t['min_qty'], 'price_pence' => (int) $t['price_pence']];
            }
        }
        $cache[$id] = $tiers;
    }
    return $cache[$id];
}

function unit_price(array $p, int $qty): int
{
    $price = (int) $p['price_pence'];
    foreach (product_tiers($p) as $t) {
        if ($qty >= $t['min_qty']) {
            $price = $t['price_pence'];
        }
    }
    return $price;
}

function lowest_price(array $p): int
{
    $t = product_tiers($p);
    return end($t)['price_pence'];
}

/** Rounds a requested quantity up to the pack multiple and at least the minimum order quantity. */
function normalise_qty(array $p, int $qty): int
{
    $step = max(1, (int) $p['step']);
    $moq = max(1, (int) $p['moq']);
    $qty = max($qty, $moq);
    return (int) (ceil($qty / $step) * $step);
}

function next_tier(array $p, int $qty): ?array
{
    foreach (product_tiers($p) as $t) {
        if ($t['min_qty'] > $qty) {
            return $t + ['more' => $t['min_qty'] - $qty];
        }
    }
    return null;
}

/* ---------- Cart (session) ---------- */

function cart(): array
{
    return $_SESSION['cart'] ?? [];
}

function cart_set(int $productId, int $qty): void
{
    if ($qty <= 0) {
        unset($_SESSION['cart'][$productId]);
        return;
    }
    $_SESSION['cart'][$productId] = $qty;
}

function cart_clear(): void
{
    $_SESSION['cart'] = [];
}

function cart_count(): int
{
    return count(cart());
}

function cart_lines(): array
{
    $cart = cart();
    if (!$cart) {
        return [];
    }
    $ids = array_map('intval', array_keys($cart));
    $in = implode(',', array_fill(0, count($ids), '?'));
    $products = rows("SELECT * FROM products WHERE active = 1 AND id IN ($in)", $ids);
    $lines = [];
    foreach ($products as $p) {
        $qty = normalise_qty($p, (int) $cart[$p['id']]);
        $unit = unit_price($p, $qty);
        $lines[] = [
            'product' => $p,
            'qty'     => $qty,
            'unit'    => $unit,
            'base'    => (int) $p['price_pence'],
            'line'    => $unit * $qty,
            'next'    => next_tier($p, $qty),
        ];
    }
    return $lines;
}

function postcode_is_poa(string $postcode): bool
{
    $pc = strtoupper(str_replace(' ', '', $postcode));
    if ($pc === '') {
        return false;
    }
    $outward = substr($pc, 0, -3);
    foreach (config('shop.poa_postcodes', []) as $prefix) {
        $prefix = strtoupper($prefix);
        if (preg_match('/\d/', $prefix)) {
            if ($outward === $prefix) {
                return true;
            }
        } elseif (preg_match('/^' . preg_quote($prefix, '/') . '\d/', $outward)) {
            return true;
        }
    }
    return false;
}

function cart_totals(array $lines, string $postcode = ''): array
{
    $subtotal = array_sum(array_column($lines, 'line'));
    $savings = 0;
    foreach ($lines as $l) {
        $savings += ($l['base'] - $l['unit']) * $l['qty'];
    }
    $free = (int) config('shop.free_delivery_pence');
    $delivery = ($subtotal >= $free || $subtotal === 0) ? 0 : (int) config('shop.delivery_pence');
    $poa = postcode_is_poa($postcode);
    $vat = (int) round(($subtotal + $delivery) * (float) config('shop.vat_rate'));
    return [
        'subtotal'       => $subtotal,
        'delivery'       => $delivery,
        'vat'            => $vat,
        'total'          => $subtotal + $delivery + $vat,
        'savings'        => $savings,
        'to_free'        => max(0, $free - $subtotal),
        'below_minimum'  => $subtotal > 0 && $subtotal < (int) config('shop.min_order_pence'),
        'poa'            => $poa,
    ];
}

/* ---------- Orders ---------- */

function order_by_ref(string $ref): ?array
{
    return row('SELECT * FROM orders WHERE ref = ?', [$ref]);
}

function order_items(int $orderId): array
{
    return rows('SELECT * FROM order_items WHERE order_id = ? ORDER BY id', [$orderId]);
}

/** Marks an order paid exactly once, then notifies the customer, admin and portal. */
function order_mark_paid(array $order, string $source, string $gatewayRef = ''): void
{
    if ($order['payment_status'] === 'paid') {
        return;
    }
    update('orders', [
        'payment_status' => 'paid',
        'status'         => 'paid',
        'paid_at'        => now(),
        'updated_at'     => now(),
        'gateway_ref'    => $gatewayRef ?: $order['gateway_ref'],
    ], 'id = :id', ['id' => $order['id']]);
    $order = row('SELECT * FROM orders WHERE id = ?', [$order['id']]);
    if ($order['payment_method'] === 'card') {
        // Card orders get their confirmation email once payment clears.
        mail_order_confirmation($order);
    } else {
        mail_send($order['email'], 'Payment received for order ' . $order['ref'], "We've received payment for order {$order['ref']} (" . money((int) $order['total_pence']) . ").\n\nWe'll email you again when it's dispatched.\n\nRezlo");
    }
    portal_event('order.paid', portal_order_payload($order) + ['source' => $source]);
}

function order_set_status(array $order, string $status): void
{
    update('orders', ['status' => $status, 'updated_at' => now()], 'id = :id', ['id' => $order['id']]);
    portal_event('order.status_changed', ['ref' => $order['ref'], 'from' => $order['status'], 'to' => $status]);
    if (in_array($status, ['dispatched', 'delivered', 'cancelled'], true)) {
        mail_send(
            $order['email'],
            'Order ' . $order['ref'] . ': ' . strtolower(status_label($status)),
            "Your order {$order['ref']} is now: " . status_label($status) . ".\n\nView it at " . abs_url('/account/orders/' . $order['ref']) . "\n\nRezlo"
        );
    }
}

const ORDER_STATUSES = ['pending_payment', 'payment_failed', 'awaiting_transfer', 'on_account', 'paid', 'processing', 'dispatched', 'delivered', 'cancelled', 'refunded'];
