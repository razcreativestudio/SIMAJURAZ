# 🎨 Panduan Desain Antarmuka: SIMAJURAZ (`DESIGN.md`)
**Sistem Panduan UI/UX Aplikasi Point of Sale Multi-Tenant**

Dokumen ini adalah sumber kebenaran tunggal (*single source of truth*) untuk seluruh elemen visual, interaksi, dan tata letak dalam pengembangan SIMAJURAZ. Tujuannya adalah memastikan konsistensi desain, menghindari penyimpangan antarmuka, dan memberikan pengalaman pengguna (*User Experience*) yang mulus bagi Super Admin, Owner, maupun Karyawan.

## 1. Filosofi & Prinsip Desain
*   **Intuitif & Cepat:** Karena ini adalah aplikasi POS, kasir harus bisa melakukan transaksi dalam hitungan detik. Mengurangi jumlah klik adalah prioritas.
*   **Isolasi Konteks (Modal-First):** Pengguna tidak boleh sering berpindah halaman untuk tindakan sederhana. Pembuatan, pengeditan, dan konfirmasi harus dilakukan di dalam tempat (*in-place*) menggunakan sistem Modal.
*   **Responsif Penuh:** Tampilan harus beradaptasi dengan sempurna di perangkat kasir layar sentuh (tablet), laptop, maupun monitor desktop.

---

## 2. Standar Komponen Inti

### A. Sistem Modal (Wajib)
Semua interaksi yang memerlukan input data singkat atau keputusan kritis **wajib** menggunakan jendela *Modal* (Pop-up *overlay*) untuk menjaga pengguna tetap berada di konteks halaman saat ini.
*   **Modal Konfirmasi:** Wajib muncul untuk tindakan destruktif (seperti menghapus barang, membatalkan transaksi, atau menutup kas). Harus memiliki latar belakang gelap (*backdrop blur*) dengan tombol "Batal" (abu-abu) dan "Ya, Lanjutkan" (warna bahaya/merah).
*   **Modal Form (Add/Edit):** Form untuk "Tambah Barang", "Edit Harga", "Tambah Karyawan", atau "Upload Logo Toko" harus berada di dalam modal.
*   **Perilaku Modal:** Harus bisa ditutup dengan mengklik area di luar modal (*backdrop click*) atau menekan tombol `ESC` (kecuali pada proses loading/transaksi).

### B. Tombol & Ikonografi (Wajib)
Aplikasi ini tidak akan menggunakan tombol teks polos. Setiap tombol wajib memiliki ikon yang merepresentasikan tindakannya untuk mempercepat kognisi pengguna.
*   **Library Ikon:** Gunakan *icon set* yang konsisten dan modern (disarankan: *Phosphor Icons*, *Heroicons*, atau *FontAwesome 6*).
*   **Tata Letak Ikon:** Ikon diletakkan di sebelah kiri teks (contoh: `[Ikon Keranjang] Bayar` atau `[Ikon Plus] Tambah Item`).
*   **Tombol Ikon Saja (Icon-only Buttons):** Wajib menggunakan *Tooltip* saat di-hover agar fungsi tombol tetap jelas (misal: ikon tempat sampah untuk hapus).
*   **Status Tombol:** Harus memiliki transisi warna yang jelas saat di-*hover*, di-*klik* (active), dan di-*disable* (saat proses submit berlangsung agar tidak terjadi *double-input*).

### C. Standar Tabel Data
Tabel digunakan pada halaman `RAZinventory.php`, `RAZfinance.php`, `RAZusers.php`, dan `RAZreports.php`. Setiap tabel wajib memiliki:
1.  **Header Aksi:** Fitur Pencarian (*Search bar*), Filter kategori/tanggal, dan tombol "Tambah [Data]" di bagian atas tabel.
2.  **Kolom "Action" (Wajib):** Kolom paling kanan di setiap baris data yang berisi tombol aksi spesifik untuk baris tersebut.
    *   *Edit* (Ikon Pensil / warna biru atau kuning) -> Membuka Modal Edit.
    *   *Hapus* (Ikon Tempat Sampah / warna merah) -> Membuka Modal Konfirmasi.
    *   *Detail* (Ikon Mata / warna abu-abu) -> Membuka rincian data.
3.  **Paginasi (Pagination):** Tabel tidak boleh memuat ratusan data sekaligus ke bawah. Batasi 10, 25, atau 50 baris per halaman.
4.  **Empty State:** Jika tabel kosong, jangan hanya menampilkan tabel kosong. Tampilkan ilustrasi/ikon menarik beserta teks "Belum ada data di sini" dan tombol untuk menambah data.

---

## 3. Sistem Warna & Tipografi

### A. Palet Warna Fungsional
Warna harus digunakan secara semantik (sesuai maknanya):
*   **Primary (Warna Utama):** Digunakan untuk elemen navigasi utama, tombol simpan, dan aksi utama. (Bisa disesuaikan dengan identitas *default* RAZ).
*   **Success (Hijau):** Untuk status berhasil, pemasukan finansial, tombol bayar, atau profit.
*   **Danger (Merah):** Untuk peringatan, tombol hapus, peringatan stok habis, atau pengeluaran.
*   **Warning (Kuning/Oranye):** Untuk peringatan stok menipis, edit data, atau status *pending*.
*   **Info (Biru Muda):** Untuk notifikasi informatif atau tooltip.
*   **Neutral/Background:** Abu-abu sangat terang untuk latar belakang (`#F3F4F6` di Tailwind) dan putih bersih untuk *Card* atau *Modal*.

### B. Tipografi
*   **Font Utama:** Gunakan *sans-serif* modern yang sangat mudah dibaca untuk angka dan tabel (disarankan: *Inter*, *Roboto*, atau *Plus Jakarta Sans*).
*   **Hierarki:**
    *   *Heading* tebal untuk nilai rupiah besar di dashboard dan total bayar di POS.
    *   *Body text* standar (14px - 16px) untuk nama item dan menu.
    *   Gunakan *Monospace* untuk kode resi/SKU agar angka mudah dibedakan.

---

## 4. Struktur Tata Letak (Layouting)

### A. Tampilan Utama (Dashboard & Back-office)
*   **Sidebar Navigation:** Terletak di sebelah kiri, berisi menu yang dapat diciutkan (*collapsible*) menjadi ikon saja untuk memperluas area kerja.
*   **Top Bar (Header):** Berisi:
    *   Nama dan Logo Toko (khusus Owner/Kasir).
    *   Indikator Role Pengguna (Super Admin / Owner / Kasir).
    *   Menu dropdown profil (untuk Logout atau Pengaturan Akun).
*   **Main Content:** Semua konten dibungkus dalam bentuk "Card" dengan bayangan (*shadow*) halus dan sudut membulat (*rounded corners*) agar terlihat modern dan bersih.

### B. Tampilan Khusus POS (`RAZpos.php`)
Ini adalah pengecualian dari layout standar. Halaman ini harus memaksimalkan *screen real estate*.
*   **Kiri/Tengah (Area Etalase):** *Grid* daftar barang beserta gambar (jika ada), nama, dan harga. Dilengkapi kotak pencarian/scanner barcode yang selalu *auto-focus*.
*   **Kanan (Keranjang/Cart):** Kolom tetap (*sticky/fixed*) yang menampilkan struk digital sementara. Menampilkan rincian barang yang dipilih, pengaturan jumlah/kuantitas (+/-), subtotal, pajak, dan tombol besar "BAYAR" di paling bawah.

---

## 5. Umpan Balik & Interaksi (Micro-interactions)
*   **Toast Notifications (Notifikasi Mengambang):** Setelahl melakukan aksi sukses (misal: "Barang berhasil ditambahkan") atau gagal, jangan gunakan *alert* bawaan browser. Gunakan *Toast/Snackbar* modern yang muncul di sudut kanan atas atau bawah dan hilang otomatis dalam 3 detik.
*   **Loading State:**
    *   Saat menekan tombol simpan, tombol harus memunculkan ikon *spinner* berputar dan teks berubah menjadi "Menyimpan...".
    *   Saat memuat data tabel atau chart di `RAZdashboard.php`, gunakan *skeleton loading* (animasi blok abu-abu) daripada layar kosong.