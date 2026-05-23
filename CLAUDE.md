# 🧠 CLAUDE.md — Master Implementation Plan SIMAJURAZ
**Versi:** 1.0.0  
**Tanggal Mulai:** 2026-05-21  
**Status:** 🟡 In Progress  
**Author:** Claude (AI Assistant)

---

## 📋 Ringkasan Proyek

SIMAJURAZ adalah aplikasi Point of Sale (POS) berbasis web multi-tenant oleh RAZ Creative Studio. Dibangun dengan PHP + SQLite/MySQL, JavaScript, dan CSS modern. Sistem mendukung 3 role: Super Admin, Owner Toko, dan Karyawan.

---

## 🏗️ Arsitektur Sistem

```
┌─────────────────────────────────────────────────┐
│                   BROWSER                        │
│  index.php │ RAZdashboard │ RAZpos │ RAZinventory│
└──────────────────────┬──────────────────────────┘
                       │ HTTP Request
┌──────────────────────▼──────────────────────────┐
│              PHP BACKEND (API Layer)             │
│  RAZconfig.php │ RAZauth.php │ RAZapi.php        │
└──────────────────────┬──────────────────────────┘
                       │ PDO
┌──────────────────────▼──────────────────────────┐
│         DATABASE (SQLite / MySQL)                │
│  users │ stores │ items │ transactions │ cash    │
└─────────────────────────────────────────────────┘
```

---

## 📐 Struktur Database (Multi-Tenant)

### Tabel `users`
| Kolom | Tipe | Keterangan |
|-------|------|------------|
| id | INT PK AUTO | ID unik user |
| store_id | INT NULL FK | NULL untuk Super Admin |
| username | VARCHAR(50) | Username login |
| password | VARCHAR(255) | Bcrypt hash |
| full_name | VARCHAR(100) | Nama lengkap |
| role | ENUM | 'superadmin','owner','employee' |
| is_active | TINYINT(1) | Status aktif |
| created_at | DATETIME | Waktu dibuat |
| updated_at | DATETIME | Waktu diupdate |

### Tabel `stores`
| Kolom | Tipe | Keterangan |
|-------|------|------------|
| id | INT PK AUTO | ID toko |
| owner_id | INT FK | Referensi ke users.id |
| store_name | VARCHAR(100) | Nama toko |
| store_address | TEXT | Alamat toko |
| store_phone | VARCHAR(20) | Telepon toko |
| store_logo | VARCHAR(255) | Path file logo |
| tax_percentage | DECIMAL(5,2) | Persentase pajak |
| receipt_footer | TEXT | Pesan di bawah struk |
| created_at | DATETIME | Waktu dibuat |

### Tabel `categories`
| Kolom | Tipe | Keterangan |
|-------|------|------------|
| id | INT PK AUTO | ID kategori |
| store_id | INT FK | Isolasi per toko |
| name | VARCHAR(50) | Nama kategori |
| color | VARCHAR(7) | Warna badge HEX |

### Tabel `items`
| Kolom | Tipe | Keterangan |
|-------|------|------------|
| id | INT PK AUTO | ID barang |
| store_id | INT FK | Isolasi per toko |
| category_id | INT FK NULL | Kategori barang |
| name | VARCHAR(100) | Nama barang |
| sku | VARCHAR(50) | Kode SKU/Barcode |
| hpp | DECIMAL(12,2) | Harga Pokok Penjualan |
| sell_price | DECIMAL(12,2) | Harga Jual |
| stock | INT | Jumlah stok |
| min_stock | INT DEFAULT 5 | Batas peringatan stok |
| image | VARCHAR(255) | Path gambar barang |
| is_active | TINYINT(1) | Status aktif |
| created_at | DATETIME | Waktu dibuat |

### Tabel `transactions`
| Kolom | Tipe | Keterangan |
|-------|------|------------|
| id | INT PK AUTO | ID transaksi |
| store_id | INT FK | Isolasi per toko |
| user_id | INT FK | Kasir yang melayani |
| invoice_number | VARCHAR(30) | Nomor invoice unik |
| subtotal | DECIMAL(12,2) | Total sebelum pajak/diskon |
| discount_amount | DECIMAL(12,2) | Nominal diskon |
| tax_amount | DECIMAL(12,2) | Nominal pajak |
| grand_total | DECIMAL(12,2) | Total akhir |
| payment_method | ENUM | 'cash','transfer','qris' |
| amount_paid | DECIMAL(12,2) | Jumlah dibayar |
| change_amount | DECIMAL(12,2) | Kembalian |
| status | ENUM | 'completed','voided' |
| created_at | DATETIME | Waktu transaksi |

### Tabel `transaction_items`
| Kolom | Tipe | Keterangan |
|-------|------|------------|
| id | INT PK AUTO | ID detail |
| transaction_id | INT FK | Referensi transaksi |
| item_id | INT FK | Referensi barang |
| item_name | VARCHAR(100) | Snapshot nama saat beli |
| qty | INT | Jumlah dibeli |
| hpp | DECIMAL(12,2) | Snapshot HPP saat beli |
| sell_price | DECIMAL(12,2) | Snapshot harga jual |
| subtotal | DECIMAL(12,2) | qty × sell_price |

### Tabel `cash_flows`
| Kolom | Tipe | Keterangan |
|-------|------|------------|
| id | INT PK AUTO | ID arus kas |
| store_id | INT FK | Isolasi per toko |
| user_id | INT FK | Siapa yang input |
| type | ENUM | 'income','expense' |
| category | VARCHAR(50) | Kategori kas |
| amount | DECIMAL(12,2) | Nominal |
| description | TEXT | Keterangan |
| created_at | DATETIME | Waktu input |

### Tabel `shifts`
| Kolom | Tipe | Keterangan |
|-------|------|------------|
| id | INT PK AUTO | ID shift |
| store_id | INT FK | Isolasi per toko |
| user_id | INT FK | Kasir yang shift |
| opening_cash | DECIMAL(12,2) | Modal awal kas |
| closing_cash | DECIMAL(12,2) NULL | Kas saat tutup |
| opened_at | DATETIME | Waktu buka |
| closed_at | DATETIME NULL | Waktu tutup |
| notes | TEXT | Catatan shift |

---

## 📁 Struktur File Lengkap

```
SIMAJURAZ/
├── GEMINI.md                    # Spesifikasi proyek
├── DESIGN.md                    # Panduan desain UI/UX
├── CLAUDE.md                    # Master plan (file ini)
├── RAZProjectPlan.md            # Project plan tracker
├── RAZProjectStructure.md       # Struktur file proyek
├── RAZUpdateHistory/            # Folder riwayat update
│   └── RAZUpdate_2026-05-21.md  # Update harian
│
├── backup/                      # Folder backup file
│
├── RAZinstall.php               # Wizard instalasi database
├── RAZconfig.php                # Koneksi database dinamis
├── index.php                    # Landing page + Login/Register
├── RAZdashboard.php             # Dashboard analytics
├── RAZpos.php                   # Point of Sale interface
├── RAZinventory.php             # Manajemen inventori
├── RAZfinance.php               # Manajemen keuangan
├── RAZreports.php               # Laporan & ekspor PDF
├── RAZusers.php                 # Manajemen karyawan
├── RAZlogout.php                # Proses logout
│
├── api/                         # Backend API endpoints
│   ├── RAZauth.php              # API autentikasi
│   ├── RAZapiItems.php          # API CRUD barang
│   ├── RAZapiTransactions.php   # API transaksi POS
│   ├── RAZapiCashflow.php       # API arus kas
│   ├── RAZapiReports.php        # API laporan
│   ├── RAZapiUsers.php          # API manajemen user
│   └── RAZapiStores.php         # API manajemen toko
│
├── assets/
│   ├── css/
│   │   ├── RAZMain.css          # Global styles & design tokens
│   │   ├── RAZAuth.css          # Styles login/register
│   │   ├── RAZDashboard.css     # Styles dashboard
│   │   ├── RAZPos.css           # Styles POS interface
│   │   ├── RAZInventory.css     # Styles inventori
│   │   ├── RAZFinance.css       # Styles keuangan
│   │   ├── RAZReports.css       # Styles laporan
│   │   ├── RAZModal.css         # Styles semua modal
│   │   └── RAZComponents.css    # Toast, table, buttons
│   ├── js/
│   │   ├── RAZMain.js           # Utility global (modal, toast, fetch)
│   │   ├── RAZAuth.js           # Logic login/register
│   │   ├── RAZDashboard.js      # Charts & analytics
│   │   ├── RAZPos.js            # Logic POS & cart
│   │   ├── RAZInventory.js      # Logic inventori
│   │   ├── RAZFinance.js        # Logic keuangan
│   │   ├── RAZReports.js        # Logic laporan & PDF
│   │   └── RAZUsers.js          # Logic manajemen user
│   ├── img/                     # Gambar statis
│   │   └── RAZlogo.png          # Logo SIMAJURAZ
│   └── fonts/                   # Font lokal (opsional)
│
├── uploads/                     # Upload dinamis
│   ├── logos/                   # Logo toko tenant
│   └── items/                   # Gambar barang
│
├── includes/                    # PHP helpers
│   ├── RAZsession.php           # Session & guard middleware
│   ├── RAZhelpers.php           # Fungsi utilitas PHP
│   └── RAZpdf.php               # PDF generator wrapper
│
├── vendor/                      # Library pihak ketiga
│   └── dompdf/                  # Library PDF
│
└── data/                        # Folder SQLite database
    └── .htaccess                # Block akses langsung
```

---

## 🎨 Design System (dari DESIGN.md)

### Palet Warna
```css
/* RAZ Design Tokens */
--raz-primary: #4F46E5;       /* Indigo - Aksi utama */
--raz-primary-dark: #3730A3;  /* Indigo gelap - Hover */
--raz-success: #059669;       /* Emerald - Berhasil/Profit */
--raz-danger: #DC2626;        /* Red - Hapus/Rugi */
--raz-warning: #D97706;       /* Amber - Peringatan */
--raz-info: #0EA5E9;          /* Sky - Informasi */
--raz-bg: #F3F4F6;            /* Gray 100 - Background */
--raz-card: #FFFFFF;          /* White - Card/Modal */
--raz-text: #111827;          /* Gray 900 - Teks utama */
--raz-text-muted: #6B7280;   /* Gray 500 - Teks sekunder */
--raz-border: #E5E7EB;       /* Gray 200 - Border */
--raz-shadow: 0 1px 3px rgba(0,0,0,0.1);
```

### Font
- **Utama:** Inter (Google Fonts) — sans-serif modern
- **Monospace:** JetBrains Mono — untuk SKU/invoice

### Ikon
- **Library:** Phosphor Icons (CDN) — modern, konsisten

### Komponen Wajib
1. **Modal System** — backdrop blur, ESC close, animasi fade+scale
2. **Toast Notification** — pojok kanan atas, auto-dismiss 3 detik
3. **Data Table** — search, filter, pagination, empty state, action column
4. **Buttons** — selalu dengan ikon, hover/active/disabled states
5. **Skeleton Loading** — animasi placeholder saat fetch data
6. **Sidebar** — collapsible, menu sesuai role

---

## 🗓️ Fase Implementasi

### FASE 1: Foundation (Core Infrastructure)
**Target:** Setup database, config, dan instalasi wizard

| No | File | Deskripsi | Status |
|----|------|-----------|--------|
| 1.1 | `RAZconfig.php` | Koneksi database dinamis (SQLite/MySQL) | ⬜ |
| 1.2 | `RAZinstall.php` | Wizard GUI instalasi + DDL tabel | ⬜ |
| 1.3 | `includes/RAZsession.php` | Session management & role guard | ⬜ |
| 1.4 | `includes/RAZhelpers.php` | Fungsi utilitas PHP | ⬜ |
| 1.5 | `assets/css/RAZMain.css` | Design tokens & global styles | ⬜ |
| 1.6 | `assets/css/RAZComponents.css` | Toast, table, buttons, skeleton | ⬜ |
| 1.7 | `assets/css/RAZModal.css` | Sistem modal global | ⬜ |
| 1.8 | `assets/js/RAZMain.js` | Utility JS (modal, toast, API helper) | ⬜ |

### FASE 2: Authentication & Landing
**Target:** Login, register, dan role-based routing

| No | File | Deskripsi | Status |
|----|------|-----------|--------|
| 2.1 | `index.php` | Landing page + form login/register | ⬜ |
| 2.2 | `assets/css/RAZAuth.css` | Styling halaman auth | ⬜ |
| 2.3 | `assets/js/RAZAuth.js` | Logic validasi & submit auth | ⬜ |
| 2.4 | `api/RAZauth.php` | API login, register, logout | ⬜ |
| 2.5 | `RAZlogout.php` | Proses logout & redirect | ⬜ |

### FASE 3: Dashboard & Layout
**Target:** Sidebar, topbar, dan dashboard analytics

| No | File | Deskripsi | Status |
|----|------|-----------|--------|
| 3.1 | `RAZdashboard.php` | Layout utama + dashboard konten | ⬜ |
| 3.2 | `assets/css/RAZDashboard.css` | Styling dashboard & layout | ⬜ |
| 3.3 | `assets/js/RAZDashboard.js` | Charts (Chart.js) & statistik | ⬜ |
| 3.4 | `api/RAZapiStores.php` | API profil & pengaturan toko | ⬜ |

### FASE 4: Inventori & Kategori
**Target:** CRUD barang, kategori, stok management

| No | File | Deskripsi | Status |
|----|------|-----------|--------|
| 4.1 | `RAZinventory.php` | Halaman manajemen inventori | ⬜ |
| 4.2 | `assets/css/RAZInventory.css` | Styling inventori | ⬜ |
| 4.3 | `assets/js/RAZInventory.js` | Logic CRUD & stok | ⬜ |
| 4.4 | `api/RAZapiItems.php` | API CRUD barang & kategori | ⬜ |

### FASE 5: Point of Sale (POS)
**Target:** Interface kasir, cart, pembayaran, cetak struk

| No | File | Deskripsi | Status |
|----|------|-----------|--------|
| 5.1 | `RAZpos.php` | Antarmuka POS full-screen | ⬜ |
| 5.2 | `assets/css/RAZPos.css` | Styling POS layout | ⬜ |
| 5.3 | `assets/js/RAZPos.js` | Logic cart, bayar, struk | ⬜ |
| 5.4 | `api/RAZapiTransactions.php` | API transaksi & struk | ⬜ |

### FASE 6: Keuangan & Kas
**Target:** Arus kas, modal, profit, bagi hasil

| No | File | Deskripsi | Status |
|----|------|-----------|--------|
| 6.1 | `RAZfinance.php` | Halaman manajemen keuangan | ⬜ |
| 6.2 | `assets/css/RAZFinance.css` | Styling keuangan | ⬜ |
| 6.3 | `assets/js/RAZFinance.js` | Logic kas, modal, profit | ⬜ |
| 6.4 | `api/RAZapiCashflow.php` | API arus kas & shift | ⬜ |

### FASE 7: Manajemen User
**Target:** CRUD karyawan oleh Owner

| No | File | Deskripsi | Status |
|----|------|-----------|--------|
| 7.1 | `RAZusers.php` | Halaman manajemen user | ⬜ |
| 7.2 | `assets/js/RAZUsers.js` | Logic CRUD karyawan | ⬜ |
| 7.3 | `api/RAZapiUsers.php` | API manajemen user | ⬜ |

### FASE 8: Laporan & PDF Export
**Target:** Laporan transaksi, keuangan, ekspor PDF

| No | File | Deskripsi | Status |
|----|------|-----------|--------|
| 8.1 | `RAZreports.php` | Halaman laporan & filter | ⬜ |
| 8.2 | `assets/css/RAZReports.css` | Styling laporan | ⬜ |
| 8.3 | `assets/js/RAZReports.js` | Logic filter & ekspor | ⬜ |
| 8.4 | `api/RAZapiReports.php` | API laporan & PDF gen | ⬜ |
| 8.5 | `includes/RAZpdf.php` | PDF generator (Dompdf) | ⬜ |

---

## ✅ Verification Plan

### Automated Testing
1. **PHP Syntax Check:** `php -l` pada setiap file PHP
2. **Database Migration:** Test install wizard SQLite & MySQL
3. **API Endpoint Test:** Manual curl/browser test setiap endpoint
4. **Browser Test:** Navigasi lengkap login → dashboard → POS → laporan

### Manual Verification
1. Register akun Owner baru → Buat toko → Tambah barang
2. Login sebagai Karyawan → Lakukan transaksi POS
3. Cek laporan keuangan → Ekspor PDF
4. Test responsivitas di tablet/mobile viewport
5. Test modal, toast, pagination berfungsi sesuai DESIGN.md

---

## 📝 Catatan Penting

1. **Setiap file WAJIB ada komentar/deskripsi** dalam Bahasa Indonesia
2. **Backup dibuat sebelum setiap edit** di folder `backup/`
3. **Update history dicatat** per hari di `RAZUpdateHistory/`
4. **Project plan & structure diupdate** setiap ada perubahan
5. **Semua dialog menggunakan custom modal** (bukan alert/confirm browser)
6. **UI harus modern, profesional, premium** — tidak boleh terlihat basic
7. **File CSS dipecah per modul** untuk menghindari file terlalu besar
