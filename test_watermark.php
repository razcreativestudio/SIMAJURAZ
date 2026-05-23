<?php
require 'C:\laragon\www\SIMAJURAZ\RAZconfig.php';
$pdo = RAZgetConnection();
$stmt = $pdo->prepare("SELECT * FROM stores LIMIT 1");
$stmt->execute();
$store = $stmt->fetch();
echo json_encode(['receipt_show_logo' => $store['receipt_show_logo'], 'store_logo' => $store['store_logo']]);
