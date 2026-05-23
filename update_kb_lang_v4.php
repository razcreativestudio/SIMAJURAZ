<?php
$file = 'C:\laragon\www\SIMAJURAZ\includes\RAZlang.php';
$lines = file($file);

$id_kb = "        // KB
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
        <ol>
            <li>Buka halaman <strong>Login</strong> dengan mengeklik tombol \"Masuk / Login\" di pojok kanan atas halaman utama.</li>
            <li>Pada form login, perhatikan bagian paling bawah dan klik teks bertuliskan <strong>Daftar di sini</strong>. Form akan otomatis berubah menjadi form Pendaftaran.</li>
            <li><strong>Pengisian Form Pendaftaran:</strong>
                <ul>
                    <li><strong>Nama Toko:</strong> Ketikkan nama usaha Anda (misalnya \"Kedai Kopi Senja\"). Nama ini akan menjadi identitas utama Anda di sistem.</li>
                    <li><strong>Username:</strong> Buat sebuah nama pengguna tanpa spasi (misalnya <code>kopisenja</code>). Ingat username ini karena akan selalu digunakan setiap kali Anda login!</li>
                    <li><strong>Password:</strong> Masukkan kata sandi rahasia yang kuat. Jangan beritahukan kepada siapa pun.</li>
                </ul>
            </li>
            <li>Setelah semua terisi, klik tombol biru <strong>Daftar & Mulai</strong>. Anda akan langsung dibawa masuk ke halaman Dashboard!</li>
        </ol>
        <h4>B. Mengatur Profil & Invoice Toko</h4>
        <ol>
            <li>Setelah masuk (login), lihat panel menu di sebelah kiri layar (Sidebar). Klik menu <strong>Pengaturan Toko</strong> berlogo roda gigi.</li>
            <li>Di tab <em>Profil Toko</em>, Anda bisa melengkapi:
                <ul>
                    <li><strong>Jenis Usaha:</strong> Pilih kategori bisnis Anda (F&B, Ritel, Jasa, dll).</li>
                    <li><strong>Alamat Lengkap:</strong> Masukkan alamat toko fisik Anda. Alamat ini akan tercetak otomatis di struk belanja pelanggan.</li>
                    <li><strong>Logo Toko:</strong> Klik kotak \"Unggah Logo\" untuk memilih file foto logo Anda dari komputer/HP. Logo inilah yang akan menjadi ikon di sudut nota.</li>
                </ul>
            </li>
            <li>Selanjutnya, pindah ke tab <strong>Pengaturan Invoice</strong> di sebelah kanan atas:
                <ul>
                    <li><strong>Prefix Invoice:</strong> Ini adalah huruf awalan untuk nomor struk Anda. Jika Anda mengisi <code>INV</code>, maka nota pertama akan bernama <code>INV-2026-001</code>. Jika toko Anda bernama \"Senja\", Anda bisa memakai prefix <code>SNJ</code>.</li>
                    <li><strong>Teks Header Struk:</strong> Kalimat sapaan yang muncul di bagian paling atas struk (misal: \"Selamat Datang di Kedai Kami\").</li>
                    <li><strong>Teks Footer Struk:</strong> Kalimat penutup di bagian paling bawah struk (misal: \"Barang yang sudah dibeli tidak dapat ditukar. Terima Kasih!\").</li>
                    <li><strong>Pilihan Template Struk:</strong> SIMAJURAZ menyediakan beberapa gaya desain struk (Minimalist, Standard, dll). Pilih salah satu, lalu klik tombol abu-abu <strong>Preview Invoice</strong> untuk melihat persis bagaimana struk itu akan dicetak nantinya!</li>
                </ul>
            </li>
            <li>Pastikan Anda mengeklik tombol biru <strong>Simpan Pengaturan</strong> di pojok kanan bawah agar semua data tidak hilang.</li>
        </ol>
        <h4>C. Mengganti Password & Menambah Karyawan</h4>
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
            <li>Sebelum membuat barang, buatlah \"Grup\" barang terlebih dahulu. Klik tombol <strong>Tambah Kategori</strong>.</li>
            <li>Ketik nama kategori (contoh: \"Minuman Dingin\" atau \"Bahan Baku\"). Klik Simpan. Kategori akan muncul sebagai \"Chip\" tombol yang bisa diklik.</li>
            <li>Jika ada kategori salah ketik, Anda bisa menghapusnya dengan menekan ikon <strong>Tempat Sampah (Tong Sampah) merah</strong> di sebelahnya.</li>
        </ol>
        <h4>B. Menambah Barang Secara Detail</h4>
        <ol>
            <li>Klik tombol hijau <strong>Tambah Barang</strong>. Sebuah jendela form besar akan menutupi layar. Mari kita isi satu per satu:</li>
            <li><strong>Unggah Foto Produk:</strong> Di sebelah kiri, klik kotak bergambar kamera. Pilih foto makanan/produk Anda. Foto ini sangat berguna di halaman Kasir agar mata Anda cepat mengenali barang.</li>
            <li><strong>Nama Barang & Kategori:</strong> Isi nama produk (misal: \"Kopi Susu Gula Aren\"). Lalu pilih Kategorinya dari daftar yang sudah Anda buat tadi.</li>
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
            <li>Di sisi kiri layar terdapat etalase barang Anda. Klik kotak bergambar produk tersebut, dan ia akan melompat masuk ke \"Keranjang Belanja\" di sisi kanan.</li>
            <li><strong>Penggunaan Barcode:</strong> Anda tidak perlu repot mengeklik! Pastikan kursor (*mouse*) berada di layar, ambil produk fisik pelanggan, dan \"TIT!\" tembak dengan <strong>Barcode Scanner</strong>. Barang akan langsung masuk keranjang seketika. Sangat cepat!</li>
            <li><strong>Ubah Kuantitas (Jumlah):</strong> Di panel keranjang kanan, jika pelanggan beli 3 kopi, cukup tekan tombol <code>+</code> di sebelah nama barang sampai angkanya jadi 3. Tekan <code>-</code> jika ingin mengurangi, atau tekan <strong>ikon tong sampah</strong> untuk membatalkan item tersebut.</li>
        </ol>
        <h4>C. Melakukan Pembayaran</h4>
        <ol>
            <li>Jika pesanan sudah sesuai, klik tombol hijau raksasa <strong>BAYAR</strong> di pojok kanan bawah. Jendela Pembayaran akan muncul.</li>
            <li>Lihat angka besar di tengah: Itu adalah <strong>Total Tagihan</strong> pelanggan.</li>
            <li><strong>Pilih Metode Pembayaran:</strong>
                <ul>
                    <li><strong>Tunai (Cash):</strong> Jika pelanggan menyerahkan uang kertas. Ketikkan jumlah uang yang diberikan (misal: Tagihan Rp 45.000, pelanggan memberi uang Rp 50.000. Ketik \"50000\" di kolom <em>Nominal Dibayar</em>). Sistem akan otomatis memunculkan tulisan <strong>Kembalian: Rp 5.000</strong>.</li>
                    <li><strong>Tombol Cepat Uang Pas:</strong> Jika malas mengetik, kami sediakan tombol cepat di bawahnya (Uang Pas, 20.000, 50.000, 100.000). Klik saja tombol tersebut!</li>
                    <li><strong>Transfer / QRIS / Non-Tunai:</strong> Jika pelanggan bayar via m-banking. Pilih opsi ini. Kolom nominal akan hilang karena pembayaran non-tunai dianggap SELALU PAS (tidak ada kembalian). Uang ini tidak akan tercatat masuk laci kasir fisik, melainkan kas digital.</li>
                </ul>
            </li>
            <li>Klik tombol besar <strong>Proses Bayar</strong>. Transaksi selesai! Stok barang di inventori langsung terpotong.</li>
        </ol>
        <h4>D. Mencetak & Membagikan Struk</h4>
        <ol>
            <li>Setelah \"Proses Bayar\" ditekan, jendela baru akan muncul menampilkan wujud asli struk (lengkap dengan logo toko Bapak!).</li>
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
            <li>Gunakan ikon Kalender di atas untuk memfilter: Apakah Bapak ingin melihat Laba hari ini saja? Laba seminggu ini? Atau bulan lalu?</li>
        </ol>
        <h4>B. Menginput Pemasukan Lain (Selain Jualan)</h4>
        <ol>
            <li>Buka menu <strong>Keuangan</strong>. Anda akan berada di tab <em>Arus Kas (Buku Kas)</em>.</li>
            <li>Jika ada uang masuk yang BUKAN dari jualan kasir (contoh: Uang hasil jual kardus bekas minuman, atau uang parkir), klik tombol hijau <strong>Pemasukan</strong>.</li>
            <li>Di form yang muncul: Ketikkan <strong>Nominal</strong> (contoh: 25000) dan <strong>Deskripsi</strong> (contoh: \"Jual kardus bekas\"). Klik Simpan. Uang ini akan mendongkrak Laba Bersih Anda.</li>
        </ol>
        <h4>C. Menginput Pengeluaran Toko</h4>
        <ol>
            <li>Ini fitur SANGAT PENTING. Setiap uang yang keluar dari laci untuk operasional toko harus dicatat! Jika tidak, Laba Bersih Anda akan terlihat palsu (seolah besar padahal banyak hutang listrik).</li>
            <li>Klik tombol merah <strong>Pengeluaran</strong>.</li>
            <li>Di form yang muncul: Ketikkan <strong>Nominal</strong> (contoh: 150000) dan <strong>Deskripsi</strong> (contoh: \"Beli Gas Elpiji 3Kg\" atau \"Bayar Tukang Sampah\"). Klik Simpan.</li>
            <li>Uang ini akan otomatis mengurangi Laba Bersih di Laporan Akhir.</li>
        </ol>
        <h4>D. Menghapus / Mengedit Catatan yang Salah</h4>
        <ol>
            <li>Di bawah tombol tadi, ada Tabel Riwayat Buku Kas yang menampilkan list semua uang masuk & keluar.</li>
            <li>Cari catatan yang salah (misal typo mengetik 150.000 padahal 15.000).</li>
            <li>Lihat ke ujung paling kanan baris tabel tersebut, di bawah kolom \"Aksi\".</li>
            <li>Klik ikon <strong>Pensil (Edit)</strong> untuk mengubah nominalnya, ATAU klik ikon <strong>Tempat Sampah Merah (Hapus)</strong> untuk menghapus catatan tersebut sepenuhnya dari sistem.</li>
        </ol>',

        'kb_c5_title' => 'Penggajian Karyawan',
        'kb_c5_desc' => '<p>SIMAJURAZ memiliki modul Payroll cerdas. Gaji yang Anda bayarkan ke karyawan akan langsung tercatat sebagai pengeluaran toko tanpa perlu mencatat dua kali di buku!</p>
        <h4>A. Memproses Penggajian</h4>
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
            <li>Karyawan Bapak butuh bukti tertulis? Bisa! Setelah ditekan simpan tadi, lihat ke \"Tabel Riwayat Penggajian\" di bawahnya.</li>
            <li>Cari catatan gajinya, geser ke paling kanan pada kolom Aksi, lalu klik tombol biru bertuliskan <strong>Download Slip</strong>.</li>
            <li>Browser akan langsung mengunduh file PDF profesional yang menampilkan rincian persis potongan dan bonus gajinya, lengkap dengan logo toko Anda di atasnya!</li>
        </ol>',
        
        'kb_c6_title' => 'Kalkulator HPP Premium',
        'kb_c6_desc' => '<p>Fitur rahasia para pengusaha F&B (Kafe/Resto). Kalkulator ini akan membongkar Harga Modal (*Food Cost*) asli dari sebuah resep secara terperinci.</p>
        <h4>Cara Simulasi Harga Racikan Makanan:</h4>
        <ol>
            <li>Buka menu <strong>Kalkulator HPP</strong>.</li>
            <li>Klik tombol besar <strong>Buat Perhitungan Baru</strong>. Anda akan dihadapkan pada satu halaman form panjang yang terbagi 4 Tahapan. Mari kita isi dari atas ke bawah:</li>
            <li><strong>Tahap 1 (Info Dasar):</strong> Beri nama resep ini. Misalnya: \"Kopi Susu Gula Aren Cup Medium\". Masukkan juga target jumlah porsi jika resep ini dibuat masal (biasanya 1 porsi saja).</li>
            <li><strong>Tahap 2 (Input Bahan Baku Utama):</strong> Ini adalah bagian terseru. Klik tombol <strong>+ Tambah Bahan</strong>.
                <ul>
                    <li>Nama Bahan: Ketik \"Biji Kopi Arabica\".</li>
                    <li>Harga Beli Mentah: Berapa Bapak beli 1 bungkusnya? Misal: Rp 100.000.</li>
                    <li>Berat Total Mentah: 1 bungkus itu berapa isinya? Misal: 1000 Gram (1 Kg).</li>
                    <li>Takaran Untuk 1 Porsi: Untuk bikin 1 cup kopi, butuh berapa gram? Misal: 15 Gram.</li>
                    <li><em>AJAIB!</em> Kalkulator akan memecah harga Rp 100rb/kg tadi dan menyimpulkan bahwa modal kopi untuk 1 cup tersebut hanyalah <strong>Rp 1.500 perak</strong>! Lakukan hal yang sama untuk Susu UHT dan Gula Aren.</li>
                </ul>
            </li>
            <li><strong>Tahap 3 (Input Bahan Kemasan & Ekstra):</strong>
                <ul>
                    <li>Klik tombol tambah di form bahan kemasan. Masukkan item seperti \"Gelas Cup Plastik\" (misal: Beli 50pcs harga 20rb, berarti 1pcs modalnya Rp 400). Masukkan sedotan, kantong plastik, dll.</li>
                    <li>Jangan lupa biaya gas elpiji dan tenaga listrik barista per porsi agar tidak bocor alus!</li>
                </ul>
            </li>
            <li><strong>Tahap 4 (Kalkulasi Target Margin):</strong>
                <ul>
                    <li>Setelah sistem menghitung total keseluruhan modal dari Tahap 2 & 3 (misalnya Total HPP racikan = Rp 5.000), saatnya menentukan untung!</li>
                    <li>Di kolom Target Margin, ketik berapa persen untung yang Bapak inginkan. Misal: 50%.</li>
                    <li>Sistem akan menyimpulkan: Untuk dapat untung 50%, Bapak <strong>HARUS menjual produk ini di harga minimal Rp 10.000</strong>! Ini adalah fungsi <em>Rekomendasi Harga Jual</em>.</li>
                </ul>
            </li>
            <li>Setelah puas melihat hasilnya, Anda bisa keluar. Jika Bapak menjual produk ini, jangan lupa mendaftarkannya di menu **Inventori** dan masukkan nilai Rp 5000 di kolom HPP, dan Rp 10000 di kolom Harga Jual!</li>
        </ol>',

        'kb_c7_title' => 'Sistem Bagi Hasil (Investor)',
        'kb_c7_desc' => '<p>Membuka toko dengan modal patungan teman/saudara? Jangan berantem masalah persentase untung! Gunakan fitur ini agar perhitungannya *fair* dan transparan.</p>
        <h4>A. Mendaftarkan Nama Investor</h4>
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
            <li><strong>Apa yang terjadi?</strong> Sistem akan berlari memeriksa total Laba Bersih Bapak pada bulan tersebut (Total Penjualan - HPP - Beli Gas - Gaji Karyawan). Misalkan laba bersih final adalah Rp 10.000.000.</li>
            <li>Sistem akan memecahkan uang 10 Juta itu secara otomatis! Sistem mencatat: Jatah Bapak Budi (30%) adalah Rp 3.000.000, Jatah Owner (Sisa 70%) adalah Rp 7.000.000.</li>
            <li>Riwayat pembagian ini akan abadi di tabel. Anda bisa klik <strong>Cetak PDF</strong> untuk diserahkan ke Bapak Budi sebagai bukti transparansi. Jika uang sudah ditransfer, klik tombol <strong>Tandai Sudah Dibayar</strong> agar laporannya berstatus hijau (Lunas).</li>
        </ol>',
        
        'kb_c8_title' => 'Cetak & Ekspor Laporan PDF',
        'kb_c8_desc' => '<p>Kecanggihan sebuah sistem terletak pada kemampuan mencetak laporannya. Di SIMAJURAZ, hampir setiap halaman punya tombol <strong>Ekspor PDF</strong> sekali klik yang akan men- *download* dokumen ukuran A4 yang sangat rapi untuk diserahkan ke atasan.</p>
        <h4>Cara Mencari Tombol Ekspor di Setiap Fitur:</h4>
        <ul>
            <li><strong>Laba Rugi Lengkap:</strong> Buka menu <em>Laporan > Laba Rugi</em>. Atur filter tanggal (Dari - Sampai). Setelah angka labanya muncul, klik tombol abu-abu berlogo PDF merah bertuliskan <strong>Ekspor PDF</strong> di pojok kanan atas tabel. Laporan final akan terunduh.</li>
            <li><strong>Laporan Riwayat Struk (Transaksi):</strong> Buka menu <em>Laporan > Transaksi Penjualan</em>. Di sini Bapak bisa melihat daftar struk 1, struk 2, dst. Cari tombol Ekspor PDF di atas tabel untuk men-download log seluruh nota penjualan Bapak bulan itu.</li>
            <li><strong>Laporan Arus Kas (Uang Masuk/Keluar):</strong> Buka menu <em>Laporan > Arus Kas</em>. Tekan tombol Ekspor PDF. Laporan ini bagaikan mutasi rekening Bank Bapak, isinya runtutan tanggal kapan beli gas, kapan uang parkir masuk, dsb.</li>
            <li><strong>Laporan Nilai Gudang (Inventori):</strong> Buka halaman <em>Inventori</em>. Tombol Ekspor PDF di sini berfungsi untuk \"Opname Stok\". Sistem akan mencetak daftar barang, sisa stok, dan mengkalikannya dengan HPP untuk memberitahu Bapak, \"Oh, nilai seluruh harta karun barang dagangan saya di gudang saat ini adalah 25 Juta Rupiah\".</li>
            <li><strong>Slip Gaji & Bagi Hasil:</strong> Seperti yang dijelaskan di bab sebelumnya, Slip Gaji Karyawan didownload dengan mengeklik tombol <em>Download Slip</em> di menu Karyawan. Sedangkan Laporan Investor didownload di menu Bagi Hasil. Keduanya juga berwujud file PDF siap *print*.</li>
        </ul>
    ],
";

$en_kb = str_replace(
    ['Pusat Bantuan & Panduan Penggunaan', 'Pelajari cara memaksimalkan penggunaan SIMAJURAZ untuk operasional bisnis Anda dengan panduan sangat rinci ini.', 'Mendaftar & Pengaturan Toko', 'Manajemen Inventori (Barang)', 'Operasional Kasir (POS)', 'Keuangan & Arus Kas', 'Penggajian Karyawan', 'Kalkulator HPP Premium', 'Sistem Bagi Hasil (Investor)', 'Cetak & Ekspor Laporan PDF'],
    ['Help Center & User Guide', 'Learn how to maximize the use of SIMAJURAZ for your business operations with this extremely detailed guide.', 'Registering & Store Settings', 'Inventory Management', 'POS Operations', 'Finance & Cash Flow', 'Employee Payroll', 'Premium COGS Calculator', 'Profit Sharing (Investors)', 'PDF Report Export'],
    $id_kb
);
$en_kb = str_replace('    ],', '    ]', $en_kb);

$new_content = "";
$in_kb_id = false;
$in_kb_en = false;

$array_section = '';

foreach ($lines as $line) {
    if (strpos($line, "'id' => [") !== false) {
        $array_section = 'id';
    } elseif (strpos($line, "'en' => [") !== false) {
        $array_section = 'en';
    }
    
    if (strpos(trim($line), "// KB") === 0) {
        if ($array_section == 'id') {
            $in_kb_id = true;
            $new_content .= $id_kb;
        } elseif ($array_section == 'en') {
            $in_kb_en = true;
            $new_content .= $en_kb;
        }
    }
    
    // Skip old KB lines until the end of the array
    if ($in_kb_id) {
        if (trim($line) == '],') {
            $in_kb_id = false;
        }
        continue;
    }
    
    if ($in_kb_en) {
        if (trim($line) == ']' || trim($line) == '];') {
            $in_kb_en = false;
            $new_content .= "];\n";
        }
        continue;
    }
    
    $new_content .= $line;
}

file_put_contents('C:\laragon\www\SIMAJURAZ\includes\RAZlang.php', $new_content);
echo "Lang file v4 completely rebuilt safely!";
?>
