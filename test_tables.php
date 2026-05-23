<?php
require 'C:\laragon\www\SIMAJURAZ\RAZconfig.php';
$pdo = RAZgetConnection();
echo "Tables:\n";
$stmt = $pdo->query("SELECT name FROM sqlite_master WHERE type='table'");
while ($row = $stmt->fetch()) {
    echo "- " . $row['name'] . "\n";
}
