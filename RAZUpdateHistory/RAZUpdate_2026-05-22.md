# SIMAJURAZ Update History
**Tanggal:** 2026-05-22

---

### Update #1: Ekspansi Modul Keuangan & Penggajian (Payroll)
**Waktu:** 2026-05-22 10:00 WIB  
**Versi:** 1.0.3  

#### Fitur & Perbaikan:
- **Tabel Gaji Karyawan (`salaries`):** Pembuatan skema database untuk merekam pembayaran gaji.
- **Backend API Payroll (`RAZapiSalaries.php`):** Logika CRUD penggajian yang terintegrasi langsung dengan `cash_flows`.
- **UI Tab Penggajian:** Menambahkan sistem *Tab* pada menu Karyawan (`RAZusers.php`) untuk Manajemen Gaji.
- **Kalkulator Otomatis:** Modal penggajian menghitung *Base Salary* + *Bonus* - *Potongan* secara real-time.
- **Slip Gaji PDF:** Implementasi cetak *Payslip* profesional format A5 Landscape.
- **Bug Fix DOM:** Menambahkan form wrap pada input password di manajemen *user* untuk menghilangkan *warning* konsol browser.
- **Bug Fix Cashflow:** Memperbaiki sistem filter tanggal `date_from=undefined` pada `RAZFinance.js` yang menyebabkan tabel arus kas di tab keuangan kosong.

---

### Update #2: Landing Page SaaS, Knowledgebase & Multi-bahasa
**Waktu:** 2026-05-22 15:30 WIB  
**Versi:** 1.1.0  

#### Fitur & Perbaikan:
- **Split Routing:** File `index.php` yang lama telah di-rename menjadi `RAZlogin.php` untuk memisahkan *portal autentikasi* dan *landing page promosi*.
- **Landing Page Modern (`index.php`):** Pembuatan halaman promosi baru dengan estetika *Glassmorphism*, layout grid 8-Fitur komprehensif, dan *zig-zag showcase* untuk menampilkan screenshot asli aplikasi (Dashboard, POS, Finance).
- **Sistem Pusat Bantuan (`RAZknowledgebase.php`):** Pembuatan halaman khusus dokumentasi/panduan pengguna lengkap dengan navigasi *sticky sidebar*.
- **Sistem i18n (`RAZlang.php`):** Implementasi kamus dwibahasa (Indonesia & Inggris) untuk pengunjung *landing page*, yang preferensinya disimpan secara presisten menggunakan *Cookies*.
- **Tema Dinamis (Dark/Light Mode):** Perombakan `RAZLanding.css` agar menggunakan *CSS Variables*. Tema dapat diganti dengan mulus melalui *toggle* Matahari/Bulan, dan status tema disimpan otomatis via `localStorage`.

---
*End of Update Log*
