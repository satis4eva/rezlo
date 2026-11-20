<?php
/**
 * Adds Rezlo as a client in the DBS master portal database (command line only, run once on the server).
 *   php tools/register-with-master.php /path/to/master.sqlite
 */
if (PHP_SAPI !== 'cli') { http_response_code(404); exit; }
$file = $argv[1] ?? '';
if (!is_file($file)) { fwrite(STDERR, "master.sqlite not found: $file\n"); exit(1); }
$db = new PDO('sqlite:' . $file);
$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
$exists = $db->query("SELECT id FROM clients WHERE domains LIKE '%rezloltd.co.uk%'")->fetchColumn();
if ($exists) { echo "already registered as client $exists\n"; exit(0); }
$checklist = ['domain' => true, 'site' => true, 'deploy' => true, 'cron' => true];
$st = $db->prepare('INSERT INTO clients (company, brand, contact_name, email, phone, domains, plan, status, setup_fee, monthly, billing_cycle, started_at, renews_at, checklist, notes)
    VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)');
$st->execute(['Rezlo Ltd', 'Rezlo', '', 'info@rezloltd.co.uk', '07947 311540', 'rezloltd.co.uk, www.rezloltd.co.uk', 'growth', 'onboarding', 0, 0, 'monthly',
    date('Y-m-d'), date('Y-m-d', strtotime('+1 month')), json_encode($checklist),
    "Custom wholesale (trade/B2B) build, not DBS Commerce: repo satis4eva/rezlo. No licence key needed. Store health via /api/platform/status. Payments: DPayments (keys pending), bank transfer, trade credit accounts. Registered office: Flat 8, 20b Winchester Street, Basingstoke RG21 7DZ. SIC 43330, 43341, 43390, 82990. Set plan and fees."]);
$id = (int) $db->lastInsertId();
$db->prepare('INSERT INTO notes (client_id, author, body) VALUES (?,?,?)')->execute([$id, 'Claude', 'Client created (Onboarding). rezloltd.co.uk live on Hostinger, deployed from GitHub; MySQL database; health check endpoint added.']);
echo "registered as client $id\n";
