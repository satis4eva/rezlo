<?php
/**
 * Rezlo Ltd — site configuration.
 * Copy this file to config.php on the server and fill in the values.
 * config.php is git-ignored so keys never end up in GitHub.
 */
return [
    'app' => [
        'env'      => 'production',              // 'development' shows errors
        'url'      => 'https://rezloltd.co.uk',  // no trailing slash
        'timezone' => 'Europe/London',
        'secret'   => 'CHANGE-ME-to-a-long-random-string', // used for CSRF/session hardening
        // Initial admin (created on first run if no admin exists). Change the password after first login.
        'admin_email'    => 'info@rezloltd.co.uk',
        'admin_password' => 'CHANGE-ME',
        'cron_key'       => 'CHANGE-ME-cron-key',
    ],

    'company' => [
        'name'           => 'Rezlo Ltd',
        'trading_name'   => 'Rezlo',
        'address'        => ['Flat 8, 20b Winchester Street', 'Basingstoke', 'Hampshire', 'RG21 7DZ', 'England'],
        'phone'          => '07947 311540',
        'phone_e164'     => '+447947311540',
        'email'          => 'info@rezloltd.co.uk',
        'company_number' => '',   // Companies House number — fill in, it appears in the footer and legal pages
        'vat_number'     => '',   // leave blank if not VAT registered
        'ico_number'     => '',   // ICO data protection registration number, if you have one
        'jurisdiction'   => 'England and Wales',
        'sic'            => ['43330 Floor and wall covering', '43341 Painting', '43390 Other building completion and finishing', '82990 Other business support service activities n.e.c.'],
    ],

    'db' => [
        // 'sqlite' needs nothing set up. For MySQL on Hostinger, set driver to 'mysql' and fill in the rest.
        'driver'   => 'sqlite',
        'sqlite'   => __DIR__ . '/data/rezlo.sqlite',
        'host'     => 'localhost',
        'name'     => '',
        'user'     => '',
        'pass'     => '',
    ],

    'shop' => [
        'currency'                 => 'GBP',
        'vat_rate'                 => 0.20,
        'prices_include_vat'       => false,  // trade prices shown ex VAT
        'require_approval'         => true,   // trade accounts must be approved before checkout
        'show_prices_to_guests'    => true,
        'min_order_pence'          => 10000,  // £100 ex VAT minimum order
        'free_delivery_pence'      => 50000,  // free mainland delivery from £500 ex VAT
        'delivery_pence'           => 4500,   // otherwise £45 ex VAT per order
        // Postcode areas quoted separately (Highlands, islands, NI, etc.)
        'poa_postcodes'            => ['BT','HS','IV','KW','PA20','PA41','PA42','PA43','PA44','PA45','PA46','PA47','PA48','PA49','PA60','PA61','PA62','PA63','PA64','PA65','PA66','PA67','PA68','PA69','PA70','PA71','PA72','PA73','PA74','PA75','PA76','PA77','PA78','PH17','PH18','PH19','PH20','PH21','PH22','PH23','PH24','PH25','PH26','PH30','PH31','PH32','PH33','PH34','PH35','PH36','PH37','PH38','PH39','PH40','PH41','PH42','PH43','PH44','PH49','PH50','KA27','KA28','ZE','IM','GY','JE','TR21','TR22','TR23','TR24','TR25'],
        'bank_transfer' => [
            'enabled'        => true,
            'account_name'   => 'Rezlo Ltd',
            'sort_code'      => '',
            'account_number' => '',
            'bank'           => '',
        ],
    ],

    'dpayments' => [
        // 'simulate' = built-in test checkout (no real money). 'sandbox' / 'live' = real DPayments API.
        'mode'            => 'simulate',
        'api_base_sandbox'=> 'https://sandbox.api.dpayments.co.uk',
        'api_base_live'   => 'https://api.dpayments.co.uk',
        'merchant_id'     => '',
        'api_key'         => '',
        'webhook_secret'  => '',
        // Match these to the DPayments API reference if they differ:
        'create_path'     => '/v1/checkout/sessions',
        'retrieve_path'   => '/v1/checkout/sessions/{id}',
        'ping_path'       => '/v1/ping',
        'redirect_field'  => 'url',              // field in the create response holding the hosted payment page URL
        'id_field'        => 'id',
        'status_field'    => 'status',
        'paid_statuses'   => ['paid', 'succeeded', 'captured', 'approved', 'completed'],
        'failed_statuses' => ['failed', 'declined', 'cancelled', 'expired'],
        'signature_header'=> 'X-DPayments-Signature', // t=<unix>,v1=<hex HMAC-SHA256 of "<t>.<raw body>"> with webhook_secret
    ],

    'portal' => [
        // DBS Group master portal
        'enabled'        => false,
        'base_url'       => 'https://platform.dbsgroup.io',
        'brand'          => 'rezlo',
        'events_path'    => '/api/brands/{brand}/events', // where Rezlo pushes events
        'api_key'        => '',   // bearer token Rezlo uses when calling the portal
        'signing_secret' => '',   // HMAC secret for X-DBS-Signature on outgoing events
        'inbound_key'    => '',   // bearer token the portal uses when calling https://rezloltd.co.uk/api/v1/...
        'dbs_public_key' => '',   // DBS licence public key (base64) — lets master.dbsgroup.io read /api/platform/status
        'powered_by'     => true, // "Powered by dbsgroup.io" in the footer
    ],

    'mail' => [
        'from'        => 'info@rezloltd.co.uk',
        'from_name'   => 'Rezlo',
        'orders_to'   => 'info@rezloltd.co.uk', // who gets new order / enquiry notifications
        'transport'   => 'mail',                // 'mail' (PHP mail on Hostinger) or 'log' (writes to data/mail.log)
    ],
];
