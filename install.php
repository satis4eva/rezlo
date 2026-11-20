<?php
/**
 * One-time server install (command line only).
 *   php install.php <db_name_and_user> <db_password> <admin_password>
 * Creates config.php from config.sample.php for production on Hostinger,
 * with MySQL and freshly generated secrets. Does nothing if config.php exists.
 */
if (PHP_SAPI !== 'cli') {
    http_response_code(403);
    exit;
}
$target = __DIR__ . '/config.php';
if (is_file($target)) {
    fwrite(STDERR, "config.php already exists, nothing to do\n");
    exit(0);
}
[$_, $db, $dbPass, $adminPass] = array_pad($argv, 4, '');
if ($db === '' || $dbPass === '' || strlen($adminPass) < 10) {
    fwrite(STDERR, "usage: php install.php <db> <db_password> <admin_password(10+)>\n");
    exit(1);
}
$q = fn(string $v) => var_export($v, true);
$c = file_get_contents(__DIR__ . '/config.sample.php');
$map = [
    "'secret'   => 'CHANGE-ME-to-a-long-random-string'" => "'secret'   => " . $q(bin2hex(random_bytes(32))),
    "'admin_password' => 'CHANGE-ME'" => "'admin_password' => " . $q($adminPass),
    "'cron_key'       => 'CHANGE-ME-cron-key'" => "'cron_key'       => " . $q(bin2hex(random_bytes(16))),
    "'driver'   => 'sqlite'" => "'driver'   => 'mysql'",
    "'name'     => '',"      => "'name'     => " . $q($db) . ',',
    "'user'     => '',"      => "'user'     => " . $q($db) . ',',
    "'pass'     => '',"      => "'pass'     => " . $q($dbPass) . ',',
    "'signing_secret' => '',"=> "'signing_secret' => " . $q(bin2hex(random_bytes(32))) . ',',
    "'inbound_key'    => '',"=> "'inbound_key'    => " . $q('rzin_' . bin2hex(random_bytes(24))) . ',',
];
foreach ($map as $from => $to) {
    if (strpos($c, $from) === false) {
        fwrite(STDERR, "sample config changed, could not find: $from\n");
        exit(1);
    }
    $c = str_replace($from, $to, $c);
}
file_put_contents($target, $c);
chmod($target, 0600);

// Create the database tables and seed the catalogue now.
require __DIR__ . '/app/bootstrap.php';
echo "config.php created; products: " . val('SELECT COUNT(*) FROM products') . "\n";
