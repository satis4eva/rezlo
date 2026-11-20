<?php
declare(strict_types=1);

function admin_view(string $name, array $vars = []): void
{
    view('admin/' . $name, $vars + ['admin' => current_admin()], 'admin/layout');
}

function admin_login(): void
{
    ensure_admin();
    if (current_admin()) redirect('/admin');
    view('admin/login', ['title' => 'Admin sign in'], 'admin/layout');
}

function admin_login_post(): void
{
    if (login_throttled('a')) {
        flash('error', 'Too many attempts. Wait 15 minutes.');
        redirect('/admin/login');
    }
    $a = row('SELECT * FROM admins WHERE email = ?', [strtolower((string) input('email'))]);
    if (!$a || !password_verify((string) ($_POST['password'] ?? ''), $a['password_hash'])) {
        login_failed('a');
        flash('error', 'Wrong email or password.');
        redirect('/admin/login');
    }
    session_regenerate_id(true);
    $_SESSION['admin_id'] = (int) $a['id'];
    unset($_SESSION['login_attempts']);
    if (password_verify((string) config('app.admin_password'), $a['password_hash']) && in_array(config('app.admin_password'), ['CHANGE-ME', 'admin', 'password'], true)) {
        flash('error', 'You are using the default admin password. Change it now.');
        redirect('/admin/password');
    }
    redirect('/admin');
}

function admin_logout(): void
{
    unset($_SESSION['admin_id']);
    redirect('/admin/login');
}

function admin_password(): void
{
    require_admin();
    admin_view('password', ['title' => 'Change password']);
}

function admin_password_post(): void
{
    $a = require_admin();
    $pw = (string) ($_POST['password'] ?? '');
    if (strlen($pw) < 10 || $pw !== ($_POST['password_confirm'] ?? '')) {
        flash('error', 'Use at least 10 characters and type it twice.');
        redirect('/admin/password');
    }
    update('admins', ['password_hash' => password_hash($pw, PASSWORD_DEFAULT)], 'id = :id', ['id' => $a['id']]);
    flash('success', 'Password changed.');
    redirect('/admin');
}

function admin_dashboard(): void
{
    require_admin();
    $since = date('Y-m-d 00:00:00', strtotime('-30 days'));
    admin_view('dashboard', [
        'title' => 'Dashboard',
        'stats' => [
            'Paid sales, 30 days (inc VAT)' => money((int) val("SELECT COALESCE(SUM(total_pence),0) FROM orders WHERE payment_status = 'paid' AND created_at >= ?", [$since])),
            'Orders, 30 days' => (int) val('SELECT COUNT(*) FROM orders WHERE created_at >= ?', [$since]),
            'Awaiting payment' => (int) val("SELECT COUNT(*) FROM orders WHERE status IN ('awaiting_transfer','pending_payment','payment_failed')"),
            'Accounts to approve' => (int) val("SELECT COUNT(*) FROM customers WHERE status = 'pending'"),
            'New enquiries' => (int) val("SELECT COUNT(*) FROM enquiries WHERE status = 'new'"),
            'Low stock lines' => (int) val('SELECT COUNT(*) FROM products WHERE active = 1 AND stock < 20'),
        ],
        'orders' => rows('SELECT * FROM orders ORDER BY id DESC LIMIT 10'),
        'pending' => rows("SELECT * FROM customers WHERE status = 'pending' ORDER BY id DESC LIMIT 10"),
    ]);
}

function admin_orders(): void
{
    require_admin();
    $status = (string) input('status');
    $qs = (string) input('q');
    $where = '1=1';
    $p = [];
    if ($status !== '') { $where .= ' AND status = ?'; $p[] = $status; }
    if ($qs !== '') { $where .= ' AND (ref LIKE ? OR email LIKE ? OR ship_company LIKE ? OR po_number LIKE ?)'; array_push($p, "%$qs%", "%$qs%", "%$qs%", "%$qs%"); }
    admin_view('orders', ['title' => 'Orders', 'orders' => rows("SELECT * FROM orders WHERE $where ORDER BY id DESC LIMIT 300", $p), 'status' => $status, 'qs' => $qs]);
}

function admin_order(string $ref): void
{
    require_admin();
    $o = order_by_ref($ref) ?? not_found();
    admin_view('order', [
        'title' => 'Order ' . $ref, 'o' => $o, 'items' => order_items((int) $o['id']),
        'customer' => $o['customer_id'] ? row('SELECT * FROM customers WHERE id = ?', [$o['customer_id']]) : null,
        'log' => rows('SELECT * FROM payment_log WHERE order_id = ? ORDER BY id DESC', [$o['id']]),
    ]);
}

function admin_order_post(string $ref): void
{
    require_admin();
    $o = order_by_ref($ref) ?? not_found();
    $action = (string) input('action');
    if ($action === 'mark_paid') {
        order_mark_paid($o, 'admin');
        dp_log((int) $o['id'], 'manual.mark_paid', ['by' => current_admin()['email']], 'admin');
        flash('success', 'Marked as paid.');
    } elseif ($action === 'status' && in_array(input('status'), ORDER_STATUSES, true)) {
        order_set_status($o, (string) input('status'));
        flash('success', 'Status updated to ' . status_label((string) input('status')) . '.');
    } elseif ($action === 'note') {
        update('orders', ['notes' => trim($o['notes'] . "\n[" . date('d/m H:i') . '] ' . input('note')), 'updated_at' => now()], 'id = :id', ['id' => $o['id']]);
        flash('success', 'Note added.');
    }
    redirect('/admin/orders/' . $ref);
}

function admin_products(): void
{
    require_admin();
    admin_view('products', ['title' => 'Products', 'products' => rows('SELECT p.*, c.name AS category_name FROM products p LEFT JOIN categories c ON c.id = p.category_id ORDER BY c.sort, p.name')]);
}

function admin_product_edit(?string $id = null): void
{
    require_admin();
    $p = $id ? row('SELECT * FROM products WHERE id = ?', [(int) $id]) : null;
    if ($id && !$p) not_found();
    $tiers = $p ? rows('SELECT * FROM price_tiers WHERE product_id = ? ORDER BY min_qty', [$p['id']]) : [];
    admin_view('product_edit', ['title' => $p ? 'Edit ' . $p['name'] : 'New product', 'p' => $p, 'tiers' => $tiers, 'cats' => categories()]);
}

function admin_product_save(): void
{
    require_admin();
    $id = (int) input('id');
    $f = [
        'category_id' => (int) input('category_id'),
        'sku' => strtoupper((string) input('sku')),
        'name' => (string) input('name'),
        'short' => (string) input('short'),
        'description' => (string) input('description'),
        'unit' => (string) input('unit') ?: 'each',
        'pack_note' => (string) input('pack_note'),
        'price_pence' => (int) round((float) input('price') * 100),
        'moq' => max(1, (int) input('moq')),
        'step' => max(1, (int) input('step')),
        'pallet_qty' => (int) input('pallet_qty'),
        'stock' => (int) input('stock'),
        'lead_time' => (string) input('lead_time'),
        'swatch' => (string) input('swatch'),
        'featured' => input('featured') === '1' ? 1 : 0,
        'active' => input('active') === '1' ? 1 : 0,
    ];
    $f['slug'] = slugify((string) (input('slug') ?: $f['name']));
    if ($f['sku'] === '' || $f['name'] === '' || $f['price_pence'] <= 0) {
        flash('error', 'SKU, name and a price above zero are required.');
        redirect($id ? "/admin/products/$id" : '/admin/products/new');
    }
    if (val('SELECT id FROM products WHERE (sku = ? OR slug = ?) AND id != ?', [$f['sku'], $f['slug'], $id])) {
        flash('error', 'Another product already uses that SKU or web address.');
        redirect($id ? "/admin/products/$id" : '/admin/products/new');
    }
    if (!empty($_FILES['image']['tmp_name']) && is_uploaded_file($_FILES['image']['tmp_name'])) {
        $info = @getimagesize($_FILES['image']['tmp_name']);
        $ext = [IMAGETYPE_JPEG => 'jpg', IMAGETYPE_PNG => 'png', IMAGETYPE_WEBP => 'webp'][$info[2] ?? 0] ?? null;
        if ($ext && $_FILES['image']['size'] < 5 * 1024 * 1024) {
            $name = $f['slug'] . '-' . substr(bin2hex(random_bytes(4)), 0, 6) . '.' . $ext;
            move_uploaded_file($_FILES['image']['tmp_name'], ROOT . '/uploads/' . $name);
            $f['image'] = '/uploads/' . $name;
        } else {
            flash('error', 'Images must be JPG, PNG or WebP under 5MB.');
        }
    }
    if (input('remove_image') === '1') {
        $f['image'] = '';
    }
    if ($id) {
        update('products', $f, 'id = :id', ['id' => $id]);
    } else {
        $id = insert('products', $f + ['created_at' => now()]);
    }
    q('DELETE FROM price_tiers WHERE product_id = ?', [$id]);
    foreach (preg_split('/\r?\n/', (string) input('tiers')) as $line) {
        $line = str_replace(['£', ','], ['', ''], $line);
        if (preg_match('/^\s*(\d+)\s*[+=:]?\s*([\d.]+)\s*$/', $line, $m) && (int) $m[1] > $f['moq']) {
            insert('price_tiers', ['product_id' => $id, 'min_qty' => (int) $m[1], 'price_pence' => (int) round((float) $m[2] * 100)]);
        }
    }
    $p = row('SELECT * FROM products WHERE id = ?', [$id]);
    portal_event('product.updated', ['sku' => $p['sku'], 'name' => $p['name'], 'price' => (int) $p['price_pence'], 'stock' => (int) $p['stock'], 'active' => (bool) $p['active']]);
    flash('success', 'Product saved.');
    redirect("/admin/products/$id");
}

function admin_categories(): void
{
    require_admin();
    admin_view('categories', ['title' => 'Categories', 'cats' => categories()]);
}

function admin_categories_post(): void
{
    require_admin();
    $id = (int) input('id');
    $f = ['name' => (string) input('name'), 'description' => (string) input('description'), 'swatch' => (string) input('swatch'), 'sort' => (int) input('sort')];
    if ($f['name'] === '') {
        flash('error', 'Enter a category name.');
        redirect('/admin/categories');
    }
    if ($id) {
        update('categories', $f, 'id = :id', ['id' => $id]);
    } else {
        insert('categories', $f + ['slug' => slugify($f['name'])]);
    }
    flash('success', 'Category saved.');
    redirect('/admin/categories');
}

function admin_customers(): void
{
    require_admin();
    $status = (string) input('status');
    admin_view('customers', [
        'title' => 'Trade customers', 'status' => $status,
        'customers' => $status ? rows('SELECT * FROM customers WHERE status = ? ORDER BY id DESC', [$status]) : rows('SELECT * FROM customers ORDER BY id DESC'),
    ]);
}

function admin_customer(string $id): void
{
    require_admin();
    $c = row('SELECT * FROM customers WHERE id = ?', [(int) $id]) ?? not_found();
    admin_view('customer', ['title' => $c['company_name'], 'c' => $c, 'orders' => rows('SELECT * FROM orders WHERE customer_id = ? ORDER BY id DESC', [$c['id']]), 'outstanding' => account_outstanding((int) $c['id'])]);
}

function customer_set_status(array $c, string $status): void
{
    update('customers', ['status' => $status], 'id = :id', ['id' => $c['id']]);
    if ($status === 'approved' && $c['status'] !== 'approved') {
        mail_send($c['email'], 'Your Rezlo trade account is approved', "Hi {$c['contact_name']},\n\nYour trade account for {$c['company_name']} is approved. You can now order online at trade prices:\n\n" . abs_url('/login') . "\n\nRezlo");
    }
    portal_event('customer.status_changed', ['id' => (int) $c['id'], 'email' => $c['email'], 'from' => $c['status'], 'to' => $status]);
}

function admin_customer_post(string $id): void
{
    require_admin();
    $c = row('SELECT * FROM customers WHERE id = ?', [(int) $id]) ?? not_found();
    if (in_array(input('status'), ['pending', 'approved', 'rejected', 'suspended'], true) && input('status') !== $c['status']) {
        customer_set_status($c, (string) input('status'));
    }
    update('customers', [
        'credit_terms' => input('credit_terms') === '1' ? 1 : 0,
        'credit_limit_pence' => (int) round((float) input('credit_limit') * 100),
    ], 'id = :id', ['id' => $c['id']]);
    flash('success', 'Customer updated.');
    redirect('/admin/customers/' . $c['id']);
}

function admin_enquiries(): void
{
    require_admin();
    admin_view('enquiries', ['title' => 'Enquiries and quote requests', 'items' => rows('SELECT * FROM enquiries ORDER BY id DESC LIMIT 300')]);
}

function admin_enquiry_post(string $id): void
{
    require_admin();
    if (in_array(input('status'), ['new', 'replied', 'quoted', 'won', 'lost', 'closed'], true)) {
        update('enquiries', ['status' => (string) input('status')], 'id = :id', ['id' => (int) $id]);
    }
    redirect('/admin/enquiries');
}

function admin_integrations(): void
{
    require_admin();
    admin_view('integrations', [
        'title' => 'Payments and DBS portal',
        'outbox' => rows('SELECT * FROM outbox ORDER BY id DESC LIMIT 40'),
        'pendingEvents' => (int) val('SELECT COUNT(*) FROM outbox WHERE sent_at IS NULL'),
        'log' => rows('SELECT * FROM payment_log ORDER BY id DESC LIMIT 20'),
    ]);
}

function admin_integrations_post(): void
{
    require_admin();
    if (input('action') === 'flush') {
        q('UPDATE outbox SET next_attempt_at = ? WHERE sent_at IS NULL', [now()]);
        $r = portal_flush(100);
        flash(isset($r['skipped']) ? 'error' : 'success', isset($r['skipped']) ? 'The DBS portal link is turned off in config.php.' : "Sent {$r['sent']} events, {$r['failed']} failed.");
    } elseif (input('action') === 'dp_ping') {
        $r = dp_ping();
        flash($r['ok'] ? 'success' : 'error', $r['ok'] ? 'DPayments accepted the API key.' : 'DPayments test failed: ' . $r['error']);
    } elseif (input('action') === 'ping') {
        portal_event('portal.ping', ['message' => 'Test event from Rezlo admin', 'site' => abs_url('/')]);
        $r = portal_flush(1);
        flash(($r['sent'] ?? 0) ? 'success' : 'error', ($r['sent'] ?? 0) ? 'Test event delivered to the DBS portal.' : 'Test event not delivered yet. Check the outbox below for the error.');
    }
    redirect('/admin/integrations');
}
