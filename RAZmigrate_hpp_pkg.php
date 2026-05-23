<?php
require_once __DIR__ . '/RAZconfig.php';
$pdo = RAZgetConnection();
if (!$pdo) { echo "DB error"; exit(1); }
$t = RAZgetDbType();

try {
    // Add columns to hpp_packagings if they don't exist
    // SQLite uses different ALTER TABLE syntax, but ADD COLUMN is supported
    $cols = [
        ['name' => 'purchase_qty', 'def' => 'DECIMAL(15,2) DEFAULT 1'],
        ['name' => 'purchase_unit', 'def' => "VARCHAR(30) DEFAULT 'pack'"],
        ['name' => 'usage_per_portion', 'def' => 'INT DEFAULT 1']
    ];

    foreach ($cols as $col) {
        try {
            $pdo->exec("ALTER TABLE hpp_packagings ADD COLUMN {$col['name']} {$col['def']}");
            echo "Added {$col['name']}\n";
        } catch (Exception $e) {
            // Column might already exist, ignore
            echo "Skipped {$col['name']} (may exist)\n";
        }
    }
    echo "Migration OK";
} catch(Exception $e) { 
    echo "Error: ".$e->getMessage(); 
}
