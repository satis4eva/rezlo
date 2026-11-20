<?php
declare(strict_types=1);

// Built-in PHP server: serve static files directly.
if (PHP_SAPI === 'cli-server') {
    $file = __DIR__ . parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
    if (is_file($file) && !preg_match('#/(app|data|views)/|config\.php#', $file)) {
        return false;
    }
}

require __DIR__ . '/app/bootstrap.php';
require __DIR__ . '/app/routes_public.php';
require __DIR__ . '/app/routes_account.php';
require __DIR__ . '/app/routes_checkout.php';
require __DIR__ . '/app/routes_admin.php';
require __DIR__ . '/app/api.php';

$method = $_SERVER['REQUEST_METHOD'];
$path = rtrim(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH) ?: '/', '/') ?: '/';

if ($method === 'POST' && !preg_match('#^/(webhooks|api)/#', $path)) {
    csrf_check();
}

$routes = [
    // Public
    'GET /'                         => 'page_home',
    'GET /shop'                     => 'page_shop',
    'GET /shop/([a-z0-9-]+)'        => 'page_shop',
    'GET /product/([a-z0-9-]+)'     => 'page_product',
    'GET /services'                 => 'page_services',
    'GET /trade'                    => 'page_trade',
    'GET /about'                    => 'page_about',
    'GET /contact'                  => 'page_contact',
    'POST /contact'                 => 'page_contact_post',
    'GET /quote'                    => 'page_quote',
    'POST /quote'                   => 'page_quote_post',
    'GET /(terms|services-terms|website-terms|privacy|cookies|delivery|returns)' => 'page_legal',
    'GET /sitemap.xml'              => 'page_sitemap',
    'GET /robots.txt'               => 'page_robots',

    // Cart and checkout
    'GET /cart'                     => 'cart_show',
    'POST /cart/add'                => 'cart_add',
    'POST /cart/update'             => 'cart_update',
    'GET /checkout'                 => 'checkout_show',
    'POST /checkout'                => 'checkout_place',
    'GET /checkout/return'          => 'checkout_return',
    'GET /order/([A-Z0-9-]+)/done'  => 'order_done',
    'GET /order/([A-Z0-9-]+)/proforma' => 'order_proforma',
    'POST /order/([A-Z0-9-]+)/pay' => 'order_pay',
    'GET /pay/simulate'             => 'pay_simulate',
    'POST /pay/simulate'            => 'pay_simulate_post',
    'POST /webhooks/dpayments'      => 'webhook_dpayments',

    // Trade accounts
    'GET /register'                 => 'account_register',
    'POST /register'                => 'account_register_post',
    'GET /login'                    => 'account_login',
    'POST /login'                   => 'account_login_post',
    'POST /logout'                  => 'account_logout',
    'GET /forgot-password'          => 'account_forgot',
    'POST /forgot-password'         => 'account_forgot_post',
    'GET /reset-password'           => 'account_reset',
    'POST /reset-password'          => 'account_reset_post',
    'GET /account'                  => 'account_home',
    'GET /account/orders/([A-Z0-9-]+)' => 'account_order',
    'POST /account/orders/([A-Z0-9-]+)/reorder' => 'account_reorder',
    'GET /account/details'          => 'account_details',
    'POST /account/details'         => 'account_details_post',

    // Admin
    'GET /admin'                    => 'admin_dashboard',
    'GET /admin/login'              => 'admin_login',
    'POST /admin/login'             => 'admin_login_post',
    'POST /admin/logout'            => 'admin_logout',
    'GET /admin/orders'             => 'admin_orders',
    'GET /admin/orders/([A-Z0-9-]+)' => 'admin_order',
    'POST /admin/orders/([A-Z0-9-]+)' => 'admin_order_post',
    'GET /admin/products'           => 'admin_products',
    'GET /admin/products/new'       => 'admin_product_edit',
    'GET /admin/products/(\d+)'     => 'admin_product_edit',
    'POST /admin/products/save'     => 'admin_product_save',
    'GET /admin/categories'         => 'admin_categories',
    'POST /admin/categories'        => 'admin_categories_post',
    'GET /admin/customers'          => 'admin_customers',
    'GET /admin/customers/(\d+)'    => 'admin_customer',
    'POST /admin/customers/(\d+)'   => 'admin_customer_post',
    'GET /admin/enquiries'          => 'admin_enquiries',
    'POST /admin/enquiries/(\d+)'   => 'admin_enquiry_post',
    'GET /admin/integrations'       => 'admin_integrations',
    'POST /admin/integrations'      => 'admin_integrations_post',
    'GET /admin/password'           => 'admin_password',
    'POST /admin/password'          => 'admin_password_post',

    // DBS master portal API
    'GET /api/v1/health'            => 'api_health',
    'GET /api/v1/summary'           => 'api_summary',
    'GET /api/v1/orders'            => 'api_orders',
    'GET /api/v1/orders/([A-Z0-9-]+)' => 'api_order',
    'POST /api/v1/orders/([A-Z0-9-]+)/status' => 'api_order_status',
    'GET /api/v1/customers'         => 'api_customers',
    'POST /api/v1/customers/(\d+)/status' => 'api_customer_status',
    'GET /api/v1/products'          => 'api_products',
    'POST /api/v1/products/([A-Za-z0-9-]+)' => 'api_product_update',
    'GET /api/v1/enquiries'         => 'api_enquiries',

    'GET /cron'                     => 'run_cron',
];

foreach ($routes as $pattern => $handler) {
    [$m, $p] = explode(' ', $pattern, 2);
    if ($m !== $method) {
        continue;
    }
    if (preg_match('#^' . $p . '$#', $path, $matches)) {
        array_shift($matches);
        $handler(...$matches);
        exit;
    }
}

if (str_starts_with($path, '/api/')) {
    json_response(['error' => 'not_found'], 404);
}
not_found();

function run_cron(): void
{
    if (!hash_equals((string) config('app.cron_key'), (string) input('key'))) {
        json_response(['error' => 'forbidden'], 403);
    }
    json_response(['portal' => portal_flush(50), 'at' => now()]);
}
