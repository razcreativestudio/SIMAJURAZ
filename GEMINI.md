# 📝 Spesifikasi Proyek: SIMAJURAZ 
**Sistem Manajemen Jualan Oleh RAZ Creative Studio**

## 1. Deskripsi Proyek
SIMAJURAZ adalah aplikasi Point of Sale (POS) berbasis web yang mendukung arsitektur *multi-tenant* (multi-account). Sistem ini dirancang agar banyak pengguna (pemilik usaha) dapat mendaftar, membuat akun toko mereka sendiri, dan menjalankan operasional bisnis tanpa mengganggu kinerja atau data akun pengguna lain (termasuk Super Admin).

* **Tech Stack Utama:** PHP, JavaScript, HTML/CSS (Bootstrap/Tailwind).
* **Database:** Mendukung dua mode (bisa dikonfigurasi saat instalasi):
  1. Internal (SQLite) untuk kemudahan *offline/portable*.
  2. Eksternal (MySQL/MariaDB) untuk skala yang lebih besar.
* **Format Laporan:** HTML to PDF (menggunakan library seperti FPDF, Dompdf, atau TCPDF).

---

## 2. Struktur File & Penamaan Skrip (Standardisasi RAZ)
Berikut adalah struktur file utama dengan penamaan tanpa *underscore*, serta mempertahankan standar untuk halaman login/landing:

* `RAZinstall.php` : GUI Setup untuk memilih database (Internal/Eksternal), migrasi tabel, dan pembuatan akun Super Admin.
* `RAZconfig.php` : File koneksi database dinamis.
* `index.php` : Halaman landing utama dan form otentikasi (Login/Register).
* `RAZdashboard.php` : Halaman ringkasan analitik bisnis (Beda tampilan sesuai role).
* `RAZpos.php` : Antarmuka utama kasir untuk melakukan transaksi.
* `RAZinventory.php` : Manajemen barang, kategori, dan stok.
* `RAZfinance.php` : Manajemen arus kas, HPP, modal, dan profit sharing.
* `RAZreports.php` : Modul pembuatan dan ekspor pembukuan ke PDF.
* `RAZusers.php` : Manajemen hak akses karyawan oleh Owner.

---

## 3. Sistem Hak Akses (Role System)
Sistem memiliki isolasi data yang ketat berdasarkan tingkat akses:

1. **Super Admin:** Mengelola infrastruktur web, melihat jumlah tenant/toko yang terdaftar, namun tidak memiliki akses ke data transaksi spesifik setiap toko.
2. **Owner Toko (Tenant):**
   * Mendaftar sendiri melalui halaman utama dan memiliki otoritas penuh atas tokonya.
   * Bisa mengatur profil toko, termasuk mengunggah **Logo Toko Kustom** (Logo ini hanya akan tampil di *dashboard* Owner/Karyawan serta cetakan struk, tanpa mengubah logo utama SIMAJURAZ).
   * Bisa melihat semua laporan keuangan, HPP, dan pembukuan.
   * Bisa membuat, mengedit, dan menghapus akun Karyawan.
3. **Karyawan (Kasir/Staff):**
   * Login menggunakan kredensial yang dibuat oleh Owner.
   * Tampilan dibatasi: Hanya bisa mengakses `RAZpos.php` untuk berjualan, melihat stok barang (tanpa bisa melihat HPP dasar), dan menutup kas harian.

---

## 4. Modul & Fitur Utama

### A. Modul Instalasi (`RAZinstall.php`)
* Form GUI untuk memasukkan kredensial database eksternal atau tombol untuk men-generate file `.sqlite` internal.
* Skrip otomatis untuk menjalankan *Data Definition Language* (DDL) pembuatan tabel-tabel utama (users, stores, items, transactions, dll).

### B. Modul Inventori & Harga (`RAZinventory.php`)
* **Input Item:** Menambahkan barang dengan nama, SKU/Barcode, dan kategori.
* **Manajemen Harga:** Input Harga Pokok Penjualan (HPP) dan Input Harga Jual.
* **Sistem Stok:** Mengurangi stok secara otomatis (FIFO) setiap ada transaksi di POS, dan peringatan jika stok menipis.

### C. Modul POS & Transaksi (`RAZpos.php`)
* Antarmuka responsif untuk pencarian barang cepat (bisa dengan *barcode scanner*).
* Kalkulasi total belanja, diskon, dan pajak (jika ada).
* **Sistem Pembayaran:** Mendukung input nominal uang tunai, kalkulasi kembalian otomatis, serta opsi pencatatan pembayaran non-tunai (Transfer/Qris).
* **Cetak Struk:** Menampilkan logo kustom toko, informasi item, total, kasir yang bertugas, dan ucapan terima kasih.

### D. Modul Keuangan & Pembukuan (`RAZfinance.php`)
* **Sistem Kas:** Pencatatan arus kas masuk (pemasukan selain jualan) dan keluar (pengeluaran operasional kedai).
* **Manajemen Modal:** Input modal awal saat buka shift/buka toko untuk tracking akurasi laci kasir.
* **Kalkulasi Keuntungan:** Perhitungan otomatis `Total Pendapatan - (Total HPP + Pengeluaran) = Laba Bersih`.
* **Profit Share:** Fitur persentase bagi hasil (misal: untuk investor atau pembagian bonus).

### E. Modul Dashboard & Laporan (`RAZdashboard.php` & `RAZreports.php`)
* **Dashboard Visual:** Menampilkan grafik pendapatan, total modal, item terlaris, dan ringkasan laba rugi.
* **Pembukuan Fleksibel:** Laporan riwayat transaksi dan arus kas yang dapat difilter berdasarkan Harian, Mingguan, Bulanan, dan Tahunan.
* **Tombol Ekspor:** Semua laporan keuangan dapat diunduh dalam format `.pdf` yang rapi dan terstruktur.

---

## 5. Alur Kerja (Workflow) AI
Bagi *AI Assistant* yang membaca dokumen ini, buatlah kode secara bertahap mulai dari:
1. Merancang Struktur Database (Tabel Relasional Multi-Tenant).
2. Membuat `RAZinstall.php` dan `RAZconfig.php`.
3. Membangun Sistem Otentikasi & Role (`index.php`).
4. Mengembangkan Modul POS (`RAZpos.php`) dan Inventori (`RAZinventory.php`).
5. Menyelesaikan Laporan dan Ekspor PDF (`RAZreports.php`).