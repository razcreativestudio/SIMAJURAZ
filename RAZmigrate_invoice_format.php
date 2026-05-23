<?php
/**
 * ============================================================
 * Skrip Migrasi: Update Struktur Tabel Stores untuk Invoice Format
 * ============================================================
 * Menambahkan kolom 'invoice_format' untuk mendukung dinamis
 * template nomor invoice/struk.
 */

require_once __DIR__ . '/RAZconfig.php';

try {
    $pdo = RAZgetConnection();
    
    // Cek apakah kolom invoice_format sudah ada
    $check = $pdo->query("PRAGMA table_info(stores)")->fetchAll(PDO::FETCH_ASSOC);
    $columnExists = false;
    foreach ($check as $col) {
        if ($col['name'] === 'invoice_format') {
            $columnExists = true;
            break;
        }
    }

    if (!$columnExists) {
        // Tambahkan kolom invoice_format
        $pdo->exec("ALTER TABLE stores ADD COLUMN invoice_format VARCHAR(50) DEFAULT 'INV-{Ymd}-{SEQ5}'");
        
        // Pindahkan data lama (jika ada invoice_prefix, kita konversi)
        // Kita set format menjadi: PREFIX-{Ymd}-{SEQ5}
        $stores = $pdo->query("SELECT id, invoice_prefix FROM stores")->fetchAll();
        $updateStmt = $pdo->prepare("UPDATE stores SET invoice_format = ? WHERE id = ?");
        foreach ($stores as $store) {
            $prefix = $store['invoice_prefix'] ?: 'INV';
            $format = $prefix . '-{Ymd}-{SEQ5}';
            $updateStmt->execute([$format, $store['id']]);
        }
        
        echo "✅ Migrasi berhasil: Kolom invoice_format berhasil ditambahkan.<br>";
    } else {
        echo "ℹ️ Kolom invoice_format sudah ada.<br>";
    }

} catch (Exception $e) {
    die("❌ Gagal melakukan migrasi: " . $e->getMessage());
}
?>
