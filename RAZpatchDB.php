<?php
require_once __DIR__ . '/RAZconfig.php';

$pdo = RAZgetConnection();
if (!$pdo) {
    die("Koneksi database gagal.");
}

$dbType = RAZgetDbType();

// SQL untuk SQLite vs MySQL
$autoInc = ($dbType === 'sqlite') ? 'INTEGER PRIMARY KEY AUTOINCREMENT' : 'INT AUTO_INCREMENT PRIMARY KEY';
$timestamp = ($dbType === 'sqlite') ? "TEXT DEFAULT (datetime('now','localtime'))" : "DATETIME DEFAULT CURRENT_TIMESTAMP";
$decimalType = 'DECIMAL(15,2)';
$textType = 'TEXT';

echo "Memulai patch database...\n";

try {
    // Tabel Capital Flows
    $pdo->exec("CREATE TABLE IF NOT EXISTS capital_flows (
        id {$autoInc},
        store_id INT NOT NULL,
        type VARCHAR(10) NOT NULL,
        source_name VARCHAR(100) NOT NULL,
        amount {$decimalType} NOT NULL DEFAULT 0,
        notes {$textType},
        created_by INT NOT NULL,
        created_at {$timestamp}
    )");
    echo "Tabel capital_flows berhasil dibuat/dicek.\n";

    // Tabel Spoilages
    $pdo->exec("CREATE TABLE IF NOT EXISTS spoilages (
        id {$autoInc},
        store_id INT NOT NULL,
        item_id INT NOT NULL,
        qty INT NOT NULL DEFAULT 1,
        hpp_value {$decimalType} NOT NULL DEFAULT 0,
        total_loss {$decimalType} NOT NULL DEFAULT 0,
        notes {$textType},
        created_by INT NOT NULL,
        created_at {$timestamp}
    )");
    echo "Tabel spoilages berhasil dibuat/dicek.\n";

    // Cek kolom deduct_from_share_id di tabel cash_flows
    $columnExists = false;
    if ($dbType === 'sqlite') {
        $stmt = $pdo->query("PRAGMA table_info(cash_flows)");
        $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
        foreach ($columns as $col) {
            if ($col['name'] === 'deduct_from_share_id') {
                $columnExists = true;
                break;
            }
        }
    } else {
        $stmt = $pdo->prepare("SHOW COLUMNS FROM cash_flows LIKE 'deduct_from_share_id'");
        $stmt->execute();
        if ($stmt->fetch()) {
            $columnExists = true;
        }
    }

    if (!$columnExists) {
        $pdo->exec("ALTER TABLE cash_flows ADD COLUMN deduct_from_share_id INT DEFAULT NULL");
        echo "Kolom deduct_from_share_id berhasil ditambahkan ke cash_flows.\n";
    } else {
        echo "Kolom deduct_from_share_id sudah ada di cash_flows.\n";
    }

    echo "Patch database berhasil diselesaikan.\n";
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
