<?php
declare(strict_types=1);

function cart_show(): void
{
    $lines = cart_lines();
    view('cart', ['title' => 'Your order', 'lines' => $lines, 'totals' => cart_totals($lines)]);
}

function cart_add(): void
{
    $p = row('SELECT * FROM products WHERE id = ? AND active = 1', [(int) input('product_id')]);
    if (!$p) {
        redirect('/shop');
    }
    $requested = max(1, (int) input('qty', 1));
    $existing = (int) (cart()[$p['id']] ?? 0);
    $qty = normalise_qty($p, $existing + $requested);
    cart_set((int) $p['id'], $qty);
    $msg = sprintf('%s: %d × %s in your order at %s each.', $p['name'], $qty, $p['unit'], money(unit_price($p, $qty)));
    if ($existing + $requested !== $qty) {
        $msg .= ' Quantity rounded to the minimum order or pack size.';
    }
    flash('success', $msg);
    redirect(input('return') === 'product' ? '/product/' . $p['slug'] : '/cart');
}

function cart_update(): void
{
    $qtys = $_POST['qty'] ?? [];
    if (is_array($qtys)) {
        foreach ($qtys as $id => $qty) {
            $p = row('SELECT * FROM products WHERE id = ?', [(int) $id]);
            if (!$p) continue;
            $qty = (int) $qty;
            cart_set((int) $id, $qty <= 0 ? 0 : normalise_qty($p, $qty));
        }
    }
    if ($remove = (int) input('remove')) {
        cart_set($remove, 0);
    }
    flash('success', 'Order updated.');
    redirect('/cart');
}

function account_outstanding(int $customerId): int
{
    return (int) val("SELECT COALESCE(SUM(total_pence),0) FROM orders WHERE customer_id = ? AND payment_method = 'account' AND payment_status != 'paid' AND status NOT IN ('cancelled','refunded')", [$customerId]);
}

function payment_methods(array $customer, int $total): array
{
    $m = ['card' => 'Pay by card (secure DPayments checkout)'];
    if (config('shop.bank_transfer.enabled')) {
        $m['bank_transfer'] = 'Bank transfer (dispatched once payment clears)';
    }
    if ((int) $customer['credit_terms'] === 1) {
        $available = (int) $customer['credit_limit_pence'] - account_outstanding((int) $customer['id']);
        if ($available >= $total) {
            $m['account'] = 'Pay on account, 30 days from invoice (' . money($available) . ' available)';
        }
    }
    return $m;
}

function checkout_show(): void
{
    $c = require_customer();
    if (!customer_can_order($c)) {
        flash('info', "Your trade account is waiting for approval. You can still send this order as a quote request.");
        redirect('/cart');
    }
    $lines = cart_lines();
    if (!$lines) {
        redirect('/cart');
    }
    if ($ref = input('cancelled')) {
        flash('info', "Card payment for $ref was cancelled. Your order is saved — choose a payment method to try again.");
    }
    $postcode = (string) ($_SESSION['old']['ship_postcode'] ?? $c['postcode'] ?? '');
    $totals = cart_totals($lines, $postcode);
    view('checkout', ['title' => 'Checkout', 'c' => $c, 'lines' => $lines, 'totals' => $totals, 'methods' => payment_methods($c, $totals['total'])]);
}

function checkout_place(): void
{
    $c = require_customer();
    if (!customer_can_order($c)) {
        redirect('/cart');
    }
    $lines = cart_lines();
    if (!$lines) {
        redirect('/cart');
    }
    $f = [];
    foreach (['ship_name', 'ship_company', 'ship_phone', 'ship_address1', 'ship_address2', 'ship_city', 'ship_postcode', 'po_number', 'notes', 'delivery_pref'] as $k) {
        $f[$k] = (string) input($k);
    }
    $f['ship_postcode'] = strtoupper($f['ship_postcode']);
    $totals = cart_totals($lines, $f['ship_postcode']);
    $method = (string) input('payment_method');
    $methods = payment_methods($c, $totals['total']);

    $errors = [];
    foreach (['ship_name' => 'contact name', 'ship_phone' => 'site phone number', 'ship_address1' => 'delivery address', 'ship_city' => 'town or city'] as $k => $label) {
        if ($f[$k] === '') $errors[] = "Enter the $label.";
    }
    if (!valid_postcode($f['ship_postcode'])) $errors[] = 'Enter a valid UK postcode.';
    if ($totals['below_minimum']) $errors[] = 'The minimum trade order is ' . money((int) config('shop.min_order_pence')) . ' ex VAT.';
    if ($totals['poa']) $errors[] = 'We quote delivery to this postcode separately. Send the order as a quote request and we will price delivery for you.';
    if (!isset($methods[$method])) $errors[] = 'Choose a payment method.';
    if (input('accept_terms') !== '1') $errors[] = 'Confirm you accept the Trade Terms and Conditions of Sale.';
    if ($errors) {
        keep_old();
        foreach ($errors as $er) flash('error', $er);
        redirect('/checkout');
    }

    $status = ['card' => 'pending_payment', 'bank_transfer' => 'awaiting_transfer', 'account' => 'on_account'][$method];
    db()->beginTransaction();
    $ref = make_ref('RZ');
    $orderId = insert('orders', $f + [
        'ref' => $ref, 'customer_id' => $c['id'], 'status' => $status, 'payment_method' => $method,
        'payment_status' => 'unpaid', 'subtotal_pence' => $totals['subtotal'], 'delivery_pence' => $totals['delivery'],
        'vat_pence' => $totals['vat'], 'total_pence' => $totals['total'], 'email' => $c['email'],
        'created_at' => now(), 'updated_at' => now(),
    ]);
    foreach ($lines as $l) {
        insert('order_items', [
            'order_id' => $orderId, 'product_id' => $l['product']['id'], 'sku' => $l['product']['sku'],
            'name' => $l['product']['name'], 'unit' => $l['product']['unit'], 'qty' => $l['qty'],
            'unit_price_pence' => $l['unit'], 'line_pence' => $l['line'],
        ]);
        q('UPDATE products SET stock = stock - ? WHERE id = ?', [$l['qty'], $l['product']['id']]);
    }
    db()->commit();
    $order = order_by_ref($ref);
    portal_event('order.created', portal_order_payload($order));
    cart_clear();

    if ($method === 'card') {
        order_start_card_payment($order);
    }
    mail_order_confirmation($order);
    redirect('/order/' . $ref . '/done');
}

function order_start_card_payment(array $order): never
{
    try {
        $s = dp_create_session($order);
        update('orders', ['gateway_ref' => $s['id'], 'status' => 'pending_payment', 'updated_at' => now()], 'id = :id', ['id' => $order['id']]);
        redirect($s['redirect']);
    } catch (Throwable $e) {
        dp_log((int) $order['id'], 'session.error', $e->getMessage());
        update('orders', ['status' => 'payment_failed', 'updated_at' => now()], 'id = :id', ['id' => $order['id']]);
        flash('error', "We couldn't start the card payment. Your order {$order['ref']} is saved — try again, or pay by bank transfer by calling " . config('company.phone') . '.');
        redirect('/account/orders/' . $order['ref']);
    }
}

function own_order_or_404(string $ref): array
{
    $o = order_by_ref($ref);
    $c = current_customer();
    if (!$o || ((!$c || (int) $c['id'] !== (int) $o['customer_id']) && !current_admin())) {
        not_found();
    }
    return $o;
}

function order_pay(string $ref): void
{
    $o = own_order_or_404($ref);
    if ($o['payment_status'] === 'paid' || in_array($o['status'], ['cancelled', 'refunded'], true)) {
        redirect('/account/orders/' . $ref);
    }
    update('orders', ['payment_method' => 'card'], 'id = :id', ['id' => $o['id']]);
    order_start_card_payment(order_by_ref($ref));
}

function checkout_return(): void
{
    $o = own_order_or_404((string) input('order'));
    if ($o['payment_status'] !== 'paid' && $o['gateway_ref']) {
        $status = dp_session_status($o['gateway_ref']);
        if ($status === 'paid') {
            order_mark_paid($o, 'return_check', $o['gateway_ref']);
        } elseif ($status === 'failed') {
            update('orders', ['status' => 'payment_failed', 'payment_status' => 'failed', 'updated_at' => now()], 'id = :id', ['id' => $o['id']]);
        }
    }
    redirect('/order/' . $o['ref'] . '/done');
}

function order_done(string $ref): void
{
    $o = own_order_or_404($ref);
    view('order_done', ['title' => 'Order ' . $o['ref'], 'o' => $o, 'items' => order_items((int) $o['id'])]);
}

function order_proforma(string $ref): void
{
    $o = own_order_or_404($ref);
    view('proforma', ['title' => 'Pro forma ' . $o['ref'], 'o' => $o, 'items' => order_items((int) $o['id'])], null);
}

/* ---------- Built-in test checkout (dpayments.mode = simulate) ---------- */

function pay_simulate(): void
{
    if (dp_mode() !== 'simulate') {
        not_found();
    }
    $o = own_order_or_404((string) input('order'));
    view('pay_simulate', ['title' => 'Test payment', 'o' => $o, 'session' => (string) input('session')], null);
}

function pay_simulate_post(): void
{
    if (dp_mode() !== 'simulate') {
        not_found();
    }
    $o = own_order_or_404((string) input('order'));
    $session = (string) input('session');
    if ($session !== $o['gateway_ref']) {
        not_found();
    }
    $approve = input('outcome') === 'approve';
    $event = [
        'type' => $approve ? 'payment.succeeded' : 'payment.failed',
        'data' => ['object' => [
            'id' => $session, 'status' => $approve ? 'paid' : 'declined', 'amount' => (int) $o['total_pence'],
            'currency' => 'GBP', 'reference' => $o['ref'], 'metadata' => ['order_ref' => $o['ref']],
        ]],
    ];
    dp_handle_event($event, 'simulate');
    redirect('/checkout/return?order=' . urlencode($o['ref']));
}

/* ---------- DPayments webhook ---------- */

function webhook_dpayments(): void
{
    $raw = (string) file_get_contents('php://input');
    $hdrName = 'HTTP_' . strtoupper(str_replace('-', '_', (string) config('dpayments.signature_header')));
    $sig = (string) ($_SERVER[$hdrName] ?? '');
    if (!dp_verify_signature($raw, $sig)) {
        dp_log(null, 'webhook.bad_signature', $raw);
        json_response(['error' => 'invalid signature'], 401);
    }
    $event = json_decode($raw, true);
    if (!is_array($event)) {
        json_response(['error' => 'invalid json'], 400);
    }
    json_response(dp_handle_event($event, 'webhook'));
}
