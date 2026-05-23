<?php
require_once __DIR__ . '/RAZconfig.php';

try {
    $pdo = RAZgetConnection();
    $driver = $pdo->getAttribute(PDO::ATTR_DRIVER_NAME);
    $autoInc = ($driver === 'mysql') ? 'INT AUTO_INCREMENT PRIMARY KEY' : 'INTEGER PRIMARY KEY AUTOINCREMENT';

    $sql = "CREATE TABLE IF NOT EXISTS salaries (
        id {$autoInc},
        store_id INT NOT NULL,
        user_id INT NOT NULL,
        period_type VARCHAR(50) NOT NULL,
        base_salary DECIMAL(15,2) DEFAULT 0,
        bonus DECIMAL(15,2) DEFAULT 0,
        deduction DECIMAL(15,2) DEFAULT 0,
        net_salary DECIMAL(15,2) DEFAULT 0,
        payment_date DATE NOT NULL,
        notes TEXT,
        cashflow_id INT DEFAULT NULL,
        created_by INT NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )";

    $pdo->exec($sql);
    echo "Tabel 'salaries' berhasil dibuat atau sudah ada.";
} catch (PDOException $e) {
    echo "Gagal membuat tabel salaries: " . $e->getMessage();
}
?>
