<div align="center">
  <img src="assets/images/logo.svg" alt="SIMAJURAZ Logo" width="120"/>
  <h1>SIMAJURAZ</h1>
  <p><strong>Sistem Manajemen Jualan Oleh RAZ Creative Studio</strong></p>
  <p>
    Aplikasi kasir (POS) premium berbasis Web App dengan arsitektur Multi-Tenant yang menggabungkan kemudahan transaksi, perhitungan HPP otomatis, manajemen inventori, hingga pembukuan komprehensif dalam satu *platform* elegan nan responsif.
  </p>
</div>

---

## 🌟 Mengapa SIMAJURAZ?

SIMAJURAZ dirancang tidak hanya sebagai mesin kasir, melainkan sebagai "**Otak Bisnis**" bagi pelaku UMKM, Kafe, Resto, dan Toko Ritel. Kami mengutamakan desain *Glassmorphism* modern dengan standar industri tingkat tinggi, sehingga pengguna awam sekalipun dapat merasa seperti menggunakan aplikasi berkelas *Enterprise*.

### ✨ Fitur Unggulan

1. **🚀 Arsitektur Multi-Tenant (Isolasi Data Aman)**
   Satu aplikasi dapat menampung ratusan pendaftar (toko) yang berbeda tanpa saling bentrok. Setiap toko memiliki lingkungan data, karyawan, dan pengaturannya sendiri secara independen.
2. **🛍️ Modul Kasir (Point of Sale) Cepat & Dinamis**
   Didesain untuk kecepatan ekstrem. Mendukung sistem keranjang dengan *mouse*, layar sentuh (Tablet), maupun *Barcode Scanner*. Termasuk kalkulasi kembalian tunai, integrasi pembayaran digital (QRIS/Transfer), dan cetak struk Bluetooth Thermal (atau bagikan via WhatsApp).
3. **📦 Manajemen Inventori Terpadu**
   Kendali penuh atas stok gudang. Peringatan otomatis stok menipis, manajemen varian barang, dan kategori terstruktur.
4. **🧮 Kalkulator HPP Premium (Food Cost Analyzer)**
   Fitur *Killer* bagi pengusaha makanan/minuman (F&B)! Mampu membedah modal resep rahasia per-gram hingga ke kemasannya, dan memberikan Rekomendasi Harga Jual berdasarkan margin target.
5. **💸 Buku Keuangan & Arus Kas Cerdas**
   Lupakan pencatatan Excel yang rumit. Sistem ini mengintegrasikan seluruh penjualan, pendapatan ekstra (contoh: parkir), pengeluaran operasional (bayar listrik/gas), hingga gaji karyawan menjadi satu **Laporan Laba Rugi Bersih** yang dihitung secara *real-time*.
6. **🤝 Sistem Bagi Hasil Investor (Profit Sharing)**
   Solusi transparan bagi toko dengan modal patungan. Secara otomatis membagi dividen Laba Bersih bulanan berdasarkan persentase (*share*) masing-masing investor tanpa harus repot menghitung kalkulator.
7. **📄 Laporan Ekspor PDF Profesional**
   Setiap lini sistem dilengkapi dengan fitur cetak PDF (*Invoice, Laporan Laba Rugi, Opname Stok, Slip Gaji, dll*) berstempel logo toko pengguna.
8. **🌐 Bilingual Knowledge Base**
   Dilengkapi dengan Buku Panduan Digital interaktif (Indonesia & English) dengan dukungan visual yang siap membimbing pemilik toko langkah demi langkah secara mandiri.

---

## 🛠️ Teknologi yang Digunakan

*   **Backend:** PHP 8+ (Vanilla/Procedural dengan struktur arsitektur bersih).
*   **Frontend:** Vanilla JavaScript (ES6), HTML5.
*   **Styling:** CSS3 (*Custom Design System* buatan RAZ Creative, bebas ketergantungan dari kerangka kerja berat seperti Bootstrap/Tailwind).
*   **Database:** Hibrida! Mendukung dua mode fleksibel (bisa dipilih saat instalasi):
    *   **SQLite** (Internal - Portabel & ringan tanpa konfigurasi server database).
    *   **MySQL / MariaDB** (Eksternal - Skalabilitas kelas berat).
*   **Ikon & UI:** Phosphor Icons.
*   **Ekspor PDF:** Terintegrasi menggunakan pustaka khusus (*Dompdf/TCPDF*).

---

## 📂 Struktur Proyek Terpadu

```text
SIMAJURAZ/
├── RAZinstall.php                # Wizard GUI Setup Database
├── RAZconfig.php                 # Modul Koneksi Database Dinamis
├── index.php                     # Halaman Landing & Autentikasi (Bilingual)
├── RAZknowledgebase.php          # Pusat Panduan Pengguna
├── RAZdashboard.php              # Dashboard Analitik
├── RAZpos.php                    # Modul Kasir Interaktif
├── RAZinventory.php              # Manajemen Barang & Stok
├── RAZhppReport.php              # Kalkulator HPP Premium
├── RAZfinance.php                # Arus Kas, Keuangan & Bagi Hasil
├── RAZusers.php                  # Manajemen Karyawan & Slip Gaji
│
├── api/                          # Endpoints asinkron (AJAX)
│   ├── RAZauth.php
│   ├── RAZapiItems.php
│   └── (file API lainnya)
│
├── assets/                       # Aset Frontend (CSS, JS, SVG, Gambar)
│   ├── css/                      # Modul CSS yang terpecah sesuai fitur
│   └── js/                       # Modul JS (DOM Logic, Fetch API)
│
├── includes/                     # Logic & Pustaka Backend Inti
│   ├── RAZhelpers.php            # Kumpulan Fungsi Bantuan
│   ├── RAZsession.php            # Penjaga Sesi & Hak Akses
│   └── RAZlang.php               # Kamus Bahasa (Localization)
│
├── data/                         # Berkas Sistem (SQLite DB & JSON Config)
└── uploads/                      # Penyimpanan berkas unggahan (Logo/Barang)
```

---

## 🚀 Panduan Instalasi (Lokal / VPS Server)

SIMAJURAZ dirancang sangat *Plug and Play*. Anda tidak perlu mengetik sintaks SQL manual di PHPMyAdmin.

1.  **Unduh & Ekstrak:** Masukkan folder `SIMAJURAZ` ke dalam direktori server lokal Anda (seperti `htdocs` pada XAMPP atau `www` pada Laragon).
2.  **Jalankan di Browser:** Buka alamat `http://localhost/SIMAJURAZ/` (atau nama domain Anda).
3.  **Proses Instalasi Cerdas:**
    *   Sistem secara otomatis akan mendeteksi bahwa instalasi belum pernah dilakukan. Anda akan dialihkan ke halaman `RAZinstall.php`.
    *   **Pilih Database:** Jika ingin mudah, klik **Gunakan SQLite**. Jika Anda ingin skala besar, pilih **MySQL** dan masukkan *Username, Password*, dan *Nama Database* (database akan dibuat secara otomatis).
    *   Klik Install. Sistem akan menyuntikkan puluhan tabel skema (*DDL*) dalam hitungan detik.
4.  **Siap Digunakan!** Anda akan diarahkan kembali ke `index.php`. Daftar sebagai pemilik toko, atur logo toko Anda, masukkan item di Inventori, dan mulailah mendulang keuntungan.

---

## 🛡️ Level Hak Akses Pengguna (Role Security)

| Peran (Role) | Hak Akses Utama |
| :--- | :--- |
| **Super Admin** | Mengawasi ekosistem web, jumlah penyewa (*Tenant*) secara global. |
| **Owner Toko** | Kontrol absolut atas tokonya. Bisa mengatur logo, melihat laporan Laba/Rugi, mengakses Kalkulator HPP, mengatur persentase investor, dan mendaftarkan akun kasir. Tidak dapat melihat data toko lain. |
| **Kasir (Karyawan)** | Ruang geraknya dibatasi. Hanya bisa melakukan proses di layar Kasir (POS), memantau sisa stok barang, dan mencetak slip gajinya sendiri. Kasir **tidak bisa** melihat modal dasar (HPP), memanipulasi riwayat kas, maupun mengganti profil toko. |

---

<div align="center">
  <b>Dibangun dengan cinta dan dedikasi oleh RAZ Creative Studio © 2026.</b><br>
  <a href="https://raz.my.id">Kunjungi Website Resmi Kami</a>
</div>
