<?php
declare(strict_types=1);

function e($v): string
{
    return htmlspecialchars((string) $v, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

function url(string $path = '/'): string
{
    return $path;
}

function abs_url(string $path = '/'): string
{
    return rtrim((string) config('app.url'), '/') . $path;
}

function asset(string $path): string
{
    $file = ROOT . '/assets/' . ltrim($path, '/');
    $v = is_file($file) ? filemtime($file) : 1;
    return '/assets/' . ltrim($path, '/') . '?v=' . $v;
}

function redirect(string $to): never
{
    header('Location: ' . $to, true, 303);
    exit;
}

function money(int $pence, bool $symbol = true): string
{
    return ($symbol ? '£' : '') . number_format($pence / 100, 2);
}

/** Whole-pound amounts in running text: £500 rather than £500.00. */
function money_whole(int $pence): string
{
    return $pence % 100 === 0 ? '£' . number_format($pence / 100) : money($pence);
}

function input(string $key, $default = '')
{
    $v = $_POST[$key] ?? $_GET[$key] ?? $default;
    return is_string($v) ? trim($v) : $v;
}

function slugify(string $s): string
{
    $s = strtolower(trim(preg_replace('/[^A-Za-z0-9]+/', '-', $s), '-'));
    return $s ?: 'item';
}

function make_ref(string $prefix): string
{
    return $prefix . '-' . date('ymd') . '-' . strtoupper(substr(bin2hex(random_bytes(3)), 0, 5));
}

/* ---------- CSRF ---------- */

function csrf_token(): string
{
    if (empty($_SESSION['csrf'])) {
        $_SESSION['csrf'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf'];
}

function csrf_field(): string
{
    return '<input type="hidden" name="_csrf" value="' . e(csrf_token()) . '">';
}

function csrf_check(): void
{
    $t = $_POST['_csrf'] ?? '';
    if (!is_string($t) || !hash_equals(csrf_token(), $t)) {
        http_response_code(419);
        exit('Your session expired. Go back, refresh the page and try again.');
    }
}

/* ---------- Flash and old input ---------- */

function flash(string $type, string $msg): void
{
    $_SESSION['flash'][] = [$type, $msg];
}

function flashes(): array
{
    $f = $_SESSION['flash'] ?? [];
    unset($_SESSION['flash']);
    return $f;
}

function keep_old(): void
{
    $data = $_POST;
    unset($data['password'], $data['password_confirm'], $data['_csrf']);
    $_SESSION['old'] = $data;
}

function old(string $key, $default = ''): string
{
    return e($_SESSION['old_now'][$key] ?? $default);
}

/* ---------- Views ---------- */

function view(string $name, array $vars = [], ?string $layout = 'layout'): void
{
    $_SESSION['old_now'] = $_SESSION['old'] ?? [];
    unset($_SESSION['old']);
    extract($vars);
    ob_start();
    require ROOT . '/views/' . $name . '.php';
    $content = ob_get_clean();
    if ($layout) {
        require ROOT . '/views/' . $layout . '.php';
    } else {
        echo $content;
    }
}

function partial(string $name, array $vars = []): void
{
    extract($vars);
    require ROOT . '/views/' . $name . '.php';
}

function json_response($data, int $status = 200): never
{
    http_response_code($status);
    header('Content-Type: application/json');
    echo json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
    exit;
}

function not_found(): never
{
    http_response_code(404);
    view('404', ['title' => 'Page not found']);
    exit;
}

/* ---------- Auth ---------- */

function current_customer(): ?array
{
    static $c = false;
    if ($c === false) {
        $c = !empty($_SESSION['customer_id'])
            ? row('SELECT * FROM customers WHERE id = ?', [$_SESSION['customer_id']])
            : null;
    }
    return $c;
}

function require_customer(): array
{
    $c = current_customer();
    if (!$c) {
        $_SESSION['after_login'] = $_SERVER['REQUEST_URI'] ?? '/account';
        flash('info', 'Sign in to your trade account to continue.');
        redirect('/login');
    }
    return $c;
}

function customer_can_order(?array $c): bool
{
    if (!$c) {
        return false;
    }
    return !config('shop.require_approval') || $c['status'] === 'approved';
}

function current_admin(): ?array
{
    static $a = false;
    if ($a === false) {
        $a = !empty($_SESSION['admin_id'])
            ? row('SELECT * FROM admins WHERE id = ?', [$_SESSION['admin_id']])
            : null;
    }
    return $a;
}

function require_admin(): array
{
    $a = current_admin();
    if (!$a) {
        redirect('/admin/login');
    }
    return $a;
}

/* ---------- Display ---------- */

/** Renders a material swatch ("oak:#b8895a") as a styled element. */
function swatch(string $spec, string $extra = '', string $image = ''): string
{
    if ($image) {
        return '<div class="swatch has-image ' . e($extra) . '"><img src="' . e($image) . '" alt="" loading="lazy"></div>';
    }
    [$kind, $colour] = array_pad(explode(':', $spec ?: 'paint:#e9e6dc', 2), 2, '#e9e6dc');
    return '<div class="swatch swatch-' . e($kind) . ' ' . e($extra) . '" style="--c:' . e($colour) . '" aria-hidden="true"></div>';
}

function status_label(string $s): string
{
    return [
        'pending_payment'   => 'Awaiting card payment',
        'awaiting_transfer' => 'Awaiting bank transfer',
        'on_account'        => 'On account',
        'paid'              => 'Paid',
        'processing'        => 'Processing',
        'dispatched'        => 'Dispatched',
        'delivered'         => 'Delivered',
        'cancelled'         => 'Cancelled',
        'refunded'          => 'Refunded',
        'payment_failed'    => 'Payment failed',
    ][$s] ?? ucfirst(str_replace('_', ' ', $s));
}

function company_line(): string
{
    $parts = [config('company.name') . ', registered in ' . config('company.jurisdiction')];
    if (config('company.company_number')) {
        $parts[] = 'company number ' . config('company.company_number');
    }
    if (config('company.vat_number')) {
        $parts[] = 'VAT ' . config('company.vat_number');
    }
    return implode(', ', $parts) . '. Registered office: ' . implode(', ', array_slice(config('company.address'), 0, 4)) . '.';
}

function valid_email(string $e): bool
{
    return (bool) filter_var($e, FILTER_VALIDATE_EMAIL);
}

function valid_postcode(string $p): bool
{
    return (bool) preg_match('/^[A-Z]{1,2}[0-9][0-9A-Z]?\s?[0-9][A-Z]{2}$/i', trim($p));
}

function plural_unit(string $unit): string
{
    if (preg_match('/(²|each|kg|L)$/u', $unit)) return $unit;
    if (preg_match('/(x|s|ch|sh)$/', $unit)) return $unit . 'es';
    return $unit . 's';
}
