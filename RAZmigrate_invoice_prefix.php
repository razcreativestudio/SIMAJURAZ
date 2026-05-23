<?php
require_once __DIR__ . '/RAZconfig.php';
$pdo = RAZgetConnection();
if (!$pdo) { echo "DB error"; exit(1); }

try {
    $pdo->exec("ALTER TABLE stores ADD COLUMN invoice_prefix VARCHAR(10) DEFAULT 'INV'");
    echo "Added invoice_prefix\n";
} catch (Exception $e) {
    echo "Skipped invoice_prefix (may exist)\n";
}
echo "Migration OK";
