<?php
/**
 * Migrasi database: Tambah tabel profit_shares dan profit_share_reports
 * Jalankan sekali untuk database yang sudah ada
 */
require_once __DIR__ . '/RAZconfig.php';
$pdo = RAZgetConnection();
if (!$pdo) { echo "Koneksi database gagal\n"; exit(1); }

try {
    $dbType = RAZgetDbType();
    $autoInc = ($dbType === 'sqlite') ? 'INTEGER PRIMARY KEY AUTOINCREMENT' : 'INT AUTO_INCREMENT PRIMARY KEY';
    $timestamp = ($dbType === 'sqlite') ? "TEXT DEFAULT (datetime('now','localtime'))" : "DATETIME DEFAULT CURRENT_TIMESTAMP";
    $decimalType = 'DECIMAL(15,2)';
    $textType = 'TEXT';

    $pdo->exec("CREATE TABLE IF NOT EXISTS profit_shares (
        id {$autoInc},
        store_id INT NOT NULL,
        name VARCHAR(100) NOT NULL,
        role_label VARCHAR(50) DEFAULT 'custom',
        percentage {$decimalType} NOT NULL DEFAULT 0,
        is_active TINYINT(1) DEFAULT 1,
        sort_order INT DEFAULT 0,
        created_at {$timestamp}
    )");

    $pdo->exec("CREATE TABLE IF NOT EXISTS profit_share_reports (
        id {$autoInc},
        store_id INT NOT NULL,
        period_from TEXT NOT NULL,
        period_to TEXT NOT NULL,
        net_profit {$decimalType} DEFAULT 0,
        distribution_json {$textType},
        notes {$textType},
        created_by INT,
        created_at {$timestamp}
    )");

    echo "Migrasi berhasil!\n";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
