<?php
declare(strict_types=1);

function db(): PDO
{
    static $pdo = null;
    if ($pdo) {
        return $pdo;
    }
    $driver = config('db.driver', 'sqlite');
    if ($driver === 'mysql') {
        $dsn = sprintf('mysql:host=%s;dbname=%s;charset=utf8mb4', config('db.host'), config('db.name'));
        $pdo = new PDO($dsn, config('db.user'), config('db.pass'));
    } else {
        $file = config('db.sqlite', ROOT . '/data/rezlo.sqlite');
        if (!is_dir(dirname($file))) {
            mkdir(dirname($file), 0750, true);
        }
        $pdo = new PDO('sqlite:' . $file);
        $pdo->exec('PRAGMA foreign_keys = ON; PRAGMA journal_mode = WAL;');
    }
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
    return $pdo;
}

function q(string $sql, array $params = []): PDOStatement
{
    $st = db()->prepare($sql);
    $st->execute($params);
    return $st;
}

function row(string $sql, array $params = []): ?array
{
    $r = q($sql, $params)->fetch();
    return $r === false ? null : $r;
}

function rows(string $sql, array $params = []): array
{
    return q($sql, $params)->fetchAll();
}

function val(string $sql, array $params = [])
{
    $v = q($sql, $params)->fetchColumn();
    return $v === false ? null : $v;
}

function insert(string $table, array $data): int
{
    $cols = array_keys($data);
    $sql = sprintf(
        'INSERT INTO %s (%s) VALUES (%s)',
        $table,
        implode(',', $cols),
        implode(',', array_map(fn($c) => ':' . $c, $cols))
    );
    q($sql, $data);
    return (int) db()->lastInsertId();
}

function update(string $table, array $data, string $where, array $whereParams = []): void
{
    $set = implode(',', array_map(fn($c) => "$c = :set_$c", array_keys($data)));
    $params = [];
    foreach ($data as $k => $v) {
        $params["set_$k"] = $v;
    }
    q("UPDATE $table SET $set WHERE $where", array_merge($params, $whereParams));
}

function db_migrate(): void
{
    $mysql = config('db.driver') === 'mysql';
    $id = $mysql ? 'INT AUTO_INCREMENT PRIMARY KEY' : 'INTEGER PRIMARY KEY AUTOINCREMENT';
    $text = $mysql ? 'MEDIUMTEXT' : 'TEXT';
    $engine = $mysql ? ' ENGINE=InnoDB DEFAULT CHARSET=utf8mb4' : '';

    $tables = [
        "categories (
            id $id,
            slug VARCHAR(120) NOT NULL UNIQUE,
            name VARCHAR(160) NOT NULL,
            description $text,
            swatch VARCHAR(60) DEFAULT '',
            sort INT DEFAULT 0
        )",
        "products (
            id $id,
            category_id INT,
            sku VARCHAR(60) NOT NULL UNIQUE,
            slug VARCHAR(160) NOT NULL UNIQUE,
            name VARCHAR(200) NOT NULL,
            short VARCHAR(255) DEFAULT '',
            description $text,
            unit VARCHAR(60) DEFAULT 'each',
            pack_note VARCHAR(160) DEFAULT '',
            price_pence INT NOT NULL DEFAULT 0,
            moq INT NOT NULL DEFAULT 1,
            step INT NOT NULL DEFAULT 1,
            pallet_qty INT DEFAULT 0,
            stock INT DEFAULT 0,
            lead_time VARCHAR(80) DEFAULT '2–4 working days',
            swatch VARCHAR(60) DEFAULT '',
            image VARCHAR(255) DEFAULT '',
            featured INT DEFAULT 0,
            active INT DEFAULT 1,
            created_at VARCHAR(25)
        )",
        "price_tiers (
            id $id,
            product_id INT NOT NULL,
            min_qty INT NOT NULL,
            price_pence INT NOT NULL
        )",
        "customers (
            id $id,
            email VARCHAR(190) NOT NULL UNIQUE,
            password_hash VARCHAR(255) NOT NULL,
            contact_name VARCHAR(160),
            company_name VARCHAR(200),
            company_number VARCHAR(40),
            vat_number VARCHAR(40),
            business_type VARCHAR(80),
            phone VARCHAR(40),
            address1 VARCHAR(200), address2 VARCHAR(200), city VARCHAR(120), postcode VARCHAR(20),
            status VARCHAR(20) DEFAULT 'pending',
            credit_terms INT DEFAULT 0,
            credit_limit_pence INT DEFAULT 0,
            marketing INT DEFAULT 0,
            reset_token VARCHAR(100), reset_expires VARCHAR(25),
            created_at VARCHAR(25)
        )",
        "orders (
            id $id,
            ref VARCHAR(30) NOT NULL UNIQUE,
            customer_id INT,
            status VARCHAR(30) NOT NULL,
            payment_method VARCHAR(30),
            payment_status VARCHAR(30) DEFAULT 'unpaid',
            gateway_ref VARCHAR(120),
            subtotal_pence INT, delivery_pence INT, vat_pence INT, total_pence INT,
            po_number VARCHAR(80), notes $text,
            ship_name VARCHAR(160), ship_company VARCHAR(200), ship_phone VARCHAR(40),
            ship_address1 VARCHAR(200), ship_address2 VARCHAR(200), ship_city VARCHAR(120), ship_postcode VARCHAR(20),
            delivery_pref VARCHAR(80),
            email VARCHAR(190),
            created_at VARCHAR(25), paid_at VARCHAR(25), updated_at VARCHAR(25)
        )",
        "order_items (
            id $id,
            order_id INT NOT NULL,
            product_id INT,
            sku VARCHAR(60), name VARCHAR(200), unit VARCHAR(60),
            qty INT, unit_price_pence INT, line_pence INT
        )",
        "enquiries (
            id $id,
            ref VARCHAR(30),
            kind VARCHAR(20) NOT NULL,
            customer_id INT,
            name VARCHAR(160), company VARCHAR(200), email VARCHAR(190), phone VARCHAR(40), postcode VARCHAR(20),
            service VARCHAR(120), message $text, items_json $text,
            status VARCHAR(20) DEFAULT 'new',
            created_at VARCHAR(25)
        )",
        "admins (
            id $id,
            email VARCHAR(190) NOT NULL UNIQUE,
            password_hash VARCHAR(255) NOT NULL,
            name VARCHAR(120),
            created_at VARCHAR(25)
        )",
        "outbox (
            id $id,
            event VARCHAR(60) NOT NULL,
            payload $text,
            attempts INT DEFAULT 0,
            last_error VARCHAR(500),
            next_attempt_at VARCHAR(25),
            sent_at VARCHAR(25),
            created_at VARCHAR(25)
        )",
        "payment_log (
            id $id,
            order_id INT,
            source VARCHAR(30),
            event VARCHAR(60),
            payload $text,
            created_at VARCHAR(25)
        )",
    ];

    $existing = val($mysql
        ? "SELECT COUNT(*) FROM information_schema.tables WHERE table_schema = DATABASE() AND table_name = 'outbox'"
        : "SELECT COUNT(*) FROM sqlite_master WHERE type='table' AND name='outbox'");
    if ((int) $existing > 0) {
        return;
    }
    foreach ($tables as $t) {
        db()->exec("CREATE TABLE IF NOT EXISTS $t$engine");
    }
    db_seed();
}

function now(): string
{
    return date('Y-m-d H:i:s');
}
