<?php $pdo = new PDO('sqlite:C:\laragon\www\SIMAJURAZ\data\simajuraz.sqlite'); $stmt = $pdo->query('PRAGMA table_info(stores);'); print_r($stmt->fetchAll(PDO::FETCH_ASSOC));
