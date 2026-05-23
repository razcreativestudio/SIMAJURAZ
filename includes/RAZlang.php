<?php
/**
 * ============================================================
 * RAZlang.php ?" Language Dictionary (i18n)
 * ============================================================
 */

// Menentukan bahasa
$current_lang = 'id';
if (isset($_GET['lang']) && in_array($_GET['lang'], ['id', 'en'])) {
    $current_lang = $_GET['lang'];
    setcookie('raz_lang', $current_lang, time() + (86400 * 30), "/"); // 30 hari
} elseif (isset($_COOKIE['raz_lang']) && in_array($_COOKIE['raz_lang'], ['id', 'en'])) {
    $current_lang = $_COOKIE['raz_lang'];
}

$LANG_DICT = [
    'id' => [
        // Navbar
        'nav_features' => 'Fitur Unggulan',
        'nav_tech' => 'Teknologi',
        'nav_kb' => 'Panduan (KB)',
        'nav_login' => 'Masuk / Login',
        
        // Hero
        'hero_badge' => '100% GRATIS SELAMANYA',
        'hero_title_1' => 'Kelola Bisnis Anda',
        'hero_title_2' => 'Lebih Mudah dengan',
        'hero_desc' => 'Aplikasi Kasir (POS), manajemen inventori, perhitungan HPP otomatis, hingga laporan laba rugi dalam satu platform elegan dan responsif. Cocok untuk UMKM, toko ritel, dan segala jenis usaha.',
        'hero_btn_start' => 'Mulai Sekarang Gratis',
        'hero_btn_learn' => 'Pelajari Fitur',
        
        // Tech
        'tech_title' => 'Ditenagai Oleh Teknologi Web Modern',
        'tech_desc' => 'SIMAJURAZ dibangun menggunakan arsitektur yang sangat ringan namun bertenaga, memastikan aplikasi berjalan cepat di berbagai perangkat tanpa memerlukan spesifikasi server yang tinggi.',
        'tech_php' => 'PHP 8.x Native (Sangat Cepat & Efisien)',
        'tech_db' => 'SQLite / MySQL (Penyimpanan Multi-Tenant)',
        'tech_css' => 'Vanilla CSS3 (Desain Glassmorphism)',
        'tech_js' => 'Vanilla JavaScript (Interaksi Real-time)',
        
        // Features Detailed (Grid)
        'feat_title' => 'Fitur Kelas Enterprise, Tanpa Biaya Tambahan',
        'feat_1_title' => 'Dashboard Analitik Lengkap',
        'feat_1_desc' => 'Pantau performa bisnis Anda secara langsung. Lihat item terlaris, total penjualan hari ini, dan pergerakan kas di dalam grafik visual yang intuitif.',
        
        'feat_2_title' => 'Manajemen Inventori Cerdas',
        'feat_2_desc' => 'Stok terpotong otomatis saat transaksi. Ketahui peringatan sisa stok menipis, HPP (Harga Modal) dasar, dan pencatatan barang rusak/basi secara real-time.',
        
        'feat_3_title' => 'Point of Sale (POS) Modern',
        'feat_3_desc' => 'Antarmuka kasir yang sangat cepat, mendukung pencarian nama atau barcode scanner, serta kalkulasi kembalian otomatis agar transaksi tidak pernah tertunda.',
        
        'feat_4_title' => 'Laporan Arus Kas & Laba Rugi',
        'feat_4_desc' => 'Sistem akan memantau seluruh uang masuk, pengeluaran toko, dan setoran modal. Laporan Laba Rugi akan dihitung secara presisi setiap saat.',
        
        'feat_5_title' => 'Sistem Penggajian & Cetak Slip',
        'feat_5_desc' => 'Atur gaji karyawan secara otomatis memotong uang kas toko Anda. Cetak Slip Gaji berformat PDF langsung dari dashboard Karyawan.',
        
        'feat_6_title' => 'Kalkulator HPP Premium',
        'feat_6_desc' => 'Hitung Harga Pokok Penjualan resep masakan Anda secara akurat (Food Cost). Kalkulator pintar akan merekomendasikan Harga Jual Optimal untuk produk Anda.',

        'feat_7_title' => 'Ekspor Laporan PDF',
        'feat_7_desc' => 'Ekspor riwayat transaksi, buku besar, hingga laporan inventori ke dokumen PDF berstandar profesional ukuran A4 dengan 1 kali klik.',

        'feat_8_title' => 'Multi-User & Hak Akses',
        'feat_8_desc' => 'Dukung banyak toko dalam 1 server (Multi-Tenant). Karyawan hanya dapat berjualan tanpa bisa melihat data laporan HPP & Keuangan utama.',
        
        // Extended Visual Showcase (Zig-Zag)
        'showcase_title' => 'Lihat Lebih Dekat',
        
        'sc_1_title' => 'Kasir Cepat & Responsif',
        'sc_1_desc' => 'Berjualan tanpa lag. Antarmuka POS kami didesain dengan tombol besar dan fitur pencarian instan untuk melayani pelanggan dalam hitungan detik.',
        
        'sc_2_title' => 'Dashboard Analitik Bisnis',
        'sc_2_desc' => 'Ketahui apa yang paling laku dijual. Grafik pendapatan interaktif membantu Anda memantau tren penjualan harian, mingguan, maupun bulanan.',
        
        'sc_3_title' => 'Keuangan Transparan',
        'sc_3_desc' => 'Tidak perlu lagi pusing menghitung laba kotor vs laba bersih. Aplikasi mencatat semua pengeluaran operasional (seperti pembelian gas/minyak) dan menggabungkannya ke laporan laba bersih akhir.',

        'sc_4_title' => 'Panduan (Knowledgebase) Lengkap',
        'sc_4_desc' => 'Jangan khawatir jika Anda baru pertama kali memakai sistem kasir. Kami menyediakan halaman Panduan Lengkap yang menuntun Anda dari instalasi hingga mahar mengelola toko.',

        // Footer
        'footer_text' => 'SIMAJURAZ by RAZ Creative Studio. Membantu UMKM Indonesia Go Digital.',

        // KB
        'kb_title' => 'Pusat Bantuan & Panduan Penggunaan',
        'kb_desc' => 'Pelajari cara memaksimalkan penggunaan SIMAJURAZ untuk operasional bisnis Anda dengan panduan sangat rinci ini.',
        
        'kb_sidebar_1' => 'Mendaftar & Pengaturan Toko',
        'kb_sidebar_2' => 'Manajemen Inventori (Barang)',
        'kb_sidebar_3' => 'Operasional Kasir (POS)',
        'kb_sidebar_4' => 'Keuangan & Arus Kas',
        'kb_sidebar_5' => 'Penggajian Karyawan',
        'kb_sidebar_6' => 'Kalkulator HPP Premium',
        'kb_sidebar_7' => 'Sistem Bagi Hasil (Investor)',
        'kb_sidebar_8' => 'Cetak & Ekspor Laporan PDF',
        
        'kb_c1_title' => 'Mendaftar & Pengaturan Toko',
        'kb_c1_desc' => '<p>Selamat datang di SIMAJURAZ! Sebelum mulai berjualan, mari kita siapkan akun dan profil toko Anda.</p>
        <h4>A. Cara Mendaftar Akun Toko Baru</h4>
        <div class="kb-img-container"><img src="assets/images/ss_register.png" alt="Register" class="kb-img-showcase"></div>
        <ol>
            <li>Buka halaman <strong>Login</strong> dengan mengeklik tombol "Masuk / Login" di pojok kanan atas halaman utama.</li>
            <li>Pada form login, perhatikan bagian paling bawah dan klik teks bertuliskan <strong>Daftar di sini</strong>. Form akan otomatis berubah menjadi form Pendaftaran.</li>
            <li><strong>Pengisian Form Pendaftaran:</strong>
                <ul>
                    <li><strong>Nama Toko:</strong> Ketikkan nama usaha Anda (misalnya "Kedai Kopi Senja"). Nama ini akan menjadi identitas utama Anda di sistem.</li>
                    <li><strong>Username:</strong> Buat sebuah nama pengguna tanpa spasi (misalnya <code>kopisenja</code>). Ingat username ini karena akan selalu digunakan setiap kali Anda login!</li>
                    <li><strong>Password:</strong> Masukkan kata sandi rahasia yang kuat. Jangan beritahukan kepada siapa pun.</li>
                </ul>
            </li>
            <li>Setelah semua terisi, klik tombol biru <strong>Daftar & Mulai</strong>. Anda akan langsung dibawa masuk ke halaman Dashboard!</li>
        </ol>
        <h4>B. Mengatur Profil & Invoice Toko</h4>
        <div class="kb-img-container"><img src="assets/images/ss_settings_top.png" alt="Settings" class="kb-img-showcase"></div>
        <ol>
            <li>Setelah masuk (login), lihat panel menu di sebelah kiri layar (Sidebar). Klik menu <strong>Pengaturan Toko</strong> berlogo roda gigi.</li>
            <li>Di tab <em>Profil Toko</em>, Anda bisa melengkapi:
                <ul>
                    <li><strong>Jenis Usaha:</strong> Pilih kategori bisnis Anda (F&B, Ritel, Jasa, dll).</li>
                    <li><strong>Alamat Lengkap:</strong> Masukkan alamat toko fisik Anda. Alamat ini akan tercetak otomatis di struk belanja pelanggan.</li>
                    <li><strong>Logo Toko:</strong> Klik kotak "Unggah Logo" untuk memilih file foto logo Anda dari komputer/HP. Logo inilah yang akan menjadi ikon di sudut nota.</li>
                </ul>
            </li>
            <li>Selanjutnya, pindah ke tab <strong>Pengaturan Invoice</strong> di sebelah kanan atas:
                <ul>
                    <li><strong>Prefix Invoice:</strong> Ini adalah huruf awalan untuk nomor struk Anda. Jika Anda mengisi <code>INV</code>, maka nota pertama akan bernama <code>INV-2026-001</code>. Jika toko Anda bernama "Senja", Anda bisa memakai prefix <code>SNJ</code>.</li>
                    <li><strong>Teks Header Struk:</strong> Kalimat sapaan yang muncul di bagian paling atas struk (misal: "Selamat Datang di Kedai Kami").</li>
                    <li><strong>Teks Footer Struk:</strong> Kalimat penutup di bagian paling bawah struk (misal: "Barang yang sudah dibeli tidak dapat ditukar. Terima Kasih!").</li>
                    <li><strong>Pilihan Template Struk:</strong> SIMAJURAZ menyediakan beberapa gaya desain struk (Minimalist, Standard, dll). Pilih salah satu, lalu klik tombol abu-abu <strong>Preview Invoice</strong> untuk melihat persis bagaimana struk itu akan dicetak nantinya!</li>
                </ul>
            </li>
            <li>Pastikan Anda mengeklik tombol biru <strong>Simpan Pengaturan</strong> di pojok kanan bawah agar semua data tidak hilang.</li>
        </ol>
        <h4>C. Mengganti Password & Menambah Karyawan</h4>
        <div class="kb-img-container"><img src="assets/images/ss_users_add.png" alt="Add Employee" class="kb-img-showcase"></div>
        <ol>
            <li><strong>Ubah Password Sendiri:</strong> Di menu Pengaturan yang sama, buka tab <em>Akun Saya</em>. Masukkan password lama Anda untuk verifikasi, lalu ketik password baru. Klik Simpan.</li>
            <li><strong>Mendaftarkan Kasir/Karyawan:</strong> Agar karyawan tidak memakai akun utama Anda (yang bisa melihat rahasia keuangan), buatkan mereka akun sendiri! Buka menu <strong>Karyawan</strong> di panel sebelah kiri.</li>
            <li>Klik tombol hijau <strong>Tambah Karyawan</strong> di sudut kanan atas. Sebuah jendela (*modal*) akan muncul.</li>
            <li>Di dalam jendela tersebut, pilih peran karyawan (misal: Kasir). Buatkan username (contoh: <code>kasir1</code>) dan password default (contoh: <code>123456</code>). Berikan kredensial ini kepada karyawan Anda agar ia bisa login di perangkat kasir. (Karyawan nantinya bisa mengubah passwordnya sendiri via menu pengaturan mereka).</li>
        </ol>',
        
        'kb_c2_title' => 'Manajemen Inventori (Barang)',
        'kb_c2_desc' => '<p>Inventori adalah otak dari toko Anda. Pastikan Anda mengisi data barang seakurat mungkin sebelum bertransaksi.</p>
        <h4>A. Menambah & Menghapus Kategori</h4>
        <ol>
            <li>Buka menu <strong>Inventori</strong>.</li>
            <li>Sebelum membuat barang, buatlah "Grup" barang terlebih dahulu. Klik tombol <strong>Tambah Kategori</strong>.</li>
            <li>Ketik nama kategori (contoh: "Minuman Dingin" atau "Bahan Baku"). Klik Simpan. Kategori akan muncul sebagai "Chip" tombol yang bisa diklik.</li>
            <li>Jika ada kategori salah ketik, Anda bisa menghapusnya dengan menekan ikon <strong>Tempat Sampah (Tong Sampah) merah</strong> di sebelahnya.</li>
        </ol>
        <h4>B. Menambah Barang Secara Detail</h4>
        <div class="kb-img-container"><img src="assets/images/ss_inventory_modal.png" alt="Inventory Modal" class="kb-img-showcase"></div>
        <ol>
            <li>Klik tombol hijau <strong>Tambah Barang</strong>. Sebuah jendela form besar akan menutupi layar. Mari kita isi satu per satu:</li>
            <li><strong>Unggah Foto Produk:</strong> Di sebelah kiri, klik kotak bergambar kamera. Pilih foto makanan/produk Anda. Foto ini sangat berguna di halaman Kasir agar mata Anda cepat mengenali barang.</li>
            <li><strong>Nama Barang & Kategori:</strong> Isi nama produk (misal: "Kopi Susu Gula Aren"). Lalu pilih Kategorinya dari daftar yang sudah Anda buat tadi.</li>
            <li><strong>SKU atau Barcode Scanner:</strong> SKU (Stock Keeping Unit) adalah kode identitas produk. 
                <ul>
                    <li>Jika toko kelontong: Arahkan kursor (*mouse*) ke kolom ini, lalu <strong>tembak barcode di kemasan jajan menggunakan alat Barcode Scanner</strong>. Angka akan otomatis terisi!</li>
                    <li>Jika kafe/restoran: Anda bisa mengarang kode sendiri, misalnya <code>KPS-001</code>.</li>
                </ul>
            </li>
            <li><strong>HPP (Harga Pokok) vs Harga Jual:</strong> 
                <ul>
                    <li><strong>HPP (Harga Pokok Penjualan):</strong> Ini adalah MODAL DASAR barang tersebut. Berapa biaya yang Anda keluarkan untuk membuat/membeli 1 item ini? <em>Jangan dikosongkan dan jangan diisi salah!</em> Jika HPP salah, Laba Rugi Anda tidak akan pernah akurat.</li>
                    <li><strong>Harga Jual:</strong> Ini adalah harga yang akan dibayarkan oleh pelanggan di kasir.</li>
                    <li>Setelah mengisi keduanya, lihat ke teks kecil di bawahnya: Sistem akan otomatis menghitung <strong>Margin Keuntungan</strong> Anda (contoh: +30%).</li>
                </ul>
            </li>
            <li><strong>Manajemen Stok:</strong>
                <ul>
                    <li><strong>Stok Awal:</strong> Masukkan jumlah barang fisik yang ada di toko/gudang Anda saat ini. Stok ini otomatis BERKURANG SATU setiap kali kasir menekan tombol Bayar!</li>
                    <li><strong>Batas Minimum Stok:</strong> Angka peringatan. Misalnya diisi `5`. Saat stok barang tersisa 5, tulisan stok akan berubah warna menjadi MERAH. Ini adalah alarm agar Anda segera kulakan/restock.</li>
                </ul>
            </li>
            <li>Periksa kembali semua isian. Jika sudah yakin, klik tombol biru <strong>Simpan Barang</strong> di pojok kanan bawah. Selesai!</li>
        </ol>',
        
        'kb_c3_title' => 'Operasional Kasir (POS)',
        'kb_c3_desc' => '<p>Halaman Kasir (Point of Sale) didesain untuk transaksi super cepat. Tidak boleh ada antrean yang terhambat di sini!</p>
        <h4>A. Membuka Shift Kasir (Wajib)</h4>
        <ol>
            <li>Masuk ke menu <strong>Kasir (POS)</strong>. Jika ini adalah transaksi pertama Anda di hari ini, sebuah layar gelap (*modal*) akan memblokir Anda.</li>
            <li>Ini adalah prosedur keamanan. Anda wajib menekan <strong>Buka Shift</strong>.</li>
            <li>Di dalam form tersebut, ketikkan <strong>Nominal Uang Kembalian</strong> yang saat ini ada di laci kasir tunai fisik Anda (misalnya Anda menaruh uang receh Rp 100.000).</li>
            <li>Tujuannya? Saat toko tutup nanti, uang di laci fisik harus sama persis dengan angka di laporan sistem (Modal awal + Total Penjualan Tunai).</li>
        </ol>
        <h4>B. Menambahkan Item ke Keranjang</h4>
        <ol>
            <li>Di sisi kiri layar terdapat etalase barang Anda. Klik kotak bergambar produk tersebut, dan ia akan melompat masuk ke "Keranjang Belanja" di sisi kanan.</li>
            <li><strong>Penggunaan Barcode:</strong> Anda tidak perlu repot mengeklik! Pastikan kursor (*mouse*) berada di layar, ambil produk fisik pelanggan, dan "TIT!" tembak dengan <strong>Barcode Scanner</strong>. Barang akan langsung masuk keranjang seketika. Sangat cepat!</li>
            <li><strong>Ubah Kuantitas (Jumlah):</strong> Di panel keranjang kanan, jika pelanggan beli 3 kopi, cukup tekan tombol <code>+</code> di sebelah nama barang sampai angkanya jadi 3. Tekan <code>-</code> jika ingin mengurangi, atau tekan <strong>ikon tong sampah</strong> untuk membatalkan item tersebut.</li>
        </ol>
        <h4>C. Melakukan Pembayaran</h4>
        <div class="kb-img-container"><img src="assets/images/ss_pos_payment.png" alt="Payment Modal" class="kb-img-showcase"></div>
        <ol>
            <li>Jika pesanan sudah sesuai, klik tombol hijau raksasa <strong>BAYAR</strong> di pojok kanan bawah. Jendela Pembayaran akan muncul.</li>
            <li>Lihat angka besar di tengah: Itu adalah <strong>Total Tagihan</strong> pelanggan.</li>
            <li><strong>Pilih Metode Pembayaran:</strong>
                <ul>
                    <li><strong>Tunai (Cash):</strong> Jika pelanggan menyerahkan uang kertas. Ketikkan jumlah uang yang diberikan (misal: Tagihan Rp 45.000, pelanggan memberi uang Rp 50.000. Ketik "50000" di kolom <em>Nominal Dibayar</em>). Sistem akan otomatis memunculkan tulisan <strong>Kembalian: Rp 5.000</strong>.</li>
                    <li><strong>Tombol Cepat Uang Pas:</strong> Jika malas mengetik, kami sediakan tombol cepat di bawahnya (Uang Pas, 20.000, 50.000, 100.000). Klik saja tombol tersebut!</li>
                    <li><strong>Transfer / QRIS / Non-Tunai:</strong> Jika pelanggan bayar via m-banking. Pilih opsi ini. Kolom nominal akan hilang karena pembayaran non-tunai dianggap SELALU PAS (tidak ada kembalian). Uang ini tidak akan tercatat masuk laci kasir fisik, melainkan kas digital.</li>
                </ul>
            </li>
            <li>Klik tombol besar <strong>Proses Bayar</strong>. Transaksi selesai! Stok barang di inventori langsung terpotong.</li>
        </ol>
        <h4>D. Mencetak & Membagikan Struk</h4>
        <div class="kb-img-container"><img src="assets/images/ss_pos_receipt.png" alt="Receipt Modal" class="kb-img-showcase"></div>
        <ol>
            <li>Setelah "Proses Bayar" ditekan, jendela baru akan muncul menampilkan wujud asli struk (lengkap dengan logo toko Anda!).</li>
            <li>Di bawah gambar struk tersebut, ada 3 tombol:</li>
            <li>Tombol <strong>Cetak Struk:</strong> Akan menyambungkan layar ke Printer Thermal Bluetooth/USB Anda. Struk fisik akan keluar.</li>
            <li>Tombol <strong>Bagikan:</strong> Akan membuat file struk digital yang bisa langsung Anda kirim via WhatsApp ke nomor pelanggan. Go Green!</li>
            <li>Tombol <strong>Tutup:</strong> Jika pelanggan tidak butuh struk. Layar kasir akan bersih kembali siap untuk pelanggan berikutnya.</li>
        </ol>',
        
        'kb_c4_title' => 'Keuangan & Arus Kas',
        'kb_c4_desc' => '<p>Modul Keuangan memantau aliran darah bisnis Anda (uang masuk dan keluar). Arus Kas ini berinteraksi langsung dengan Laba Rugi akhir.</p>
        <h4>A. Membaca Laporan Laba Rugi</h4>
        <ol>
            <li>Buka menu <strong>Laporan > Laba Rugi</strong>.</li>
            <li>Di sini, Anda tidak perlu menghitung manual lagi. Rumus cerdas kami bekerja 24 jam: <code>(Semua Uang Penjualan Barang)</code> DIKURANGI <code>(Total HPP / Modal Barang Tersebut)</code> DIKURANGI <code>(Semua Pengeluaran Ekstra Toko)</code>.</li>
            <li>Hasil akhirnya akan tampil besar di kotak <strong>Laba Bersih</strong>. Laba ini menunjukkan keuntungan riil Anda setelah dipotong beban operasional.</li>
            <li>Gunakan ikon Kalender di atas untuk memfilter: Apakah Anda ingin melihat Laba hari ini saja? Laba seminggu ini? Atau bulan lalu?</li>
        </ol>
        <h4>B. Menginput Pemasukan Lain (Selain Jualan)</h4>
        <div class="kb-img-container"><img src="assets/images/ss_finance_in.png" alt="Income Modal" class="kb-img-showcase"></div>
        <ol>
            <li>Buka menu <strong>Keuangan</strong>. Anda akan berada di tab <em>Arus Kas (Buku Kas)</em>.</li>
            <li>Jika ada uang masuk yang BUKAN dari jualan kasir (contoh: Uang hasil jual kardus bekas minuman, atau uang parkir), klik tombol hijau <strong>Pemasukan</strong>.</li>
            <li>Di form yang muncul: Ketikkan <strong>Nominal</strong> (contoh: 25000) dan <strong>Deskripsi</strong> (contoh: "Jual kardus bekas"). Klik Simpan. Uang ini akan mendongkrak Laba Bersih Anda.</li>
        </ol>
        <h4>C. Menginput Pengeluaran Toko</h4>
        <div class="kb-img-container"><img src="assets/images/ss_finance_out.png" alt="Expense Modal" class="kb-img-showcase"></div>
        <ol>
            <li>Ini fitur SANGAT PENTING. Setiap uang yang keluar dari laci untuk operasional toko harus dicatat! Jika tidak, Laba Bersih Anda akan terlihat palsu (seolah besar padahal banyak hutang listrik).</li>
            <li>Klik tombol merah <strong>Pengeluaran</strong>.</li>
            <li>Di form yang muncul: Ketikkan <strong>Nominal</strong> (contoh: 150000) dan <strong>Deskripsi</strong> (contoh: "Beli Gas Elpiji 3Kg" atau "Bayar Tukang Sampah"). Klik Simpan.</li>
            <li>Uang ini akan otomatis mengurangi Laba Bersih di Laporan Akhir.</li>
        </ol>
        <h4>D. Menghapus / Mengedit Catatan yang Salah</h4>
        <ol>
            <li>Di bawah tombol tadi, ada Tabel Riwayat Buku Kas yang menampilkan list semua uang masuk & keluar.</li>
            <li>Cari catatan yang salah (misal typo mengetik 150.000 padahal 15.000).</li>
            <li>Lihat ke ujung paling kanan baris tabel tersebut, di bawah kolom "Aksi".</li>
            <li>Klik ikon <strong>Pensil (Edit)</strong> untuk mengubah nominalnya, ATAU klik ikon <strong>Tempat Sampah Merah (Hapus)</strong> untuk menghapus catatan tersebut sepenuhnya dari sistem.</li>
        </ol>',

        'kb_c5_title' => 'Penggajian Karyawan',
        'kb_c5_desc' => '<p>SIMAJURAZ memiliki modul Payroll cerdas. Gaji yang Anda bayarkan ke karyawan akan langsung tercatat sebagai pengeluaran toko tanpa perlu mencatat dua kali di buku!</p>
        <h4>A. Memproses Penggajian</h4>
        <div class="kb-img-container"><img src="assets/images/ss_payroll_modal.png" alt="Payroll Modal" class="kb-img-showcase"></div>
        <ol>
            <li>Buka menu <strong>Karyawan</strong> di panel sebelah kiri.</li>
            <li>Abaikan tab Daftar Karyawan, klik tab di sebelahnya: <strong>Penggajian (Payroll)</strong>.</li>
            <li>Di sana terdapat tombol hijau terang bertuliskan <strong>Bayar Gaji</strong>. Klik tombol tersebut! Jendela *Form Slip Gaji* akan muncul.</li>
            <li><strong>Pengisian Rincian Gaji:</strong>
                <ul>
                    <li><strong>Pilih Karyawan:</strong> Klik dropdown dan pilih nama kasir/staf yang akan digaji. (Otomatis mengambil data dari Daftar Karyawan).</li>
                    <li><strong>Gaji Pokok:</strong> Masukkan gaji dasar bulanan/hariannya. Misal: 2000000.</li>
                    <li><strong>Bonus:</strong> Jika target penjualannya bagus, berikan ia bonus. Masukkan nominalnya (opsional). Misal: 100000.</li>
                    <li><strong>Potongan / Kasbon:</strong> Jika karyawan tersebut punya kebiasaan mengutang barang dagangan atau utang kasbon, masukkan total utangnya di form ini. Ini akan memotong gajinya. Misal: 50000.</li>
                </ul>
            </li>
            <li>Perhatikan teks tebal di bawahnya! Sistem telah menghitung <strong>Total Gaji Bersih</strong> secara *live* (Gaji + Bonus - Kasbon).</li>
            <li>Klik tombol <strong>Simpan & Bayar</strong>. Selesai! Uang tersebut telah masuk ke buku Arus Kas toko sebagai beban Pengeluaran Gaji.</li>
        </ol>
        <h4>B. Cetak Bukti Slip Gaji</h4>
        <ol>
            <li>Karyawan Anda butuh bukti tertulis? Bisa! Setelah ditekan simpan tadi, lihat ke "Tabel Riwayat Penggajian" di bawahnya.</li>
            <li>Cari catatan gajinya, geser ke paling kanan pada kolom Aksi, lalu klik tombol biru bertuliskan <strong>Download Slip</strong>.</li>
            <li>Browser akan langsung mengunduh file PDF profesional yang menampilkan rincian persis potongan dan bonus gajinya, lengkap dengan logo toko Anda di atasnya!</li>
        </ol>',
        
        'kb_c6_title' => 'Kalkulator HPP Premium',
        'kb_c6_desc' => '<p>Fitur rahasia para pengusaha F&B (Kafe/Resto). Kalkulator ini akan membongkar Harga Modal (*Food Cost*) asli dari sebuah resep secara terperinci.</p>
        <h4>Cara Simulasi Harga Racikan Makanan:</h4>
        <div class="kb-img-container"><img src="assets/images/ss_hpp.png" alt="HPP Calculator" class="kb-img-showcase"></div>
        <ol>
            <li>Buka menu <strong>Kalkulator HPP</strong>.</li>
            <li>Klik tombol besar <strong>Buat Perhitungan Baru</strong>. Anda akan dihadapkan pada satu halaman form panjang yang terbagi 4 Tahapan. Mari kita isi dari atas ke bawah:</li>
            <li><strong>Tahap 1 (Info Dasar):</strong> Beri nama resep ini. Misalnya: "Kopi Susu Gula Aren Cup Medium". Masukkan juga target jumlah porsi jika resep ini dibuat masal (biasanya 1 porsi saja).</li>
            <li><strong>Tahap 2 (Input Bahan Baku Utama):</strong> Ini adalah bagian terseru. Klik tombol <strong>+ Tambah Bahan</strong>.
                <ul>
                    <li>Nama Bahan: Ketik "Biji Kopi Arabica".</li>
                    <li>Harga Beli Mentah: Berapa Anda beli 1 bungkusnya? Misal: Rp 100.000.</li>
                    <li>Berat Total Mentah: 1 bungkus itu berapa isinya? Misal: 1000 Gram (1 Kg).</li>
                    <li>Takaran Untuk 1 Porsi: Untuk bikin 1 cup kopi, butuh berapa gram? Misal: 15 Gram.</li>
                    <li><em>AJAIB!</em> Kalkulator akan memecah harga Rp 100rb/kg tadi dan menyimpulkan bahwa modal kopi untuk 1 cup tersebut hanyalah <strong>Rp 1.500 perak</strong>! Lakukan hal yang sama untuk Susu UHT dan Gula Aren.</li>
                </ul>
            </li>
            <li><strong>Tahap 3 (Input Bahan Kemasan & Ekstra):</strong>
                <ul>
                    <li>Klik tombol tambah di form bahan kemasan. Masukkan item seperti "Gelas Cup Plastik" (misal: Beli 50pcs harga 20rb, berarti 1pcs modalnya Rp 400). Masukkan sedotan, kantong plastik, dll.</li>
                    <li>Jangan lupa biaya gas elpiji dan tenaga listrik barista per porsi agar tidak bocor alus!</li>
                </ul>
            </li>
            <li><strong>Tahap 4 (Kalkulasi Target Margin):</strong>
                <ul>
                    <li>Setelah sistem menghitung total keseluruhan modal dari Tahap 2 & 3 (misalnya Total HPP racikan = Rp 5.000), saatnya menentukan untung!</li>
                    <li>Di kolom Target Margin, ketik berapa persen untung yang Anda inginkan. Misal: 50%.</li>
                    <li>Sistem akan menyimpulkan: Untuk dapat untung 50%, Anda <strong>HARUS menjual produk ini di harga minimal Rp 10.000</strong>! Ini adalah fungsi <em>Rekomendasi Harga Jual</em>.</li>
                </ul>
            </li>
            <li>Setelah puas melihat hasilnya, Anda bisa keluar. Jika Anda menjual produk ini, jangan lupa mendaftarkannya di menu **Inventori** dan masukkan nilai Rp 5000 di kolom HPP, dan Rp 10000 di kolom Harga Jual!</li>
        </ol>',

        'kb_c7_title' => 'Sistem Bagi Hasil (Investor)',
        'kb_c7_desc' => '<p>Membuka toko dengan modal patungan teman/saudara? Jangan berantem masalah persentase untung! Gunakan fitur ini agar perhitungannya *fair* dan transparan.</p>
        <h4>A. Mendaftarkan Nama Investor</h4>
        <div class="kb-img-container"><img src="assets/images/ss_profitshare_tab.png" alt="Profit Share" class="kb-img-showcase"></div>
        <ol>
            <li>Buka menu <strong>Keuangan</strong>. Abaikan tab Arus Kas, klik tab di sebelahnya: <strong>Bagi Hasil (Profit Share)</strong>.</li>
            <li>Di bawah tulisan Distribusi Bagi Hasil, klik tombol tambah (ikon orang+). Jendela modal akan muncul.</li>
            <li><strong>Form Investor:</strong>
                <ul>
                    <li><strong>Nama Investor:</strong> Masukkan nama partner bisnis Anda (misal: Bapak Budi).</li>
                    <li><strong>Porsi / Persentase:</strong> Berapa kesepakatan jatah bersihnya? Ketik angkanya saja (misal: 30 untuk 30%). Pastikan total porsi semua investor tidak lebih dari 100%.</li>
                </ul>
            </li>
            <li>Klik Simpan. Nama Bapak Budi kini muncul di tabel investor dengan persentase 30%.</li>
        </ol>
        <h4>B. Menghitung Pencairan Laba Bulanan</h4>
        <ol>
            <li>Setiap menjelang awal bulan tutup buku (tanggal 30/31), masuk kembali ke halaman ini.</li>
            <li>Lihat panel <em>Riwayat Laporan Bagi Hasil</em>. Klik tombol biru <strong>Generate Laporan Bagi Hasil</strong>.</li>
            <li>Sebuah jendela kalender akan muncul. Pilih <strong>Rentang Tanggal</strong> (Pilih tanggal 1 sampai tanggal 30/31 di bulan tersebut).</li>
            <li>Klik <strong>Proses & Kalkulasi</strong>.</li>
            <li><strong>Apa yang terjadi?</strong> Sistem akan berlari memeriksa total Laba Bersih Anda pada bulan tersebut (Total Penjualan - HPP - Beli Gas - Gaji Karyawan). Misalkan laba bersih final adalah Rp 10.000.000.</li>
            <li>Sistem akan memecahkan uang 10 Juta itu secara otomatis! Sistem mencatat: Jatah Bapak Budi (30%) adalah Rp 3.000.000, Jatah Owner (Sisa 70%) adalah Rp 7.000.000.</li>
            <li>Riwayat pembagian ini akan abadi di tabel. Anda bisa klik <strong>Cetak PDF</strong> untuk diserahkan ke Bapak Budi sebagai bukti transparansi. Jika uang sudah ditransfer, klik tombol <strong>Tandai Sudah Dibayar</strong> agar laporannya berstatus hijau (Lunas).</li>
        </ol>',
        
        'kb_c8_title' => 'Cetak & Ekspor Laporan PDF',
        'kb_c8_desc' => '<p>Kecanggihan sebuah sistem terletak pada kemampuan mencetak laporannya. Di SIMAJURAZ, hampir setiap halaman punya tombol <strong>Ekspor PDF</strong> sekali klik yang akan men- *download* dokumen ukuran A4 yang sangat rapi untuk diserahkan ke atasan.</p>
        <h4>Cara Mencari Tombol Ekspor di Setiap Fitur:</h4>
        <div class="kb-img-container"><img src="assets/images/ss_reports.png" alt="Reports" class="kb-img-showcase"></div>
        <ul>
            <li><strong>Laba Rugi Lengkap:</strong> Buka menu <em>Laporan > Laba Rugi</em>. Atur filter tanggal (Dari - Sampai). Setelah angka labanya muncul, klik tombol abu-abu berlogo PDF merah bertuliskan <strong>Ekspor PDF</strong> di pojok kanan atas tabel. Laporan final akan terunduh.</li>
            <li><strong>Laporan Riwayat Struk (Transaksi):</strong> Buka menu <em>Laporan > Transaksi Penjualan</em>. Di sini Anda bisa melihat daftar struk 1, struk 2, dst. Cari tombol Ekspor PDF di atas tabel untuk men-download log seluruh nota penjualan Anda bulan itu.</li>
            <li><strong>Laporan Arus Kas (Uang Masuk/Keluar):</strong> Buka menu <em>Laporan > Arus Kas</em>. Tekan tombol Ekspor PDF. Laporan ini bagaikan mutasi rekening Bank Anda, isinya runtutan tanggal kapan beli gas, kapan uang parkir masuk, dsb.</li>
            <li><strong>Laporan Nilai Gudang (Inventori):</strong> Buka halaman <em>Inventori</em>. Tombol Ekspor PDF di sini berfungsi untuk "Opname Stok". Sistem akan mencetak daftar barang, sisa stok, dan mengkalikannya dengan HPP untuk memberitahu Anda, "Oh, nilai seluruh harta karun barang dagangan saya di gudang saat ini adalah 25 Juta Rupiah".</li>
            <li><strong>Slip Gaji & Bagi Hasil:</strong> Seperti yang dijelaskan di bab sebelumnya, Slip Gaji Karyawan didownload dengan mengeklik tombol <em>Download Slip</em> di menu Karyawan. Sedangkan Laporan Investor didownload di menu Bagi Hasil. Keduanya juga berwujud file PDF siap *print*.</li>
        </ul>',
        // Baru Ditambahkan (Landing & KB)
        'os_title' => 'Open Source & Gratis',
        'os_desc' => 'Proyek SIMAJURAZ bersifat 100% Open Source dan dapat Anda unduh serta modifikasi secara gratis. Kami mengundang siapa saja untuk berkontribusi!',
        'os_c1_title' => 'Unduh Source Code',
        'os_c1_desc' => 'Dapatkan *source code* terbaru, laporkan bug, atau pelajari cara melakukan instalasi aplikasi secara mandiri.',
        'os_c1_btn' => 'Pusat Unduhan',
        'os_cloud_title' => 'Gunakan Gratis Melalui Website Kami',
        'os_cloud_desc' => 'Tidak ingin repot install server? Gunakan sistem kami secara gratis. Data aman, online 24/7, dan siap pakai.',
        'os_cloud_btn' => 'Daftar Sekarang',
        'os_c2_title' => 'Dukung Pengembangan',
        'os_c2_desc' => 'Aplikasi ini gratis! Namun, dukungan Anda sangat berarti agar kami terus bisa mengembangkan fitur-fitur baru secara berkelanjutan.',
        'os_c3_title' => 'Hubungi Kami',
        'os_c3_desc' => 'Butuh bantuan instalasi, kustomisasi fitur, atau sekadar menyapa tim developer? Silakan hubungi kontak resmi RAZ Creative Studio.',
        'os_c3_btn' => 'Kontak Website RAZ',
        'banner_cloud_title' => 'Gunakan Gratis Melalui Website Kami',
        'banner_cloud_desc' => 'Tidak ingin repot sewa server, pusing memikirkan domain, atau mengatur database? Gunakan sistem <strong>Cloud SIMAJURAZ</strong> publik kami secara gratis 100%. Data Anda terenkripsi aman, sistem selalu online 24/7, dan siap pakai.',
        'banner_cloud_btn' => 'Daftar Akun Toko Sekarang',
        'footer_ext_about' => 'RAZ Creative Studio adalah agensi IT & kreatif digital yang mengintegrasikan teknologi, desain, dan inovasi untuk menghasilkan solusi digital bernilai komersial.',
        'footer_ext_links' => 'Quick Links',
        'footer_ext_services' => 'Layanan',
        'footer_ext_tools' => 'Tools Online',
        'footer_ext_copy' => 'SIMAJURAZ by RAZ Creative Studio. Membantu UMKM Indonesia Go Digital.',
        
        'kb_about_os_title' => 'Tentang SIMAJURAZ Open Source',
        'kb_about_os_p1' => '<strong>SIMAJURAZ</strong> (Sistem Manajemen Jualan Oleh RAZ Creative Studio) adalah platform <i>Point of Sale</i> (POS) dan perangkat lunak bisnis mini berbasis web. Sistem ini secara khusus dirancang untuk memberdayakan UMKM di seluruh Indonesia agar bisa melakukan digitalisasi, manajemen inventori, dan pemantauan arus kas secara mandiri.',
        'kb_about_os_p2' => 'Proyek ini dikerjakan, disponsori, dan dikelola langsung oleh tim dari <strong><a href="https://raz.my.id" target="_blank" style="color:var(--l-primary-light); text-decoration:none;">RAZ Creative Studio</a></strong>. Kami merilis keseluruhan basis kode (<i>source code</i>) aplikasi ini ke publik dengan lisensi <strong>Open Source (MIT)</strong>. Ini berarti Anda bebas mengunduh, mempelajari, memodifikasi, dan menggunakan aplikasi ini untuk keperluan pribadi maupun bisnis (komersial) secara 100% gratis.',
        'kb_tech_title' => 'Teknologi & Struktur Proyek',
        'kb_tech_p1' => 'Kami membangun SIMAJURAZ dengan <i>stack</i> teknologi yang modern namun sangat ringan, memastikan kompatibilitas yang tinggi agar bisa berjalan lancar di hampir semua server hosting standar maupun komputer kasir spesifikasi rendah.',
        'kb_tech_l1' => '<strong>Backend:</strong> PHP 8.x Native (Sangat cepat tanpa <i>overhead</i> framework yang berat).',
        'kb_tech_l2' => '<strong>Database:</strong> SQLite (Mode offline/portable) atau MySQL/MariaDB (Untuk skala besar & sinkronisasi multi-kasir di Cloud).',
        'kb_tech_l3' => '<strong>Frontend:</strong> Vanilla JS & Vanilla CSS (Menggunakan desain <i>Glassmorphism</i> modern ala RAZ v3.0).',
        'kb_tech_l4' => '<strong>Ikon:</strong> Phosphor Icons.',
        'kb_tech_l5' => '<strong>Dokumen:</strong> DomPDF/TCPDF (Untuk ekspor laporan).',
        'kb_tech_struct' => 'Struktur Folder Utama (Project Structure)',
        'kb_install_title' => 'Cara Instalasi & Hosting (Deploy)',
        'kb_install_p1' => 'SIMAJURAZ sangat fleksibel. Aplikasi ini dirancang agar mudah diinstal, baik di komputer kasir lokal (untuk penggunaan toko tanpa internet) maupun di Cloud Hosting komersial (untuk diakses dari mana saja).',
        'kb_install_a' => 'A. Instalasi Lokal / Komputer Kasir (Offline)',
        'kb_install_a1' => 'Unduh dan install web server lokal seperti <strong>XAMPP</strong> atau <strong>Laragon</strong> di komputer/laptop kasir Anda.',
        'kb_install_a2' => 'Pastikan modul <strong>Apache</strong> dan ekstensi <strong>PHP SQLite3</strong> sudah aktif (secara default biasanya sudah aktif).',
        'kb_install_a3' => 'Ekstrak folder hasil unduhan SIMAJURAZ ke dalam folder <code>htdocs</code> (XAMPP) atau <code>www</code> (Laragon).',
        'kb_install_a4' => 'Buka browser (Google Chrome/Edge) dan ketikkan alamat <code>http://localhost/SIMAJURAZ/</code>',
        'kb_install_a5' => 'Sistem akan mendeteksi instalasi baru dan mengarahkan Anda ke <strong>Halaman Instalasi (RAZinstall.php)</strong>.',
        'kb_install_a6' => 'Pilih mode database <strong>Internal (SQLite)</strong> untuk kemudahan offline tanpa ribet, lalu buat akun Super Admin untuk masuk ke sistem.',
        'kb_install_b' => 'B. Cara Hosting Online (CPanel / Plesk)',
        'kb_install_b1' => 'Login ke panel hosting Anda (misal: CPanel), lalu buka <strong>File Manager</strong>.',
        'kb_install_b2' => 'Masuk ke direktori <code>public_html</code> (atau direktori subdomain target Anda).',
        'kb_install_b3' => 'Upload file ZIP SIMAJURAZ dan ekstrak di dalam direktori tersebut.',
        'kb_install_b4' => 'Buat database baru melalui menu <strong>MySQL Databases</strong> (Catat baik-baik <i>Database Name</i>, <i>User</i>, dan <i>Password</i>-nya).',
        'kb_install_b5' => 'Buka alamat domain Anda di browser (misal: <code>https://kasir.tokosaya.com</code>).',
        'kb_install_b6' => 'Pada layar Instalasi, pilih tipe koneksi <strong>MySQL/MariaDB Eksternal</strong> dan masukkan kredensial database yang telah Anda catat tadi. Sistem SIMAJURAZ akan melakukan instalasi tabel dan skema secara otomatis dalam hitungan detik.',
        'kb_cloud_title' => 'Gunakan Gratis Melalui Website Kami',
        'kb_cloud_p1' => 'Selain mengunduh dan melakukan instalasi mandiri, Anda juga bisa langsung menggunakan aplikasi SIMAJURAZ <strong>secara gratis</strong> di website kami tanpa perlu repot mengurus server, hosting, atau database! Sistem Cloud publik kami selalu online 24/7 dan siap digunakan.',
        'kb_cloud_info' => 'Untuk mulai menggunakan versi Cloud Gratis ini, silakan langsung ikuti panduan <strong>1. Mendaftar & Pengaturan Toko</strong> di bawah.',
        'kb_raz_title' => 'Layanan Profesional RAZ Creative Studio',
        'kb_raz_p1' => 'Tidak ingin repot dengan teknis instalasi? Membutuhkan modifikasi sistem untuk menyesuaikan SIMAJURAZ dengan SOP bisnis perusahaan Anda? Tim pengembang asli kami siap memberikan dukungan purna jual berkualitas enterprise.',
        'kb_raz_s1' => 'Cloud Hosting & Instalasi',
        'kb_raz_s1_d' => 'Terima beres. Kami belikan nama domain toko Anda, siapkan VPS stabil, dan instalkan aplikasi SIMAJURAZ sampai siap Anda gunakan.',
        'kb_raz_s2' => 'Kustomisasi Modul Tambahan',
        'kb_raz_s2_d' => 'Ingin notifikasi otomatis via WhatsApp? Sistem poin member? Atau integrasi printer termal custom? Kami bisa mengembangkannya untuk Anda.',
        'kb_raz_s3' => 'Maintenance Eksklusif',
        'kb_raz_s3_d' => 'Dukungan perbaikan bug prioritas tinggi, konsultasi bisnis, dan *backup* database otomatis setiap hari demi keamanan data pelanggan.',
        'kb_raz_btn' => 'Pesan Jasa Instalasi Sekarang',
        'kb_nav_about' => 'Tentang SIMAJURAZ',
        'kb_nav_tech' => 'Teknologi & Struktur Proyek',
        'kb_nav_install' => 'Cara Instalasi & Hosting',
        'kb_nav_raz' => 'Jasa RAZ Studio',
        'kb_nav_cloud' => 'Gunakan Cloud Gratis',
        
        'dl_title' => 'Download Source Code Resmi',
        'dl_desc' => 'Dapatkan pembaruan sistem manajemen POS terbaru yang dikelola oleh tim developer RAZ Creative Studio langsung dari repositori GitHub publik kami.',
        'dl_btn' => 'Menuju GitHub SIMAJURAZ',
        'dl_raz_title' => 'Layanan Profesional RAZ',
        'dl_contact_title' => 'Hubungi Kami Langsung',
        'dl_form_title' => 'Tulis Pesan Anda',
        'dl_form_desc' => 'Pesan ini akan otomatis dikirimkan langsung ke WhatsApp tim kami.',
        'dl_form_name' => 'Nama Anda',
        'dl_form_name_ph' => 'Masukkan nama lengkap',
        'dl_form_subject' => 'Subjek Pesan',
        'dl_form_msg' => 'Detail Pesan',
        'dl_form_msg_ph' => 'Jelaskan kebutuhan bisnis atau pertanyaan Anda di sini secara detail...',
        'dl_form_btn' => 'Kirim Pesan Sekarang',
        'dl_wa_1' => 'WhatsApp Pribadi',
        'dl_wa_1_desc' => 'Respon paling cepat untuk diskusi ringan dan janji temu.',
        'dl_email' => 'Email Resmi',
        'dl_email_desc' => 'Kirimkan detail proposal penawaran atau kerjasama bisnis.',
        'dl_web' => 'Kunjungi Website',
        'dl_web_desc' => 'Lihat profil, layanan lain, serta portofolio agensi kami.',
        'dl_opt_1' => 'Tanya Jasa Instalasi / Hosting',
        'dl_opt_2' => 'Kustomisasi Fitur SIMAJURAZ',
        'dl_opt_3' => 'Kerjasama Bisnis & Layanan Lain',
        'dl_opt_4' => 'Lainnya...',
    ],
    'en' => [
        // Navbar
        'nav_features' => 'Key Features',
        'nav_tech' => 'Technology',
        'nav_kb' => 'Knowledge Base',
        'nav_login' => 'Sign In / Login',
        
        // Hero
        'hero_badge' => '100% FREE FOREVER',
        'hero_title_1' => 'Manage Your Business',
        'hero_title_2' => 'Easier With',
        'hero_desc' => 'Point of Sale (POS) application, inventory management, automatic COGS calculation, and profit/loss reporting in one elegant and responsive platform. Perfect for SMEs, retail stores, and all business types.',
        'hero_btn_start' => 'Start Now For Free',
        'hero_btn_learn' => 'Explore Features',
        
        // Tech
        'tech_title' => 'Powered By Modern Web Technologies',
        'tech_desc' => 'SIMAJURAZ is built using an ultra-lightweight yet powerful architecture, ensuring the application runs blazingly fast across devices without requiring high-end servers.',
        'tech_php' => 'Native PHP 8.x (Extremely Fast & Efficient)',
        'tech_db' => 'SQLite / MySQL (Multi-Tenant Storage)',
        'tech_css' => 'Vanilla CSS3 (Glassmorphism Aesthetics)',
        'tech_js' => 'Vanilla JavaScript (Real-time Interactions)',
        
        // Features Detailed (Grid)
        'feat_title' => 'Enterprise-Grade Features, Zero Hidden Costs',
        'feat_1_title' => 'Comprehensive Analytics Dashboard',
        'feat_1_desc' => 'Monitor your business performance instantly. View top-selling items, total daily sales, and cash flow movements through intuitive visual charts.',
        
        'feat_2_title' => 'Smart Inventory Management',
        'feat_2_desc' => 'Stock is automatically deducted (FIFO) upon every POS transaction. Get real-time alerts for low stock, base COGS tracking, and spoilage recording.',
        
        'feat_3_title' => 'Modern Point of Sale (POS)',
        'feat_3_desc' => 'A lightning-fast cashier interface supporting name search or barcode scanning, and instant change calculation, specifically designed to prevent customer queues.',
        
        'feat_4_title' => 'Financial & Cash Flow Reports',
        'feat_4_desc' => 'The system monitors all incoming money, store expenses, and drawer capital. Profit and Loss reports are calculated precisely in real-time.',
        
        'feat_5_title' => 'Payroll System & Payslips',
        'feat_5_desc' => 'Manage employee salaries which automatically deduct your store cash flow. Print professional PDF Payslips directly from the Employee dashboard.',
        
        'feat_6_title' => 'Premium COGS Calculator',
        'feat_6_desc' => 'Calculate your food recipes\' Cost of Goods Sold accurately. Our smart calculator will recommend the optimal Selling Price for your products.',

        'feat_7_title' => 'Professional PDF Exports',
        'feat_7_desc' => 'Export all transaction histories, ledgers, and inventory reports into professional A4 PDF documents with just one click.',

        'feat_8_title' => 'Multi-User Access Control',
        'feat_8_desc' => 'Supports multiple stores in one server (Multi-Tenant). Employees can only sell and cannot view critical COGS or Financial data.',
        
        // Extended Visual Showcase (Zig-Zag)
        'showcase_title' => 'Take a Closer Look',
        
        'sc_1_title' => 'Fast & Responsive POS',
        'sc_1_desc' => 'Sell without lag. Our POS interface is designed with large buttons and instant search features to serve customers in seconds.',
        
        'sc_2_title' => 'Business Analytics Dashboard',
        'sc_2_desc' => 'Know what sells best. Interactive revenue charts help you monitor daily, weekly, and monthly sales trends effortlessly.',
        
        'sc_3_title' => 'Transparent Finances',
        'sc_3_desc' => 'No more headaches calculating gross vs. net profit. The app records all operational expenses and incorporates them into your final net profit report.',

        'sc_4_title' => 'Comprehensive Knowledge Base',
        'sc_4_desc' => 'Don\'t worry if it\'s your first time using a POS system. We provide a complete Knowledge Base page to guide you from installation to mastering your store operations.',

        // Footer
        'footer_text' => 'SIMAJURAZ by RAZ Creative Studio. Empowering Indonesian SMEs to Go Digital.',

        // KB
        'kb_title' => 'Help Center & User Guide',
        'kb_desc' => 'Learn how to maximize the use of SIMAJURAZ for your business operations with this extremely detailed guide.',
        
        'kb_sidebar_1' => 'Registering & Store Settings',
        'kb_sidebar_2' => 'Inventory Management',
        'kb_sidebar_3' => 'POS Operations',
        'kb_sidebar_4' => 'Finance & Cash Flow',
        'kb_sidebar_5' => 'Employee Payroll',
        'kb_sidebar_6' => 'Premium COGS Calculator',
        'kb_sidebar_7' => 'Profit Sharing (Investors)',
        'kb_sidebar_8' => 'PDF Report Export',
        
        'kb_c1_title' => 'Registering & Store Settings',
        'kb_c1_desc' => '<p>Welcome to SIMAJURAZ! Before you start selling, let\'s set up your account and store profile.</p>
        <h4>A. How to Register a New Store Account</h4>
        <div class="kb-img-container"><img src="assets/images/ss_register.png" alt="Register" class="kb-img-showcase"></div>
        <ol>
            <li>Go to the <strong>Login</strong> page by clicking the "Masuk / Login" button in the top right corner of the main page.</li>
            <li>On the login form, look at the very bottom and click the text that says <strong>Daftar di sini (Register here)</strong>. The form will automatically change to a Registration form.</li>
            <li>Fill in your Full Name, Active Email, and create a strong Password.</li>
            <li>Click the <strong>Daftar (Register)</strong> button. You will be immediately redirected to the main Dashboard of your store. Congratulations, your store is officially established!</li>
        </ol>
        <h4>B. Store Profile & Invoice Settings</h4>
        <div class="kb-img-container"><img src="assets/images/ss_settings_top.png" alt="Settings" class="kb-img-showcase"></div>
        <ol>
            <li>Now that you are on the Dashboard, look at the left panel and click the <strong>Pengaturan (Settings)</strong> menu.</li>
            <li>In the <strong>Profil Toko (Store Profile)</strong> tab, you can enter your Store Name, Store Address, and Phone Number. This data will be printed on your customers\' receipts.</li>
            <li><strong>Upload Store Logo:</strong> Click the logo upload box. Choose an image (JPG/PNG). This logo will appear at the top of your receipt.</li>
            <li><strong>Invoice Prefix:</strong> Set the initial code for your receipts. For example, if you sell Coffee, type "KOP". Your receipt numbers will look like KOP-0001, KOP-0002, etc.</li>
            <li><strong>Header & Footer Notes:</strong> Fill in the text you want to display on the receipt. Example Footer: "Thank you for shopping at our store!".</li>
            <li>Click <strong>Simpan Perubahan (Save Changes)</strong> at the bottom. Done!</li>
        </ol>
        <h4>C. Changing Password & Adding Employees</h4>
        <div class="kb-img-container"><img src="assets/images/ss_users_add.png" alt="Add Employee" class="kb-img-showcase"></div>
        <ol>
            <li>If you want to change your password, go to the <em>Pengaturan > Akun Anda (Settings > Your Account)</em> tab and enter your new password.</li>
            <li><strong>Adding Employees:</strong> Do you have a cashier? Don\'t let them use your Owner account! Go to the <strong>Karyawan (Employees)</strong> menu on the left.</li>
            <li>Click the <strong>Tambah Karyawan (Add Employee)</strong> button.</li>
            <li>Enter the cashier\'s Name, Email, and Password. Select their Role as "Kasir (Cashier)".</li>
            <li>Now your cashier can log in using that email, and their access will be strictly limited (they cannot see financial reports or store settings).</li>
        </ol>',
        
        'kb_c2_title' => 'Inventory Management',
        'kb_c2_desc' => '<p>Inventory is the brain of your store. Make sure you enter item data as accurately as possible before making transactions.</p>
        <h4>A. Adding & Deleting Categories</h4>
        <ol>
            <li>Go to the <strong>Inventori (Inventory)</strong> menu.</li>
            <li>Before creating items, create item "Groups" first. Click the <strong>Tambah Kategori (Add Category)</strong> button.</li>
            <li>Type the category name (e.g., "Cold Drinks" or "Raw Materials"). Click Save. The category will appear as a clickable "Chip" button.</li>
            <li>To delete, simply click the (x) mark on the category chip. WARNING: Do not delete a category if there are still items inside it!</li>
        </ol>
        <h4>B. Adding Items in Detail</h4>
        <div class="kb-img-container"><img src="assets/images/ss_inventory_modal.png" alt="Inventory Modal" class="kb-img-showcase"></div>
        <ol>
            <li>Click the blue <strong>Tambah Barang Baru (Add New Item)</strong> button. A large form will appear.</li>
            <li><strong>Name & Category:</strong> Enter the item name (e.g., "Vanilla Latte"). Select the category you created earlier.</li>
            <li><strong>SKU / Barcode:</strong> This is the unique code of the item. If you use a barcode scanner, put your cursor in this box and scan the product\'s barcode. If it\'s homemade food, just make up a code (e.g., VL-01).</li>
            <li><strong>HPP (COGS) & Selling Price:</strong>
                <ul>
                    <li><em>HPP (Harga Pokok Penjualan / Cost of Goods Sold):</em> This is your CAPITAL price. How much does it cost you to make/buy this item? Let\'s say Rp 10,000.</li>
                    <li><em>Selling Price:</em> The price given to the customer. Let\'s say Rp 15,000.</li>
                    <li>The system will automatically calculate that your Gross Profit for this item is Rp 5,000.</li>
                </ul>
            </li>
            <li><strong>Stock & Minimum Limit:</strong> Enter the initial amount of stock you have. <em>Minimum Stock</em> is the limit where the system will give you a "Running out of stock" warning (e.g., set it to 5).</li>
            <li><strong>Product Photo:</strong> Upload an attractive photo so the Cashier display looks beautiful and premium.</li>
            <li>Click <strong>Simpan (Save)</strong>. The item is now ready to be sold!</li>
        </ol>',
        
        'kb_c3_title' => 'POS Operations',
        'kb_c3_desc' => '<p>The Cashier (Point of Sale) page is designed for super fast transactions. There shouldn\'t be any delayed queues here!</p>
        <h4>A. Opening Cashier Shift (Mandatory)</h4>
        <ol>
            <li>Go to the <strong>Kasir (POS)</strong> menu. If this is your first transaction today, a dark screen (*modal*) will block you.</li>
            <li>This is a security procedure. You must click <strong>Buka Shift (Open Shift)</strong>.</li>
            <li>In the form, type the <strong>Change Amount (Cash Drawer Starting Balance)</strong> currently in your physical cash drawer (e.g., you put Rp 100,000 in change).</li>
            <li>This initial capital is recorded so that when you close the shop later, the money in the drawer will exactly match the system (Sales + Initial Capital).</li>
        </ol>
        <h4>B. Adding Items to Cart</h4>
        <ol>
            <li><strong>Using Mouse:</strong> Simply click the product photos displayed. Each click will add 1 quantity to the right panel (Cart).</li>
            <li><strong>Using Barcode Scanner:</strong> Make sure the cursor is blinking in the <em>"Cari Nama / Scan Barcode"</em> search box. Scan the item! It will instantly pop into the Cart.</li>
            <li>To reduce or increase quantities, click the (-) or (+) buttons next to the item name in the Cart.</li>
        </ol>
        <h4>C. Making Payments</h4>
        <div class="kb-img-container"><img src="assets/images/ss_pos_payment.png" alt="Payment Modal" class="kb-img-showcase"></div>
        <ol>
            <li>Once the customer finishes ordering, click the large blue <strong>Bayar (Pay)</strong> button at the bottom right.</li>
            <li>A payment window will appear showing the Total Bill.</li>
            <li><strong>Payment Method:</strong> Choose whether the customer is paying with Cash, Transfer, or QRIS.</li>
            <li><strong>Cash Input:</strong> If Cash, type the money given by the customer (e.g., bill is 15rb, given 50rb). The system will automatically display the <strong>Change Amount</strong> (35rb) in red text so you don\'t have to calculate manually!</li>
            <li>Click <strong>Proses Pembayaran (Process Payment)</strong>.</li>
        </ol>
        <h4>D. Printing & Sharing Receipts</h4>
        <div class="kb-img-container"><img src="assets/images/ss_pos_receipt.png" alt="Receipt Modal" class="kb-img-showcase"></div>
        <ol>
            <li>After successful payment, a digital receipt will appear perfectly on the screen.</li>
            <li>To print using a Bluetooth/Thermal Printer, click the <strong>Print Struk</strong> button.</li>
            <li>To send it to a customer\'s WhatsApp (paperless), simply screenshot the screen, or use the PDF Export feature in the Transactions report menu later.</li>
            <li>Click <strong>Tutup (Close)</strong> to serve the next customer.</li>
        </ol>',
        
        'kb_c4_title' => 'Finance & Cash Flow',
        'kb_c4_desc' => '<p>The Finance Module monitors the lifeblood of your business (money coming in and going out). This Cash Flow interacts directly with the final Profit and Loss.</p>
        <h4>A. Reading the Profit and Loss Report</h4>
        <ol>
            <li>Go to the <strong>Laporan > Laba Rugi (Reports > Profit and Loss)</strong> menu.</li>
            <li>Here, you no longer need to calculate manually. Our smart formula works 24 hours: <code>(All Sales Revenue)</code> MINUS <code>(Total COGS / Capital of those Items)</code> MINUS <code>(All Extra Store Expenses)</code>.</li>
            <li>The final result will be displayed large in the <strong>Laba Bersih (Net Profit)</strong> box. This profit shows your real earnings after deducting operational expenses.</li>
        </ol>
        <h4>B. Inputting Other Income (Non-Sales)</h4>
        <div class="kb-img-container"><img src="assets/images/ss_finance_in.png" alt="Income Modal" class="kb-img-showcase"></div>
        <ol>
            <li>What if someone tips the cashier or there is income outside of selling items?</li>
            <li>Go to the <strong>Keuangan (Finance)</strong> menu (Arus Kas / Cash Flow tab).</li>
            <li>Click <strong>Pemasukan Lain (Other Income)</strong>.</li>
            <li>Enter the Nominal, Date, and Description (e.g., "Parking fee revenue"). This money will add to your total store balance.</li>
        </ol>
        <h4>C. Inputting Store Expenses</h4>
        <div class="kb-img-container"><img src="assets/images/ss_finance_out.png" alt="Expense Modal" class="kb-img-showcase"></div>
        <ol>
            <li>Did you run out of LPG gas? Bought a broom? Paid the electricity bill?</li>
            <li>On the same page, click <strong>Catat Pengeluaran (Record Expense)</strong>.</li>
            <li>Enter the Nominal, Date, and Description (e.g., "Bought 2 LPG Gas cylinders").</li>
            <li>This expense is CRITICAL. It will directly deduct your Profit in the Profit and Loss report so you don\'t think the business is profitable when it\'s actually losing money due to wasteful expenses!</li>
        </ol>',

        'kb_c5_title' => 'Employee Payroll',
        'kb_c5_desc' => '<p>SIMAJURAZ has a smart Payroll module. The salaries you pay to employees will be directly recorded as store expenses without needing to record them twice in the books!</p>
        <h4>A. Processing Payroll</h4>
        <div class="kb-img-container"><img src="assets/images/ss_payroll_modal.png" alt="Payroll Modal" class="kb-img-showcase"></div>
        <ol>
            <li>Go to the <strong>Karyawan (Employees)</strong> menu on the left panel.</li>
            <li>Ignore the Employee List tab, click the tab next to it: <strong>Penggajian (Payroll)</strong>.</li>
            <li>Click the blue <strong>Buat Penggajian (Create Payroll)</strong> button.</li>
            <li>A form will appear. Select the <strong>Employee Name</strong> you want to pay.</li>
            <li>Enter their <strong>Basic Salary</strong> (e.g., Rp 2,000,000).</li>
            <li>Does the employee have a debt/cash advance? Enter it in the <strong>Deductions</strong> box. Did they perform well? Enter it in the <strong>Bonus/Allowances</strong> box.</li>
            <li>The system will automatically calculate the <em>Net Salary</em>.</li>
            <li>Click Save. BAM! This salary is instantly injected into your Store Expenses and automatically deducts the Net Profit of the month. Very integrated!</li>
        </ol>
        <h4>B. Printing Employee Payslips</h4>
        <ol>
            <li>After saving, the salary history will appear in the table.</li>
            <li>At the far right of the table, click the blue <strong>Download Slip</strong> button.</li>
            <li>The browser will instantly download a professional PDF file showing the exact details of deductions and bonuses, complete with your store logo at the top!</li>
        </ol>',
        
        'kb_c6_title' => 'Premium COGS Calculator',
        'kb_c6_desc' => '<p>The secret feature of F&B (Cafe/Resto) entrepreneurs. This calculator will break down the original Capital Price (*Food Cost*) of a recipe in detail.</p>
        <h4>How to Simulate Recipe Prices:</h4>
        <div class="kb-img-container"><img src="assets/images/ss_hpp.png" alt="HPP Calculator" class="kb-img-showcase"></div>
        <ol>
            <li>Go to the <strong>Kalkulator HPP (COGS Calculator)</strong> menu.</li>
            <li>Click the large <strong>Buat Perhitungan Baru (Create New Calculation)</strong> button. You will face a long form divided into 4 Stages. Let\'s fill it from top to bottom:</li>
            <li><strong>Stage 1 (Basic Info):</strong> Name this recipe. Example: "Medium Cup Palm Sugar Milk Coffee". Also enter the target number of portions if this recipe is made in bulk (usually just 1 portion).</li>
            <li><strong>Stage 2 (Input Main Raw Materials):</strong> This is the fun part. Click the <strong>+ Tambah Bahan (+ Add Ingredient)</strong> button.
                <ul>
                    <li>Ingredient Name: Type "Arabica Coffee Beans".</li>
                    <li>Raw Purchase Price: How much did you buy 1 pack for? Example: Rp 100,000.</li>
                    <li>Total Raw Weight: How much is inside 1 pack? Example: 1000 Grams (1 Kg).</li>
                    <li>Amount for 1 Portion: To make 1 cup of coffee, how many grams are needed? Example: 15 Grams.</li>
                    <li><em>MAGIC!</em> The calculator will break down the Rp 100k/kg price earlier and conclude that the coffee capital for that 1 cup is only <strong>Rp 1,500</strong>! Do the same for UHT Milk and Palm Sugar.</li>
                </ul>
            </li>
            <li><strong>Stage 3 (Input Packaging & Extra Materials):</strong>
                <ul>
                    <li>Click the add button in the packaging materials form. Enter items like "Plastic Cup" (e.g., Bought 50pcs for 20k, meaning 1pcs capital is Rp 400). Enter straws, plastic bags, etc.</li>
                    <li>Don\'t forget LPG gas and barista electricity costs per portion so there are no hidden leaks!</li>
                </ul>
            </li>
            <li><strong>Stage 4 (Calculate Target Margin):</strong>
                <ul>
                    <li>After the system calculates the total overall capital from Stages 2 & 3 (e.g., Total Recipe COGS = Rp 5,000), it\'s time to determine the profit!</li>
                    <li>In the Target Margin column, type what percentage of profit you want. Example: 50%.</li>
                    <li>The system will conclude: To get a 50% profit, you <strong>MUST sell this product for at least Rp 10,000</strong>! This is the <em>Recommended Selling Price</em> function.</li>
                </ul>
            </li>
            <li>Once satisfied with the results, you can exit. If you sell this product, don\'t forget to register it in the **Inventori** menu and enter the value Rp 5000 in the HPP column, and Rp 10000 in the Selling Price column!</li>
        </ol>',

        'kb_c7_title' => 'Profit Sharing (Investors)',
        'kb_c7_desc' => '<p>Opening a store with joint capital from friends/relatives? Don\'t fight over profit percentages! Use this feature so the calculations are *fair* and transparent.</p>
        <h4>A. Registering Investor Names</h4>
        <div class="kb-img-container"><img src="assets/images/ss_profitshare_tab.png" alt="Profit Share" class="kb-img-showcase"></div>
        <ol>
            <li>Go to the <strong>Keuangan (Finance)</strong> menu. Ignore the Cash Flow tab, click the tab next to it: <strong>Bagi Hasil (Profit Share)</strong>.</li>
            <li>Under the Profit Distribution text, click the add button (person+ icon). A modal window will appear.</li>
            <li><strong>Investor Form:</strong>
                <ul>
                    <li><strong>Investor Name:</strong> Enter the name of your business partner (e.g., Mr. Budi).</li>
                    <li><strong>Portion / Percentage:</strong> What is the agreed net share? Type the number only (e.g., 30 for 30%). Ensure the total portion of all investors does not exceed 100%.</li>
                </ul>
            </li>
            <li>Click Save. Mr. Budi\'s name now appears in the investor table with a 30% percentage.</li>
        </ol>
        <h4>B. Calculating Monthly Profit Disbursement</h4>
        <ol>
            <li>Every time approaching the beginning of the book-closing month (date 30/31), return to this page.</li>
            <li>Look at the <em>Riwayat Laporan Bagi Hasil (Profit Share Report History)</em> panel. Click the blue <strong>Generate Laporan Bagi Hasil</strong> button.</li>
            <li>A calendar window will appear. Select the <strong>Date Range</strong> (Select date 1 to date 30/31 in that month).</li>
            <li>Click <strong>Proses & Kalkulasi (Process & Calculate)</strong>.</li>
            <li><strong>What happens?</strong> The system will run to check your total Net Profit in that month (Total Sales - COGS - Buy Gas - Employee Salaries). Suppose the final net profit is Rp 10,000,000.</li>
            <li>The system will automatically split that 10 Million! The system records: Mr. Budi\'s Share (30%) is Rp 3,000,000, Owner\'s Share (Remaining 70%) is Rp 7,000,000.</li>
            <li>This distribution history will be immortalized in the table. You can click <strong>Cetak PDF (Print PDF)</strong> to hand over to Mr. Budi as proof of transparency. If the money has been transferred, click the <strong>Tandai Sudah Dibayar (Mark as Paid)</strong> button so the report status turns green (Paid).</li>
        </ol>',
        
        'kb_c8_title' => 'PDF Report Export',
        'kb_c8_desc' => '<p>Kecanggihan sebuah sistem terletak pada kemampuan mencetak laporannya. Di SIMAJURAZ, hampir setiap halaman punya tombol <strong>Ekspor PDF</strong> sekali klik yang akan men- *download* dokumen ukuran A4 yang sangat rapi untuk diserahkan ke atasan.</p>
        <h4>Cara Mencari Tombol Ekspor di Setiap Fitur:</h4>
        <div class="kb-img-container"><img src="assets/images/ss_reports.png" alt="Reports" class="kb-img-showcase"></div>
        <ul>
            <li><strong>Laba Rugi Lengkap:</strong> Buka menu <em>Laporan > Laba Rugi</em>. Atur filter tanggal (Dari - Sampai). Setelah angka labanya muncul, klik tombol abu-abu berlogo PDF merah bertuliskan <strong>Ekspor PDF</strong> di pojok kanan atas tabel. Laporan final akan terunduh.</li>
            <li><strong>Laporan Riwayat Struk (Transaksi):</strong> Buka menu <em>Laporan > Transaksi Penjualan</em>. Di sini Anda bisa melihat daftar struk 1, struk 2, dst. Cari tombol Ekspor PDF di atas tabel untuk men-download log seluruh nota penjualan Anda bulan itu.</li>
            <li><strong>Laporan Arus Kas (Uang Masuk/Keluar):</strong> Buka menu <em>Laporan > Arus Kas</em>. Tekan tombol Ekspor PDF. Laporan ini bagaikan mutasi rekening Bank Anda, isinya runtutan tanggal kapan beli gas, kapan uang parkir masuk, dsb.</li>
            <li><strong>Laporan Nilai Gudang (Inventori):</strong> Buka halaman <em>Inventori</em>. Tombol Ekspor PDF di sini berfungsi untuk "Opname Stok". Sistem akan mencetak daftar barang, sisa stok, dan mengkalikannya dengan HPP untuk memberitahu Anda, "Oh, nilai seluruh harta karun barang dagangan saya di gudang saat ini adalah 25 Juta Rupiah".</li>
            <li><strong>Slip Gaji & Bagi Hasil:</strong> Seperti yang dijelaskan di bab sebelumnya, Slip Gaji Karyawan didownload dengan mengeklik tombol <em>Download Slip</em> di menu Karyawan. Sedangkan Laporan Investor didownload di menu Bagi Hasil. Keduanya juga berwujud file PDF siap *print*.</li>
        </ul>',
        // Newly added (Landing & KB)
        'os_title' => 'Open Source & Free',
        'os_desc' => 'The SIMAJURAZ project is 100% Open Source and you can download and modify it for free. We invite everyone to contribute!',
        'os_c1_title' => 'Download Source Code',
        'os_c1_desc' => 'Get the latest source code, report bugs, or learn how to manually install the application.',
        'os_c1_btn' => 'Download Center',
        'os_cloud_title' => 'Use For Free Via Our Website',
        'os_cloud_desc' => 'Don\'t want the hassle of renting a server? Use our system for free. Your data is securely encrypted, online 24/7, and ready to use.',
        'os_cloud_btn' => 'Register Now',
        'os_c2_title' => 'Support Development',
        'os_c2_desc' => 'This app is free! However, your support means a lot to help us continuously develop new features.',
        'os_c3_title' => 'Contact Us',
        'os_c3_desc' => 'Need installation help, custom features, or just want to say hi to the dev team? Contact RAZ Creative Studio\'s official channels.',
        'os_c3_btn' => 'RAZ Website Contact',
        'banner_cloud_title' => 'Use For Free Via Our Website',
        'banner_cloud_desc' => 'Don\'t want the hassle of renting a server, buying a domain, or setting up a database? Use our public <strong>SIMAJURAZ Cloud</strong> system 100% for free. Your data is securely encrypted, online 24/7, and ready to use.',
        'banner_cloud_btn' => 'Register Store Account Now',
        'footer_ext_about' => 'RAZ Creative Studio is an IT & digital creative agency integrating technology, design, and innovation to deliver commercially valuable digital solutions.',
        'footer_ext_links' => 'Quick Links',
        'footer_ext_services' => 'Services',
        'footer_ext_tools' => 'Online Tools',
        'footer_ext_copy' => 'SIMAJURAZ by RAZ Creative Studio. Helping Indonesian SMEs Go Digital.',
        
        'kb_about_os_title' => 'About SIMAJURAZ Open Source',
        'kb_about_os_p1' => '<strong>SIMAJURAZ</strong> is a web-based mini Point of Sale (POS) and business software. This system is specifically designed to empower SMEs across Indonesia to digitalize, manage inventory, and monitor cash flow independently.',
        'kb_about_os_p2' => 'This project is developed, sponsored, and managed by the team at <strong><a href="https://raz.my.id" target="_blank" style="color:var(--l-primary-light); text-decoration:none;">RAZ Creative Studio</a></strong>. We release the entire source code to the public under the <strong>Open Source (MIT)</strong> license. This means you are free to download, study, modify, and use this application for personal or commercial purposes 100% free of charge.',
        'kb_tech_title' => 'Technology & Project Structure',
        'kb_tech_p1' => 'We built SIMAJURAZ with a modern yet lightweight technology stack, ensuring high compatibility to run smoothly on almost any standard hosting server or low-spec cashier computer.',
        'kb_tech_l1' => '<strong>Backend:</strong> Native PHP 8.x (Extremely fast without heavy framework overhead).',
        'kb_tech_l2' => '<strong>Database:</strong> SQLite (Offline/portable mode) or MySQL/MariaDB (For large scale & cloud multi-cashier sync).',
        'kb_tech_l3' => '<strong>Frontend:</strong> Vanilla JS & Vanilla CSS (Using modern <i>Glassmorphism</i> design ala RAZ v3.0).',
        'kb_tech_l4' => '<strong>Icons:</strong> Phosphor Icons.',
        'kb_tech_l5' => '<strong>Documents:</strong> DomPDF/TCPDF (For report exports).',
        'kb_tech_struct' => 'Main Folder Structure (Project Structure)',
        'kb_install_title' => 'Installation & Hosting Guide',
        'kb_install_p1' => 'SIMAJURAZ is highly flexible. The application is designed to be easily installed, whether on a local cashier computer (for offline stores) or on commercial Cloud Hosting (to be accessed from anywhere).',
        'kb_install_a' => 'A. Local / Cashier Computer Installation (Offline)',
        'kb_install_a1' => 'Download and install a local web server like <strong>XAMPP</strong> or <strong>Laragon</strong> on your computer.',
        'kb_install_a2' => 'Make sure the <strong>Apache</strong> module and <strong>PHP SQLite3</strong> extension are active (usually active by default).',
        'kb_install_a3' => 'Extract the downloaded SIMAJURAZ folder into the <code>htdocs</code> (XAMPP) or <code>www</code> (Laragon) directory.',
        'kb_install_a4' => 'Open a browser and type <code>http://localhost/SIMAJURAZ/</code>',
        'kb_install_a5' => 'The system will detect a new installation and redirect you to the <strong>Installation Page (RAZinstall.php)</strong>.',
        'kb_install_a6' => 'Select <strong>Internal (SQLite)</strong> database mode for easy offline use, then create your first Super Admin account.',
        'kb_install_b' => 'B. Online Hosting Guide (CPanel / Plesk)',
        'kb_install_b1' => 'Login to your hosting panel (e.g. CPanel), then open <strong>File Manager</strong>.',
        'kb_install_b2' => 'Go to the <code>public_html</code> directory (or your target subdomain directory).',
        'kb_install_b3' => 'Upload the SIMAJURAZ ZIP file and extract it there.',
        'kb_install_b4' => 'Create a new database via <strong>MySQL Databases</strong> (Note down the <i>Database Name</i>, <i>User</i>, and <i>Password</i>).',
        'kb_install_b5' => 'Open your domain in the browser (e.g. <code>https://cashier.mystore.com</code>).',
        'kb_install_b6' => 'On the Installation screen, choose <strong>External MySQL/MariaDB</strong> connection and enter the database credentials. The system will automatically migrate the tables and schema in seconds.',
        'kb_cloud_title' => 'Use For Free Via Our Website',
        'kb_cloud_p1' => 'Besides downloading and installing it yourself, you can also directly use the SIMAJURAZ application <strong>for free</strong> on our website without the hassle of setting up servers, hosting, or databases! Our public Cloud system is online 24/7 and ready to use.',
        'kb_cloud_info' => 'To start using this free Cloud version, please follow the <strong>1. Registering & Store Settings</strong> guide below.',
        'kb_raz_title' => 'RAZ Creative Studio Professional Services',
        'kb_raz_p1' => 'Don\'t want to deal with technical installations? Need system modifications to match your company\'s business SOPs? Our original development team is ready to provide enterprise-quality after-sales support.',
        'kb_raz_s1' => 'Cloud Hosting & Setup',
        'kb_raz_s1_d' => 'Leave it to us. We will purchase your domain name, set up a stable VPS, and install SIMAJURAZ until it\'s ready for use.',
        'kb_raz_s2' => 'Custom Module Development',
        'kb_raz_s2_d' => 'Want automated WhatsApp notifications? A member points system? Or custom thermal printer integration? We can build it for you.',
        'kb_raz_s3' => 'Exclusive Maintenance',
        'kb_raz_s3_d' => 'High priority bug fixes, business consulting, and daily automatic database backups to ensure customer data security.',
        'kb_raz_btn' => 'Order Installation Service Now',
        'kb_nav_about' => 'About SIMAJURAZ',
        'kb_nav_tech' => 'Tech & Project Structure',
        'kb_nav_install' => 'Installation & Hosting',
        'kb_nav_raz' => 'RAZ Studio Services',
        'kb_nav_cloud' => 'Use Free Cloud',
        
        'dl_title' => 'Official Source Code Download',
        'dl_desc' => 'Get the latest POS management system updates maintained by the RAZ Creative Studio development team directly from our public GitHub repository.',
        'dl_btn' => 'Go to SIMAJURAZ GitHub',
        'dl_raz_title' => 'RAZ Professional Services',
        'dl_contact_title' => 'Contact Us Directly',
        'dl_form_title' => 'Write Your Message',
        'dl_form_desc' => 'This message will automatically be sent to our team\'s WhatsApp.',
        'dl_form_name' => 'Your Name',
        'dl_form_name_ph' => 'Enter your full name',
        'dl_form_subject' => 'Message Subject',
        'dl_form_msg' => 'Message Details',
        'dl_form_msg_ph' => 'Explain your business needs or questions here in detail...',
        'dl_form_btn' => 'Send Message Now',
        'dl_wa_1' => 'Personal WhatsApp',
        'dl_wa_1_desc' => 'Fastest response for quick discussions and appointments.',
        'dl_email' => 'Official Email',
        'dl_email_desc' => 'Send detailed proposals or business partnerships.',
        'dl_web' => 'Visit Website',
        'dl_web_desc' => 'View our agency profile, other services, and portfolio.',
        'dl_opt_1' => 'Installation / Hosting Inquiry',
        'dl_opt_2' => 'SIMAJURAZ Feature Customization',
        'dl_opt_3' => 'Business Partnership & Other Services',
        'dl_opt_4' => 'Others...',
    ]
];

// Helper function
function t($key) {
    global $LANG_DICT, $current_lang;
    return $LANG_DICT[$current_lang][$key] ?? $key;
}
?>
