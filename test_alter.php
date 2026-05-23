<?php
require 'C:\laragon\www\SIMAJURAZ\RAZconfig.php';
try {
    $pdo = RAZgetConnection();
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->exec("ALTER TABLE stores ADD COLUMN invoice_format VARCHAR(50) DEFAULT 'INV-{Ymd}-{SEQ5}'");
    echo "SUCCESS";
} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage();
}
