<?php
require 'C:\laragon\www\SIMAJURAZ\RAZconfig.php';
$pdo = RAZgetConnection();
echo "hpp_calculations schema:\n";
$stmt = $pdo->query("PRAGMA table_info(hpp_calculations)");
while ($row = $stmt->fetch()) {
    echo "- " . $row['name'] . "\n";
}
