<?php
$content = file_get_contents('C:\laragon\www\SIMAJURAZ\includes\RAZlang.php');

$replacements = [
    'toko Bapak!' => 'toko Anda!',
    'Bapak ingin' => 'Anda ingin',
    'Karyawan Bapak' => 'Karyawan Anda',
    'Berapa Bapak beli' => 'Berapa Anda beli',
    'yang Bapak inginkan' => 'yang Anda inginkan',
    'Bapak <strong>HARUS' => 'Anda <strong>HARUS',
    'Jika Bapak menjual' => 'Jika Anda menjual',
    'Bersih Bapak' => 'Bersih Anda',
    'di sini Bapak bisa' => 'di sini Anda bisa',
    'penjualan Bapak bulan itu' => 'penjualan Anda bulan itu',
    'Bank Bapak' => 'Bank Anda',
    'memberitahu Bapak' => 'memberitahu Anda',
    'bisnis ritel/F&B Bapak' => 'bisnis ritel/F&B Anda',
    'Membantu UMKM Indonesia Go Digital.' => 'Membantu UMKM Indonesia Go Digital.', // Just a reference
];

foreach ($replacements as $search => $replace) {
    $content = str_replace($search, $replace, $content);
}

file_put_contents('C:\laragon\www\SIMAJURAZ\includes\RAZlang.php', $content);
file_put_contents('c:\Users\Administrator\Documents\DATA ZUL\RAZ\SIMAJURAZ\includes\RAZlang.php', $content);
echo "Words replaced successfully!";
?>
