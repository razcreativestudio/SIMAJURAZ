# RAZ Update Log - 2026-05-23
**Proyek:** SIMAJURAZ
**Fokus Utama:** Optimasi Responsivitas UI, Fitur Lintas Bahasa (I18n), dan Dokumentasi

## 1. Responsivitas Layar Mobile (UI/UX)
- Merombak Navbar Utama di index.php dan RAZknowledgebase.php untuk layar sempit (Smartphone).
- Mengubah tombol teks "Masuk / Login" menjadi ikon bundar agar tidak merusak layout saat di bawah 768px.
- Mengaplikasikan teknik *Fluid Typography* (clamp) pada judul Hero Banner agar ukuran font menyusut mulus.
- Menambahkan class .reveal beserta sensor *Intersection Observer* via JS untuk efek animasi memudar dan meluncur naik (*Scroll Reveal Animation*) saat layar di-*scroll*, sehingga situs tampak jauh lebih hidup dan profesional seperti standar agensi.

## 2. Injeksi & Penyesuaian Footer
- Meng-kloning Footer asli dari portal utama https://raz.my.id/.
- Memasang lapisan *secondary footer* (versi hitam/dark) yang memuat link layanan RAZ Creative Studio tepat di bawah footer bawaan SIMAJURAZ.
- Menyesuaikan *FontAwesome CDN* untuk menarik logo ikon media sosial (Instagram, YouTube, TikTok) secara dinamis lengkap dengan efek elevasi warna *gold* saat kursor melayang di atasnya.
- Memperbaiki isu di mana *grid text alignment* memusat (rata tengah) secara tak disengaja dengan merestorasi *text-align: left*.

## 3. Penerjemahan Knowledge Base Penuh (Bilingual)
- Mengeksekusi penerjemahan blok masif pada file includes/RAZlang.php.
- Sebelumnya fitur Bahasa Inggris (EN) gagal memproses teks paragraf (hanya judul yang diubah), namun kini seluruh narasi 8 Bab panduan lengkap telah terkonversi mulus ke bahasa Inggris. 

## 4. Restrukturisasi Dokumentasi
- Menulis ulang dan memperkaya dokumen README.md yang tadinya hanya 3 baris.
- Menyusun tabel penjelasan arsitektur *Multi-Tenant*, Modul-modul (*Inventory, HPP Analyzer, Finance, Profit Share*), sistem instalasi mandiri, hingga jenjang hak akses (Super Admin, Owner, Kasir).
- Semua panduan dokumentasi diselaraskan dengan informasi terkini per perbaruan V4 (Fase 8 Selesai).

## 5. Hotfix Database & Instalasi (v1.0.1)
- Memperbaiki isu HTTP 500 Internal Server Error yang menyebabkan `SyntaxError: Unexpected end of JSON input` saat modul keuangan mencoba memuat data via `RAZapiCashflow.php`.
- Memperbaiki `RAZinstall.php` dengan menambahkan kueri *Data Definition Language* (DDL) untuk membuat tabel `capital_flows` dan `spoilages` yang sebelumnya absen dari skrip instalasi.
- Menambahkan kolom `deduct_from_share_id` ke dalam tabel `cash_flows` saat instalasi awal.
- Membuat skrip `RAZpatchDB.php` untuk memfasilitasi migrasi cepat database yang sudah berjalan (existing database) sehingga dapat langsung menggunakan fitur arus kas tambahan dan pencatatan kerusakan stok (spoilage).
- Memperbarui versi aplikasi di dalam `RAZconfig.php` menjadi v1.0.1.

## 6. Hotfix Kompatibilitas MySQL & SQLite (v1.0.2)
- Menemukan dan memperbaiki *bug* saat menyimpan HPP (Harga Pokok Penjualan) atau Shift Kasir di MySQL/MariaDB yang menghasilkan kode HTTP 500 Internal Server Error.
- Masalah bersumber pada fungsi waktu SQLite-spesifik `datetime('now','localtime')` yang ditolak (menghasilkan *Syntax Error*) oleh MySQL.
- Merubah kueri `UPDATE` di `RAZapiHpp.php` (Line 114) dan `RAZapiCashflow.php` (Line 243) untuk menerima nilai parameterisasi dinamis `date('Y-m-d H:i:s')` bawaan PHP sehingga kompatibel 100% untuk kedua lingkungan database (SQLite & MySQL).
- Perbaikan diimplementasikan serentak ke direktori utama dan folder `/public/api`.
- Memperbarui versi aplikasi di dalam `RAZconfig.php` menjadi v1.0.2.

## 7. Fitur E-Struk Publik & Screenshot (v1.0.3)
- Memodifikasi `RAZreceipt.php` agar memungkinkan URL struk diakses secara publik oleh pelanggan tanpa harus login. Sistem keamanan menggunakan *Hash Validation (MD5)* yang mencocokkan parameter rahasia untuk mencegah kebocoran data transaksi lain.
- Menambahkan library eksternal `html2canvas` pada jendela e-struk.
- Mengimplementasikan tombol hijau **📥 Download / Share** yang akan otomatis mengambil *screenshot* pixel-perfect dari struk thermal dan menawarkannya sebagai gambar PNG.
- Pada perangkat *Mobile* (Android/iOS), tombol unduh otomatis memanggil *Web Share API* (fitur share bawaan ponsel) sehingga kasir dapat langsung mengirimkan struk gambar ke WhatsApp pelanggan.
- Menambahkan tombol **🔗 Copy Link** untuk memudahkan penyalinan tautan publik.
- Memperbarui versi aplikasi di dalam `RAZconfig.php` menjadi v1.0.3.
