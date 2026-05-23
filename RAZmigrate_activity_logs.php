<?php
require_once 'RAZconfig.php';

try {
    $pdo = RAZgetConnection();
    if (!$pdo) die("Koneksi gagal");
    
    // Support auto increment syntax based on db driver
    $isMySQL = ($pdo->getAttribute(PDO::ATTR_DRIVER_NAME) === 'mysql');
    $autoInc = $isMySQL ? "INT AUTO_INCREMENT PRIMARY KEY" : "INTEGER PRIMARY KEY AUTOINCREMENT";
    $timestamp = $isMySQL ? "DATETIME DEFAULT CURRENT_TIMESTAMP" : "DATETIME DEFAULT CURRENT_TIMESTAMP";

    $pdo->exec("CREATE TABLE IF NOT EXISTS activity_logs (
        id {$autoInc},
        user_id INT DEFAULT NULL,
        store_id INT DEFAULT NULL,
        action VARCHAR(100) NOT NULL,
        details TEXT DEFAULT NULL,
        ip_address VARCHAR(50) DEFAULT NULL,
        user_agent TEXT DEFAULT NULL,
        created_at {$timestamp}
    )");

    echo "Tabel activity_logs berhasil dibuat atau sudah ada.";
} catch (PDOException $e) {
    die("Database Error: " . $e->getMessage());
}
?>
