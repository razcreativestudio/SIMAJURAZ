<?php
/**
 * ============================================================
 * RAZmigrate_advanced_finance.php — Skrip Migrasi Struktur Database
 * ============================================================
 * Modul: Manajemen Modal, Barang Rusak, Atribusi Pengeluaran
 * ============================================================
 */
require_once __DIR__ . '/RAZconfig.php';

echo "<h2>Mulai Migrasi Database SIMAJURAZ (Advanced Finance)</h2>";

try {
    $pdo = RAZgetConnection();
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    echo "Koneksi SQLite Berhasil.<br>";

    // 1. Buat Tabel capital_flows
    $sqlCapital = "CREATE TABLE IF NOT EXISTS capital_flows (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        store_id INTEGER NOT NULL,
        type VARCHAR(20) NOT NULL DEFAULT 'in', -- 'in' (tambah modal), 'out' (tarik modal)
        source_name VARCHAR(100) NOT NULL,
        amount DECIMAL(15,2) NOT NULL DEFAULT 0,
        notes TEXT,
        created_by INTEGER NOT NULL,
        created_at DATETIME DEFAULT (datetime('now','localtime')),
        FOREIGN KEY (store_id) REFERENCES stores(id) ON DELETE CASCADE
    );";
    $pdo->exec($sqlCapital);
    echo "Tabel 'capital_flows' berhasil dibuat/dicek.<br>";

    // 2. Buat Tabel spoilages (Barang Rusak/Basi)
    $sqlSpoilages = "CREATE TABLE IF NOT EXISTS spoilages (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        store_id INTEGER NOT NULL,
        item_id INTEGER NOT NULL,
        qty INTEGER NOT NULL DEFAULT 1,
        hpp_value DECIMAL(15,2) NOT NULL DEFAULT 0,
        total_loss DECIMAL(15,2) NOT NULL DEFAULT 0,
        notes TEXT,
        created_by INTEGER NOT NULL,
        created_at DATETIME DEFAULT (datetime('now','localtime')),
        FOREIGN KEY (store_id) REFERENCES stores(id) ON DELETE CASCADE,
        FOREIGN KEY (item_id) REFERENCES items(id) ON DELETE CASCADE
    );";
    $pdo->exec($sqlSpoilages);
    echo "Tabel 'spoilages' berhasil dibuat/dicek.<br>";

    // 3. Tambah kolom deduct_from_share_id di cash_flows
    $stmt = $pdo->query("PRAGMA table_info(cash_flows)");
    $columns = $stmt->fetchAll(PDO::FETCH_COLUMN, 1);
    
    if (!in_array('deduct_from_share_id', $columns)) {
        $pdo->exec("ALTER TABLE cash_flows ADD COLUMN deduct_from_share_id INTEGER DEFAULT NULL");
        echo "Kolom 'deduct_from_share_id' ditambahkan ke tabel cash_flows.<br>";
    } else {
        echo "Kolom 'deduct_from_share_id' sudah ada di cash_flows.<br>";
    }

    // 4. Tambah kolom snapshot JSON di profit_share_reports
    $stmt = $pdo->query("PRAGMA table_info(profit_share_reports)");
    $columns = $stmt->fetchAll(PDO::FETCH_COLUMN, 1);
    
    if (!in_array('capital_json', $columns)) {
        $pdo->exec("ALTER TABLE profit_share_reports ADD COLUMN capital_json TEXT");
        echo "Kolom 'capital_json' ditambahkan ke tabel profit_share_reports.<br>";
    }
    if (!in_array('spoilages_json', $columns)) {
        $pdo->exec("ALTER TABLE profit_share_reports ADD COLUMN spoilages_json TEXT");
        echo "Kolom 'spoilages_json' ditambahkan ke tabel profit_share_reports.<br>";
    }

    echo "<h3>Migrasi Selesai Tanpa Error!</h3>";

} catch (Exception $e) {
    echo "<h3 style='color:red'>Terjadi Kesalahan:</h3>";
    echo $e->getMessage();
}
