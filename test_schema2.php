<?php
require 'C:\laragon\www\SIMAJURAZ\RAZconfig.php';
$pdo = RAZgetConnection();
$check = $pdo->query("PRAGMA table_info(stores)")->fetchAll(PDO::FETCH_ASSOC);
print_r($check);
