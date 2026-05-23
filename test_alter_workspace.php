<?php
require 'c:\Users\Administrator\Documents\DATA ZUL\RAZ\SIMAJURAZ\RAZconfig.php';
try {
    $pdo = RAZgetConnection();
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->exec("ALTER TABLE stores ADD COLUMN invoice_format VARCHAR(50) DEFAULT 'INV-{Ymd}-{SEQ5}'");
    echo "SUCCESS WORKSPACE";
} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage();
}
