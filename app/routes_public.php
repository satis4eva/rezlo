<?php
declare(strict_types=1);

function categories(): array
{
    static $c = null;
    return $c ??= rows('SELECT * FROM categories ORDER BY sort, name');
}

function services_list(): array
{
    return [
        'flooring' => ['Floor covering installation', 'LVT, engineered wood, laminate, carpet tiles and safety vinyl, including subfloor preparation and levelling.', 'oak:#b8895a'],
        'walls'    => ['Wall coverings and tiling', 'Commercial vinyl wallcoverings, lining, and wall and floor tiling for kitchens, bathrooms and washrooms.', 'tile:#cfcac1'],
        'painting' => ['Painting and decorating', 'Interior and exterior decorating for new builds, voids, offices and planned maintenance.', 'paint:#e9e6dc'],
        'finishing'=> ['Building completion and finishing', 'Snagging, making good, second-fix finishing and handover preparation.', 'bag:#8f938c'],
        'support'  => ['Procurement and project support', 'Quantity take-offs, sourcing, phased deliveries to site and consolidated invoicing for contractors.', 'roll:#3d5a73'],
    ];
}

function page_home(): void
{
    $featured = rows('SELECT p.*, c.name AS category_name FROM products p LEFT JOIN categories c ON c.id = p.category_id WHERE p.active = 1 AND p.featured = 1 ORDER BY p.id LIMIT 8');
    $counts = [];
    foreach (rows('SELECT category_id, COUNT(*) n FROM products WHERE active = 1 GROUP BY category_id') as $r) {
        $counts[$r['category_id']] = (int) $r['n'];
    }
    view('home', [
        'title' => 'Trade flooring, tiles, wall coverings and paint in bulk',
        'meta' => 'Rezlo supplies flooring, tiles, wall coverings, paint and adhesives at trade prices, with bulk price breaks and pallet delivery across mainland UK.',
        'featured' => $featured, 'counts' => $counts,
    ]);
}

function page_shop(?string $catSlug = null): void
{
    $cat = null;
    $params = [];
    $where = 'p.active = 1';
    if ($catSlug) {
        $cat = row('SELECT * FROM categories WHERE slug = ?', [$catSlug]);
        if (!$cat) {
            not_found();
        }
        $where .= ' AND p.category_id = ?';
        $params[] = $cat['id'];
    }
    $search = (string) input('q');
    if ($search !== '') {
        $where .= ' AND (p.name LIKE ? OR p.sku LIKE ? OR p.short LIKE ?)';
        array_push($params, "%$search%", "%$search%", "%$search%");
    }
    $sort = (string) input('sort', 'featured');
    $order = match ($sort) {
        'price' => 'p.price_pence ASC',
        'price_desc' => 'p.price_pence DESC',
        'name' => 'p.name ASC',
        default => 'p.featured DESC, c.sort, p.id',
    };
    $products = rows("SELECT p.*, c.name AS category_name FROM products p LEFT JOIN categories c ON c.id = p.category_id WHERE $where ORDER BY $order", $params);
    view('shop', [
        'title' => $cat ? $cat['name'] . ' — trade prices' : ($search ? 'Search: ' . $search : 'Shop the trade catalogue'),
        'meta' => $cat['description'] ?? 'Trade catalogue of flooring, tiles, wall coverings, paint, adhesives and sundries with bulk price breaks.',
        'cat' => $cat, 'products' => $products, 'search' => $search, 'sort' => $sort,
    ]);
}

function page_product(string $slug): void
{
    $p = product_by_slug($slug);
    if (!$p) {
        not_found();
    }
    $related = rows('SELECT * FROM products WHERE active = 1 AND category_id = ? AND id != ? ORDER BY featured DESC, id LIMIT 4', [$p['category_id'], $p['id']]);
    view('product', ['title' => $p['name'], 'meta' => $p['short'], 'p' => $p, 'tiers' => product_tiers($p), 'related' => $related]);
}

function page_services(): void
{
    view('services', ['title' => 'Installation, decorating and project support', 'meta' => 'Floor covering installation, wall coverings and tiling, painting and decorating, building finishing and procurement support across Hampshire and the South of England.']);
}

function page_trade(): void
{
    view('trade', ['title' => 'Open a trade account', 'meta' => 'How Rezlo trade accounts, bulk pricing, credit terms and pallet delivery work.']);
}

function page_about(): void
{
    view('about', ['title' => 'About Rezlo', 'meta' => 'Rezlo Ltd is a Basingstoke-based supplier of building finishing materials and services for the trade.']);
}

function page_contact(): void
{
    view('contact', ['title' => 'Contact us', 'meta' => 'Call, email or message Rezlo about trade orders, quotes and accounts.']);
}

function enquiry_save(string $kind): ?int
{
    $data = [
        'name' => (string) input('name'), 'company' => (string) input('company'), 'email' => strtolower((string) input('email')),
        'phone' => (string) input('phone'), 'postcode' => strtoupper((string) input('postcode')),
        'service' => (string) input('service'), 'message' => (string) input('message'),
    ];
    if (input('website') !== '') { // honeypot
        return null;
    }
    $errors = [];
    if ($data['name'] === '') $errors[] = 'Enter your name.';
    if (!valid_email($data['email'])) $errors[] = 'Enter a valid email address.';
    if ($data['message'] === '' && $kind !== 'quote') $errors[] = 'Tell us what you need.';
    if ($errors) {
        keep_old();
        foreach ($errors as $er) flash('error', $er);
        return null;
    }
    $items = null;
    if ($kind === 'quote' && input('include_cart') === '1') {
        $items = json_encode(array_map(fn($l) => ['sku' => $l['product']['sku'], 'name' => $l['product']['name'], 'qty' => $l['qty'], 'unit' => $l['product']['unit'], 'list_unit_price' => $l['unit']], cart_lines()));
    }
    $ref = make_ref($kind === 'quote' ? 'RQ' : 'EN');
    $id = insert('enquiries', $data + [
        'ref' => $ref, 'kind' => $kind, 'customer_id' => current_customer()['id'] ?? null,
        'items_json' => $items, 'status' => 'new', 'created_at' => now(),
    ]);
    $body = "Ref: $ref\nType: $kind\nName: {$data['name']}\nCompany: {$data['company']}\nEmail: {$data['email']}\nPhone: {$data['phone']}\nPostcode: {$data['postcode']}\nService: {$data['service']}\n\n{$data['message']}";
    if ($items) {
        foreach (json_decode($items, true) as $i) {
            $body .= "\n- {$i['sku']} {$i['name']} × {$i['qty']} {$i['unit']}";
        }
    }
    mail_admin(($kind === 'quote' ? 'Quote request ' : 'Enquiry ') . $ref . ' from ' . $data['name'], $body, $data['email']);
    mail_send($data['email'], 'We have your ' . ($kind === 'quote' ? 'quote request' : 'message') . " ($ref)", "Hi {$data['name']},\n\nThanks for getting in touch. We'll reply within one working day.\n\nYour reference is $ref.\n\nRezlo");
    portal_event('enquiry.created', ['ref' => $ref, 'kind' => $kind, 'name' => $data['name'], 'company' => $data['company'], 'email' => $data['email'], 'service' => $data['service'], 'items' => $items ? json_decode($items, true) : []]);
    return $id;
}

function page_contact_post(): void
{
    if (enquiry_save('contact')) {
        flash('success', "Message sent. We'll reply within one working day.");
    }
    redirect('/contact');
}

function page_quote(): void
{
    view('quote', ['title' => 'Request a quote', 'meta' => 'Get a quote for pallet quantities, project supply or installation work.', 'lines' => cart_lines(), 'service' => (string) input('service')]);
}

function page_quote_post(): void
{
    if (enquiry_save('quote')) {
        if (input('include_cart') === '1') {
            cart_clear();
        }
        flash('success', "Quote request sent. We'll come back to you within one working day.");
        redirect('/quote');
    }
    redirect('/quote');
}

function page_legal(string $doc): void
{
    $titles = [
        'terms' => 'Trade terms and conditions of sale',
        'services-terms' => 'Terms for installation and support services',
        'website-terms' => 'Website terms of use',
        'privacy' => 'Privacy policy',
        'cookies' => 'Cookie policy',
        'delivery' => 'Delivery policy',
        'returns' => 'Returns and refunds policy',
    ];
    view('legal/' . $doc, ['title' => $titles[$doc], 'doc' => $doc, 'legalTitles' => $titles, 'updated' => '20 November 2026'], 'legal_layout');
}

function page_sitemap(): void
{
    header('Content-Type: application/xml');
    $urls = ['/', '/shop', '/services', '/trade', '/about', '/contact', '/quote', '/terms', '/services-terms', '/website-terms', '/privacy', '/cookies', '/delivery', '/returns'];
    foreach (categories() as $c) $urls[] = '/shop/' . $c['slug'];
    foreach (rows('SELECT slug FROM products WHERE active = 1') as $p) $urls[] = '/product/' . $p['slug'];
    echo '<?xml version="1.0" encoding="UTF-8"?><urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">';
    foreach ($urls as $u) echo '<url><loc>' . e(abs_url($u)) . '</loc></url>';
    echo '</urlset>';
}

function page_robots(): void
{
    header('Content-Type: text/plain');
    echo "User-agent: *\nDisallow: /admin\nDisallow: /account\nDisallow: /checkout\nDisallow: /cart\nDisallow: /api/\nSitemap: " . abs_url('/sitemap.xml') . "\n";
}
