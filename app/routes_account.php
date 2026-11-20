<?php
declare(strict_types=1);

const BUSINESS_TYPES = ['Flooring contractor', 'Painter and decorator', 'Tiler', 'Builder / main contractor', 'Fit-out / interiors', 'Property management / housing', 'Facilities management', 'Retailer / merchant', 'Other trade'];

function account_register(): void
{
    if (current_customer()) redirect('/account');
    view('account/register', ['title' => 'Open a trade account']);
}

function account_register_post(): void
{
    $f = [];
    foreach (['contact_name', 'company_name', 'company_number', 'vat_number', 'business_type', 'phone', 'address1', 'address2', 'city', 'postcode', 'email'] as $k) {
        $f[$k] = (string) input($k);
    }
    $f['email'] = strtolower($f['email']);
    $f['postcode'] = strtoupper($f['postcode']);
    $pw = (string) ($_POST['password'] ?? '');
    $errors = [];
    if ($f['contact_name'] === '') $errors[] = 'Enter your name.';
    if ($f['company_name'] === '') $errors[] = 'Enter your company or trading name.';
    if (!valid_email($f['email'])) $errors[] = 'Enter a valid email address.';
    elseif (val('SELECT id FROM customers WHERE email = ?', [$f['email']])) $errors[] = 'An account with that email already exists. Sign in instead.';
    if ($f['phone'] === '') $errors[] = 'Enter a phone number.';
    if ($f['address1'] === '' || $f['city'] === '') $errors[] = 'Enter your business address.';
    if (!valid_postcode($f['postcode'])) $errors[] = 'Enter a valid UK postcode.';
    if (strlen($pw) < 8) $errors[] = 'Use a password of at least 8 characters.';
    if ($pw !== ($_POST['password_confirm'] ?? '')) $errors[] = 'The passwords do not match.';
    if (input('accept_terms') !== '1') $errors[] = 'Confirm you accept the trade terms and privacy policy.';
    if ($errors) {
        keep_old();
        foreach ($errors as $er) flash('error', $er);
        redirect('/register');
    }
    $status = config('shop.require_approval') ? 'pending' : 'approved';
    $id = insert('customers', $f + [
        'password_hash' => password_hash($pw, PASSWORD_DEFAULT),
        'status' => $status, 'credit_terms' => 0, 'credit_limit_pence' => 0,
        'marketing' => input('marketing') === '1' ? 1 : 0, 'created_at' => now(),
    ]);
    session_regenerate_id(true);
    $_SESSION['customer_id'] = $id;
    $c = row('SELECT * FROM customers WHERE id = ?', [$id]);
    portal_event('customer.registered', portal_customer_payload($c));
    mail_admin('New trade account application: ' . $f['company_name'], "Company: {$f['company_name']}\nCompany no: {$f['company_number']}\nVAT: {$f['vat_number']}\nType: {$f['business_type']}\nContact: {$f['contact_name']}\nEmail: {$f['email']}\nPhone: {$f['phone']}\nPostcode: {$f['postcode']}\n\nReview: " . abs_url('/admin/customers/' . $id), $f['email']);
    mail_send($f['email'], 'Your Rezlo trade account application', "Hi {$f['contact_name']},\n\nThanks for applying for a Rezlo trade account for {$f['company_name']}." . ($status === 'pending' ? "\n\nWe check new accounts within one working day and will email you as soon as you can order. In the meantime you can browse prices and send quote requests." : "\n\nYour account is ready to use.") . "\n\nRezlo");
    flash('success', $status === 'pending' ? "Application received. We'll email you once your account is approved — usually within one working day." : 'Your trade account is ready.');
    redirect('/account');
}

function account_login(): void
{
    if (current_customer()) redirect('/account');
    view('account/login', ['title' => 'Sign in']);
}

function login_throttled(string $key): bool
{
    $a = $_SESSION['login_attempts'][$key] ?? ['n' => 0, 't' => time()];
    if (time() - $a['t'] > 900) $a = ['n' => 0, 't' => time()];
    return $a['n'] >= 8;
}

function login_failed(string $key): void
{
    $a = $_SESSION['login_attempts'][$key] ?? ['n' => 0, 't' => time()];
    $a['n']++;
    $_SESSION['login_attempts'][$key] = $a;
}

function account_login_post(): void
{
    $email = strtolower((string) input('email'));
    if (login_throttled('c')) {
        flash('error', 'Too many attempts. Wait 15 minutes or reset your password.');
        redirect('/login');
    }
    $c = row('SELECT * FROM customers WHERE email = ?', [$email]);
    if (!$c || !password_verify((string) ($_POST['password'] ?? ''), $c['password_hash'])) {
        login_failed('c');
        keep_old();
        flash('error', 'That email and password do not match an account.');
        redirect('/login');
    }
    session_regenerate_id(true);
    $_SESSION['customer_id'] = (int) $c['id'];
    unset($_SESSION['login_attempts']);
    $to = $_SESSION['after_login'] ?? '/account';
    unset($_SESSION['after_login']);
    redirect(str_starts_with($to, '/') ? $to : '/account');
}

function account_logout(): void
{
    unset($_SESSION['customer_id']);
    session_regenerate_id(true);
    redirect('/');
}

function account_forgot(): void
{
    view('account/forgot', ['title' => 'Reset your password']);
}

function account_forgot_post(): void
{
    $email = strtolower((string) input('email'));
    $c = row('SELECT * FROM customers WHERE email = ?', [$email]);
    if ($c) {
        $token = bin2hex(random_bytes(24));
        update('customers', ['reset_token' => hash('sha256', $token), 'reset_expires' => date('Y-m-d H:i:s', time() + 3600)], 'id = :id', ['id' => $c['id']]);
        mail_send($email, 'Reset your Rezlo password', "Use this link within an hour to set a new password:\n\n" . abs_url('/reset-password?token=' . $token) . "\n\nIf you didn't ask for this, ignore this email.");
    }
    flash('success', 'If that email has an account, we have sent a reset link to it.');
    redirect('/login');
}

function account_reset(): void
{
    view('account/reset', ['title' => 'Choose a new password', 'token' => (string) input('token')]);
}

function account_reset_post(): void
{
    $token = (string) input('token');
    $c = row('SELECT * FROM customers WHERE reset_token = ? AND reset_expires > ?', [hash('sha256', $token), now()]);
    $pw = (string) ($_POST['password'] ?? '');
    if (!$c) {
        flash('error', 'That reset link has expired. Request a new one.');
        redirect('/forgot-password');
    }
    if (strlen($pw) < 8 || $pw !== ($_POST['password_confirm'] ?? '')) {
        flash('error', 'Use at least 8 characters and type the same password twice.');
        redirect('/reset-password?token=' . urlencode($token));
    }
    update('customers', ['password_hash' => password_hash($pw, PASSWORD_DEFAULT), 'reset_token' => null, 'reset_expires' => null], 'id = :id', ['id' => $c['id']]);
    flash('success', 'Password changed. Sign in with your new password.');
    redirect('/login');
}

function account_home(): void
{
    $c = require_customer();
    $orders = rows('SELECT * FROM orders WHERE customer_id = ? ORDER BY id DESC', [$c['id']]);
    view('account/home', [
        'title' => 'Your trade account', 'c' => $c, 'orders' => $orders,
        'outstanding' => account_outstanding((int) $c['id']),
    ]);
}

function account_order(string $ref): void
{
    $c = require_customer();
    $o = order_by_ref($ref);
    if (!$o || (int) $o['customer_id'] !== (int) $c['id']) not_found();
    view('account/order', ['title' => 'Order ' . $o['ref'], 'o' => $o, 'items' => order_items((int) $o['id'])]);
}

function account_reorder(string $ref): void
{
    $c = require_customer();
    $o = order_by_ref($ref);
    if (!$o || (int) $o['customer_id'] !== (int) $c['id']) not_found();
    foreach (order_items((int) $o['id']) as $i) {
        $p = row('SELECT * FROM products WHERE id = ? AND active = 1', [$i['product_id']]);
        if ($p) cart_set((int) $p['id'], normalise_qty($p, (int) $i['qty']));
    }
    flash('success', "Items from {$o['ref']} added to your order at today's prices.");
    redirect('/cart');
}

function account_details(): void
{
    $c = require_customer();
    view('account/details', ['title' => 'Account details', 'c' => $c]);
}

function account_details_post(): void
{
    $c = require_customer();
    $f = [];
    foreach (['contact_name', 'company_name', 'company_number', 'vat_number', 'business_type', 'phone', 'address1', 'address2', 'city', 'postcode'] as $k) {
        $f[$k] = (string) input($k);
    }
    $f['postcode'] = strtoupper($f['postcode']);
    $f['marketing'] = input('marketing') === '1' ? 1 : 0;
    if ($f['contact_name'] === '' || $f['company_name'] === '' || !valid_postcode($f['postcode'])) {
        flash('error', 'Name, company and a valid postcode are required.');
        redirect('/account/details');
    }
    $new = (string) ($_POST['password'] ?? '');
    if ($new !== '') {
        if (strlen($new) < 8) {
            flash('error', 'Use a password of at least 8 characters.');
            redirect('/account/details');
        }
        $f['password_hash'] = password_hash($new, PASSWORD_DEFAULT);
    }
    update('customers', $f, 'id = :id', ['id' => $c['id']]);
    portal_event('customer.updated', portal_customer_payload(row('SELECT * FROM customers WHERE id = ?', [$c['id']])));
    flash('success', 'Details saved.');
    redirect('/account/details');
}
