<?php
require_once 'RAZconfig.php';
$pdo = RAZgetConnection();
$stmt = $pdo->query("PRAGMA table_info(hpp_calculations)");
$columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
print_r($columns);
