<?php
session_start();
$_SESSION['user_id'] = 1;
$_SESSION['store_id'] = 1;
$_SESSION['role'] = 'owner';
$_GET['action'] = 'update_receipt';
file_put_contents('php://input', json_encode([
    'invoice_format' => 'INV',
    'receipt_template' => 1,
    'receipt_header' => '',
    'receipt_footer' => '',
    'receipt_show_logo' => 1
]));
require 'C:\laragon\www\SIMAJURAZ\api\RAZapiSettings.php';
