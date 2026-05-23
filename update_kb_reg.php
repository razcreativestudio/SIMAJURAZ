<?php
$content = file_get_contents('c:\Users\Administrator\Documents\DATA ZUL\RAZ\SIMAJURAZ\includes\RAZlang.php');

$id_kb1 = "
        'kb_sidebar_1' => 'Mendaftar & Pengaturan Toko',
        'kb_sidebar_2' => 'Manajemen Inventori (Barang)',
        'kb_sidebar_3' => 'Operasional Kasir (POS)',
        'kb_sidebar_4' => 'Keuangan & Arus Kas',
        'kb_sidebar_5' => 'Penggajian Karyawan',
        'kb_sidebar_6' => 'Kalkulator HPP Premium',
        'kb_sidebar_7' => 'Sistem Bagi Hasil (Investor)',
        'kb_sidebar_8' => 'Cetak & Ekspor Laporan PDF',
        
        'kb_c1_title' => 'Mendaftar & Pengaturan Toko',
        'kb_c1_desc' => '<p>Selamat datang di SIMAJURAZ! Langkah pertama sebelum menggunakan aplikasi adalah membuat akun toko Anda sendiri.</p>
        <h4>A. Cara Mendaftar Akun Toko Baru</h4>
        <ol>
            <li>Buka halaman <strong>Login</strong> (klik tombol Masuk di kanan atas halaman utama).</li>
            <li>Di jendela login, klik tautan <strong>Daftar di sini</strong> yang ada di bagian bawah form.</li>
            <li><strong>Form Pendaftaran:</strong>
                <ul>
                    <li><strong>Nama Toko:</strong> Masukkan nama bisnis atau kedai Anda.</li>
                    <li><strong>Username:</strong> Buat username tanpa spasi (misal: <code>tokobudi</code>) yang akan digunakan untuk login.</li>
                    <li><strong>Password:</strong> Masukkan kata sandi rahasia Anda.</li>
                </ul>
            </li>
            <li>Klik tombol <strong>Daftar Akun</strong>. Akun Anda akan langsung jadi dan Anda otomatis masuk ke Dashboard!</li>
        </ol>
        <h4>B. Pengaturan Toko & Invoice</h4>
        <ol>
            <li><strong>Masuk ke Pengaturan:</strong> Setelah login, klik menu <strong>Pengaturan</strong> di sidebar sebelah kiri.</li>
            <li><strong>Nama, Alamat & Logo:</strong> Isi nama toko, jenis usaha (misal: F&B, Ritel), alamat lengkap, dan unggah Logo Toko. Logo ini akan otomatis tampil di Struk belanja pelanggan.</li>
            <li><strong>Pengaturan Invoice:</strong> 
                <ul>
                    <li><strong>Prefix Invoice:</strong> Teks awalan untuk nomor nota (Contoh: `INV` akan menghasilkan `INV-2024-001`).</li>
                    <li><strong>Header & Footer Struk:</strong> Anda bisa menambahkan teks kustom seperti \"Terima kasih atas kunjungan Anda\" atau info garansi di bagian atas/bawah struk.</li>
                    <li><strong>Template Invoice:</strong> Pilih desain struk dari opsi yang tersedia (misal: Minimalist, Standard). Ada tombol <strong>Preview</strong> untuk melihat hasilnya.</li>
                </ul>
            </li>
            <li>Klik <strong>Simpan Pengaturan</strong> di pojok kanan bawah.</li>
        </ol>
        <h4>C. Manajemen Akun & Karyawan</h4>
        <ol>
            <li><strong>Ganti Password:</strong> Di tab <em>Akun Saya</em>, masukkan password lama, lalu ketik password baru.</li>
            <li><strong>Tambah Karyawan:</strong> Buka menu <strong>Karyawan</strong>. Klik tombol hijau <strong>Tambah Karyawan</strong>.</li>
            <li>Pilih peran (Kasir/Admin), masukkan username dan password awal mereka. (Note: Karyawan juga bisa mengganti password mereka sendiri melalui menu Pengaturan setelah login).</li>
        </ol>',";

$en_kb1 = str_replace(
    ['Mendaftar & Pengaturan Toko'],
    ['Registering & Store Settings'],
    $id_kb1
);

$pattern_id = "/'kb_sidebar_1' => 'Memulai \(Instalasi & Pengaturan Toko\)'.*?'kb_c1_desc' => '.*?<\/ol>',/s";
$content = preg_replace($pattern_id, ltrim($id_kb1), $content);

$pattern_en = "/'kb_sidebar_1' => 'Getting Started \(Install & Settings\)'.*?'kb_c1_desc' => '.*?<\/ol>',/s";
$content = preg_replace($pattern_en, ltrim($en_kb1), $content);

file_put_contents('c:\Users\Administrator\Documents\DATA ZUL\RAZ\SIMAJURAZ\includes\RAZlang.php', $content);
echo "Lang file updated!";
?>
