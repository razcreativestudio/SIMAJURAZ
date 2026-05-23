<?php
// 1. Clean RAZknowledgebase.php
$kb_path1 = 'C:\laragon\www\SIMAJURAZ\RAZknowledgebase.php';
$kb_path2 = 'c:\Users\Administrator\Documents\DATA ZUL\RAZ\SIMAJURAZ\RAZknowledgebase.php';

$kb_content = file_get_contents($kb_path1);
$kb_content = preg_replace('/<div class="kb-img-container">.*?<\/div>/s', '', $kb_content);
file_put_contents($kb_path1, $kb_content);
file_put_contents($kb_path2, $kb_content);

// 2. Update RAZlang.php with embedded images
$lang_path1 = 'C:\laragon\www\SIMAJURAZ\includes\RAZlang.php';
$lang_path2 = 'c:\Users\Administrator\Documents\DATA ZUL\RAZ\SIMAJURAZ\includes\RAZlang.php';

$lang_content = file_get_contents($lang_path1);

$replacements = [
    // Register
    '<h4>A. Cara Mendaftar Akun Toko Baru</h4>' => '<h4>A. Cara Mendaftar Akun Toko Baru</h4>
        <div class="kb-img-container"><img src="assets/images/ss_register.png" alt="Register" class="kb-img-showcase"></div>',
    
    // Settings
    '<h4>B. Mengatur Profil & Invoice Toko</h4>' => '<h4>B. Mengatur Profil & Invoice Toko</h4>
        <div class="kb-img-container"><img src="assets/images/ss_settings_top.png" alt="Settings" class="kb-img-showcase"></div>',
        
    // Employee Add
    '<h4>C. Mengganti Password & Menambah Karyawan</h4>' => '<h4>C. Mengganti Password & Menambah Karyawan</h4>
        <div class="kb-img-container"><img src="assets/images/ss_users_add.png" alt="Add Employee" class="kb-img-showcase"></div>',
        
    // Inventory
    '<h4>B. Menambah Barang Secara Detail</h4>' => '<h4>B. Menambah Barang Secara Detail</h4>
        <div class="kb-img-container"><img src="assets/images/ss_inventory_modal.png" alt="Inventory Modal" class="kb-img-showcase"></div>',

    // POS Payment
    '<h4>C. Melakukan Pembayaran</h4>' => '<h4>C. Melakukan Pembayaran</h4>
        <div class="kb-img-container"><img src="assets/images/ss_pos_payment.png" alt="Payment Modal" class="kb-img-showcase"></div>',

    // POS Receipt
    '<h4>D. Mencetak & Membagikan Struk</h4>' => '<h4>D. Mencetak & Membagikan Struk</h4>
        <div class="kb-img-container"><img src="assets/images/ss_pos_receipt.png" alt="Receipt Modal" class="kb-img-showcase"></div>',

    // Finance In
    '<h4>B. Menginput Pemasukan Lain (Selain Jualan)</h4>' => '<h4>B. Menginput Pemasukan Lain (Selain Jualan)</h4>
        <div class="kb-img-container"><img src="assets/images/ss_finance_in.png" alt="Income Modal" class="kb-img-showcase"></div>',

    // Finance Out
    '<h4>C. Menginput Pengeluaran Toko</h4>' => '<h4>C. Menginput Pengeluaran Toko</h4>
        <div class="kb-img-container"><img src="assets/images/ss_finance_out.png" alt="Expense Modal" class="kb-img-showcase"></div>',

    // Payroll
    '<h4>A. Memproses Penggajian</h4>' => '<h4>A. Memproses Penggajian</h4>
        <div class="kb-img-container"><img src="assets/images/ss_payroll_modal.png" alt="Payroll Modal" class="kb-img-showcase"></div>',

    // HPP
    '<h4>Cara Simulasi Harga Racikan Makanan:</h4>' => '<h4>Cara Simulasi Harga Racikan Makanan:</h4>
        <div class="kb-img-container"><img src="assets/images/ss_hpp.png" alt="HPP Calculator" class="kb-img-showcase"></div>',

    // Profit Share
    '<h4>A. Mendaftarkan Nama Investor</h4>' => '<h4>A. Mendaftarkan Nama Investor</h4>
        <div class="kb-img-container"><img src="assets/images/ss_profitshare_tab.png" alt="Profit Share" class="kb-img-showcase"></div>',

    // Reports
    '<h4>Cara Mencari Tombol Ekspor di Setiap Fitur:</h4>' => '<h4>Cara Mencari Tombol Ekspor di Setiap Fitur:</h4>
        <div class="kb-img-container"><img src="assets/images/ss_reports.png" alt="Reports" class="kb-img-showcase"></div>',
];

foreach ($replacements as $search => $replace) {
    $lang_content = str_replace($search, $replace, $lang_content);
}

file_put_contents($lang_path1, $lang_content);
file_put_contents($lang_path2, $lang_content);

echo "Images embedded in context!";
?>
