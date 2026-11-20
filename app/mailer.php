<?php
declare(strict_types=1);

function mail_send(string $to, string $subject, string $body, ?string $replyTo = null): bool
{
    if (!valid_email($to)) {
        return false;
    }
    $from = (string) config('mail.from');
    $name = (string) config('mail.from_name');
    $headers = [
        'From: ' . sprintf('"%s" <%s>', addslashes($name), $from),
        'Reply-To: ' . ($replyTo && valid_email($replyTo) ? $replyTo : $from),
        'MIME-Version: 1.0',
        'Content-Type: text/plain; charset=UTF-8',
        'X-Mailer: Rezlo',
    ];
    $footer = "\n\n--\n" . config('company.name') . ' · ' . config('company.phone') . ' · ' . config('company.email') . "\n" . abs_url('/');
    if (config('mail.transport') === 'log') {
        $line = str_repeat('=', 60) . "\n" . date('c') . "\nTo: $to\nSubject: $subject\n" . implode("\n", $headers) . "\n\n$body$footer\n";
        file_put_contents(ROOT . '/data/mail.log', $line, FILE_APPEND);
        return true;
    }
    $encoded = '=?UTF-8?B?' . base64_encode($subject) . '?=';
    return @mail($to, $encoded, $body . $footer, implode("\r\n", $headers), '-f' . $from);
}

function mail_admin(string $subject, string $body, ?string $replyTo = null): void
{
    mail_send((string) config('mail.orders_to'), $subject, $body, $replyTo);
}

function order_text(array $order): string
{
    $lines = [];
    foreach (order_items((int) $order['id']) as $i) {
        $lines[] = sprintf('%s  %s × %d %s @ %s = %s', $i['sku'], $i['name'], $i['qty'], $i['unit'], money((int) $i['unit_price_pence']), money((int) $i['line_pence']));
    }
    $t = implode("\n", $lines);
    $t .= "\n\nSubtotal (ex VAT): " . money((int) $order['subtotal_pence']);
    $t .= "\nDelivery (ex VAT): " . ((int) $order['delivery_pence'] ? money((int) $order['delivery_pence']) : 'Free');
    $t .= "\nVAT: " . money((int) $order['vat_pence']);
    $t .= "\nTotal: " . money((int) $order['total_pence']);
    $t .= "\n\nDeliver to:\n" . implode("\n", array_filter([$order['ship_name'], $order['ship_company'], $order['ship_address1'], $order['ship_address2'], $order['ship_city'], $order['ship_postcode'], $order['ship_phone']]));
    if ($order['po_number']) {
        $t .= "\n\nYour PO: " . $order['po_number'];
    }
    return $t;
}

function mail_order_confirmation(array $order): void
{
    $intro = match ($order['payment_method']) {
        'bank_transfer' => "Thanks for your order. Please pay by bank transfer using the reference {$order['ref']}. We'll dispatch once the payment reaches our account.\n\n" . bank_details_text(),
        'account'       => "Thanks for your order. It has been placed on your trade account and will be invoiced on our standard credit terms.",
        default         => "Thanks for your order. Your card payment has been received.",
    };
    mail_send($order['email'], 'Order ' . $order['ref'] . ' confirmed', "$intro\n\nOrder {$order['ref']}\n\n" . order_text($order) . "\n\nTrack it at " . abs_url('/account/orders/' . $order['ref']) . "\n\nOrders are supplied under our Trade Terms and Conditions of Sale: " . abs_url('/terms'));
    mail_admin('New order ' . $order['ref'] . ' (' . money((int) $order['total_pence']) . ', ' . $order['payment_method'] . ')', order_text($order) . "\n\nCustomer email: {$order['email']}\nAdmin: " . abs_url('/admin/orders/' . $order['ref']), $order['email']);
}

function bank_details_text(): string
{
    $b = config('shop.bank_transfer');
    return "Account name: {$b['account_name']}\nSort code: " . ($b['sort_code'] ?: '(to be confirmed)') . "\nAccount number: " . ($b['account_number'] ?: '(to be confirmed)') . ($b['bank'] ? "\nBank: {$b['bank']}" : '');
}
