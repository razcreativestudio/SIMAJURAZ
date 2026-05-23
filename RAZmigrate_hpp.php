<?php
require_once __DIR__ . '/RAZconfig.php';
$pdo = RAZgetConnection();
if (!$pdo) { echo "DB error"; exit(1); }
$t = RAZgetDbType();
$a = ($t==='sqlite') ? 'INTEGER PRIMARY KEY AUTOINCREMENT' : 'INT AUTO_INCREMENT PRIMARY KEY';
$ts = ($t==='sqlite') ? "TEXT DEFAULT (datetime('now','localtime'))" : "DATETIME DEFAULT CURRENT_TIMESTAMP";
$d = 'DECIMAL(15,2)'; $tx = 'TEXT';
try {
    $pdo->exec("CREATE TABLE IF NOT EXISTS hpp_calculations (id {$a},store_id INT NOT NULL,product_name VARCHAR(150) NOT NULL,portions INT DEFAULT 1,overhead_pct {$d} DEFAULT 15,margin_pct {$d} DEFAULT 30,current_sell_price {$d} DEFAULT 0,notes {$tx},is_active TINYINT(1) DEFAULT 1,created_at {$ts},updated_at {$ts})");
    $pdo->exec("CREATE TABLE IF NOT EXISTS hpp_ingredients (id {$a},hpp_id INT NOT NULL,name VARCHAR(100) NOT NULL,purchase_qty {$d} DEFAULT 1,purchase_unit VARCHAR(30) DEFAULT 'pcs',purchase_price {$d} DEFAULT 0,portions_yield INT DEFAULT 1,sort_order INT DEFAULT 0)");
    $pdo->exec("CREATE TABLE IF NOT EXISTS hpp_packagings (id {$a},hpp_id INT NOT NULL,name VARCHAR(100) NOT NULL,purchase_price {$d} DEFAULT 0,capacity_pcs INT DEFAULT 1,sort_order INT DEFAULT 0)");
    $pdo->exec("CREATE TABLE IF NOT EXISTS hpp_extra_costs (id {$a},hpp_id INT NOT NULL,name VARCHAR(100) NOT NULL,amount {$d} DEFAULT 0,portions_divide INT DEFAULT 1,sort_order INT DEFAULT 0)");
    echo "OK";
} catch(Exception $e) { echo "Error: ".$e->getMessage(); }
