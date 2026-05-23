/**
 * RAZMain.js — Utility JavaScript Global SIMAJURAZ
 * Versi: 1.0.0 | Dibuat: 2026-05-21
 * Deskripsi: Library JS utama yang menyediakan fungsi global:
 *            - Sistem Modal (buka, tutup, konfirmasi)
 *            - Toast Notification (sukses, error, warning, info)
 *            - API Fetch Helper (wrapper untuk fetch dengan error handling)
 *            - Format Helper (rupiah, tanggal)
 *            - Sidebar Toggle
 */

'use strict';

// ============================================================
// NAMESPACE GLOBAL — Semua fungsi ada di dalam objek RAZ
// ============================================================
const RAZ = {

  // ========================
  // TOAST NOTIFICATION SYSTEM
  // ========================
  
  /**
   * Menampilkan notifikasi toast di pojok kanan atas.
   * Otomatis hilang setelah durasi tertentu.
   * 
   * @param {string} type - Tipe toast: 'success', 'danger', 'warning', 'info'
   * @param {string} title - Judul notifikasi
   * @param {string} message - Pesan detail (opsional)
   * @param {number} duration - Durasi tampil dalam milidetik (default: 3000)
   */
  toast(type, title, message = '', duration = 3000) {
    // Pastikan container toast ada di halaman
    let container = document.querySelector('.raz-toast-container');
    if (!container) {
      container = document.createElement('div');
      container.className = 'raz-toast-container';
      document.body.appendChild(container);
    }

    // Map ikon sesuai tipe (menggunakan Phosphor Icons)
    const icons = {
      success: '<i class="ph-bold ph-check-circle"></i>',
      danger: '<i class="ph-bold ph-x-circle"></i>',
      warning: '<i class="ph-bold ph-warning"></i>',
      info: '<i class="ph-bold ph-info"></i>',
    };

    // Buat elemen toast
    const toast = document.createElement('div');
    toast.className = `raz-toast ${type}`;
    toast.innerHTML = `
      <span class="raz-toast-icon">${icons[type] || icons.info}</span>
      <div class="raz-toast-body">
        <div class="raz-toast-title">${title}</div>
        ${message ? `<div class="raz-toast-message">${message}</div>` : ''}
      </div>
      <button class="raz-toast-close" onclick="this.closest('.raz-toast').remove()">
        <i class="ph-bold ph-x"></i>
      </button>
    `;

    // Masukkan ke container
    container.appendChild(toast);

    // Auto-remove setelah durasi tertentu
    setTimeout(() => {
      toast.classList.add('removing');
      setTimeout(() => toast.remove(), 300);
    }, duration);
  },

  // Shortcut methods untuk toast
  success(title, msg) { this.toast('success', title, msg); },
  error(title, msg) { this.toast('danger', title, msg); },
  warning(title, msg) { this.toast('warning', title, msg); },
  info(title, msg) { this.toast('info', title, msg); },

  // ========================
  // MODAL SYSTEM
  // ========================

  /**
   * Membuka modal berdasarkan ID.
   * Menambahkan class 'show' dan event listener untuk menutup.
   * 
   * @param {string} modalId - ID elemen modal overlay
   */
  openModal(modalId) {
    const overlay = document.getElementById(modalId);
    if (!overlay) return;
    
    overlay.classList.add('show');
    document.body.style.overflow = 'hidden'; // Cegah scroll body

    // Tutup modal saat klik backdrop (area di luar modal)
    overlay._backdropHandler = (e) => {
      if (e.target === overlay) this.closeModal(modalId);
    };
    overlay.addEventListener('click', overlay._backdropHandler);

    // Tutup modal saat tekan ESC
    overlay._escHandler = (e) => {
      if (e.key === 'Escape') this.closeModal(modalId);
    };
    document.addEventListener('keydown', overlay._escHandler);

    // Focus ke elemen pertama yang bisa di-focus
    setTimeout(() => {
      const focusable = overlay.querySelector('input, select, textarea, button:not(.raz-modal-close)');
      if (focusable) focusable.focus();
    }, 100);
  },

  /**
   * Menutup modal berdasarkan ID.
   * Menghapus class 'show' dan membersihkan event listener.
   * 
   * @param {string} modalId - ID elemen modal overlay
   */
  closeModal(modalId) {
    const overlay = document.getElementById(modalId);
    if (!overlay) return;

    overlay.classList.remove('show');
    document.body.style.overflow = '';

    // Bersihkan event listener
    if (overlay._backdropHandler) {
      overlay.removeEventListener('click', overlay._backdropHandler);
    }
    if (overlay._escHandler) {
      document.removeEventListener('keydown', overlay._escHandler);
    }
  },

  /**
   * Menampilkan dialog konfirmasi dalam modal.
   * Mengembalikan Promise yang resolve true (konfirmasi) atau false (batal).
   * 
   * @param {Object} options - Opsi konfirmasi
   * @param {string} options.title - Judul konfirmasi
   * @param {string} options.message - Pesan konfirmasi
   * @param {string} options.type - 'danger', 'warning', atau 'success'
   * @param {string} options.confirmText - Teks tombol konfirmasi
   * @param {string} options.cancelText - Teks tombol batal
   * @returns {Promise<boolean>}
   */
  confirm({ title = 'Konfirmasi', message = 'Apakah Anda yakin?', type = 'danger', confirmText = 'Ya, Lanjutkan', cancelText = 'Batal' } = {}) {
    return new Promise((resolve) => {
      // Map ikon konfirmasi
      const icons = {
        danger: '<i class="ph-bold ph-trash"></i>',
        warning: '<i class="ph-bold ph-warning"></i>',
        success: '<i class="ph-bold ph-check-circle"></i>',
      };

      // Buat overlay modal dinamis
      const overlay = document.createElement('div');
      overlay.className = 'raz-modal-overlay show';
      overlay.innerHTML = `
        <div class="raz-modal modal-sm">
          <div class="raz-modal-body" style="padding-top:32px; padding-bottom:8px;">
            <div class="raz-modal-confirm-icon ${type}">
              ${icons[type] || icons.danger}
            </div>
            <div class="raz-modal-confirm-text">
              <h3>${title}</h3>
              <p>${message}</p>
            </div>
          </div>
          <div class="raz-modal-footer" style="justify-content:center;">
            <button class="raz-btn raz-btn-secondary" id="razConfirmCancel">
              <span class="btn-icon"><i class="ph-bold ph-x"></i></span>
              ${cancelText}
            </button>
            <button class="raz-btn raz-btn-${type}" id="razConfirmOk">
              <span class="btn-icon"><i class="ph-bold ph-check"></i></span>
              ${confirmText}
            </button>
          </div>
        </div>
      `;

      document.body.appendChild(overlay);
      document.body.style.overflow = 'hidden';

      // Fungsi untuk menutup dan menghapus overlay
      const closeConfirm = (result) => {
        overlay.classList.remove('show');
        document.body.style.overflow = '';
        setTimeout(() => overlay.remove(), 250);
        resolve(result);
      };

      // Event listener tombol
      overlay.querySelector('#razConfirmCancel').addEventListener('click', () => closeConfirm(false));
      overlay.querySelector('#razConfirmOk').addEventListener('click', () => closeConfirm(true));
      overlay.addEventListener('click', (e) => { if (e.target === overlay) closeConfirm(false); });
      document.addEventListener('keydown', function escHandler(e) {
        if (e.key === 'Escape') { document.removeEventListener('keydown', escHandler); closeConfirm(false); }
      });
    });
  },

  // ========================
  // API FETCH HELPER
  // ========================

  /**
   * Wrapper untuk fetch API dengan error handling otomatis.
   * Secara default mengirim dan menerima JSON.
   * 
   * @param {string} url - URL endpoint API
   * @param {Object} options - Opsi tambahan
   * @param {string} options.method - HTTP method (GET, POST, PUT, DELETE)
   * @param {Object} options.body - Data yang dikirim (akan di-stringify)
   * @param {boolean} options.showError - Tampilkan toast error otomatis
   * @returns {Promise<Object>} Response JSON
   */
  async api(url, { method = 'GET', body = null, showError = true } = {}) {
    try {
      const options = {
        method,
        headers: { 'Content-Type': 'application/json' },
      };

      // Tambahkan body jika ada
      if (body && method !== 'GET') {
        options.body = JSON.stringify(body);
      }

      const response = await fetch(url, options);
      const data = await response.json();

      // Jika response tidak sukses, tampilkan error
      if (!data.success && showError) {
        this.error('Gagal', data.message || 'Terjadi kesalahan');
      }

      return data;
    } catch (err) {
      console.error('RAZ API Error:', err);
      if (showError) {
        this.error('Koneksi Error', 'Tidak dapat terhubung ke server');
      }
      return { success: false, message: err.message, data: [] };
    }
  },

  /**
   * Upload file menggunakan FormData.
   * 
   * @param {string} url - URL endpoint upload
   * @param {FormData} formData - Data form termasuk file
   * @returns {Promise<Object>} Response JSON
   */
  async upload(url, formData) {
    try {
      const response = await fetch(url, { method: 'POST', body: formData });
      const data = await response.json();
      if (!data.success) this.error('Upload Gagal', data.message);
      return data;
    } catch (err) {
      this.error('Upload Error', 'Gagal mengunggah file');
      return { success: false, message: err.message };
    }
  },

  // ========================
  // FORMAT HELPERS
  // ========================

  /**
   * Format angka menjadi format Rupiah.
   * @param {number} amount - Jumlah uang
   * @returns {string} Format "Rp 150.000"
   */
  formatRupiah(amount) {
    const num = parseFloat(amount) || 0;
    return 'Rp ' + num.toLocaleString('id-ID', { minimumFractionDigits: 0 });
  },

  /**
   * Format angka biasa dengan pemisah ribuan.
   * @param {number} num - Angka
   * @returns {string} Format "150.000"
   */
  formatNumber(num) {
    return (parseFloat(num) || 0).toLocaleString('id-ID');
  },

  /**
   * Parse string rupiah kembali ke angka.
   * Contoh: "Rp 150.000" → 150000
   * @param {string} str - String rupiah
   * @returns {number}
   */
  parseRupiah(str) {
    if (typeof str === 'number') return str;
    return parseInt(String(str).replace(/[^\d]/g, ''), 10) || 0;
  },

  // ========================
  // SIDEBAR TOGGLE
  // ========================

  /**
   * Toggle sidebar antara expanded dan collapsed.
   */
  toggleSidebar() {
    const sidebar = document.querySelector('.raz-sidebar');
    if (sidebar) sidebar.classList.toggle('collapsed');
    // Simpan preferensi ke localStorage
    const isCollapsed = sidebar?.classList.contains('collapsed');
    localStorage.setItem('raz_sidebar_collapsed', isCollapsed ? '1' : '0');
  },

  /**
   * Toggle sidebar di mobile (slide in/out).
   */
  toggleMobileSidebar() {
    const sidebar = document.querySelector('.raz-sidebar');
    if (sidebar) sidebar.classList.toggle('mobile-open');
  },

  /**
   * Inisialisasi sidebar berdasarkan preferensi tersimpan.
   */
  initSidebar() {
    const sidebar = document.querySelector('.raz-sidebar');
    if (!sidebar) return;
    if (localStorage.getItem('raz_sidebar_collapsed') === '1') {
      sidebar.classList.add('collapsed');
    }
  },

  // ========================
  // LOADING STATE TOMBOL
  // ========================

  /**
   * Set tombol ke loading state (spinner + teks berubah).
   * @param {HTMLElement} btn - Elemen tombol
   * @param {string} loadingText - Teks saat loading
   */
  btnLoading(btn, loadingText = 'Menyimpan...') {
    btn.disabled = true;
    btn._originalHTML = btn.innerHTML;
    btn.innerHTML = `<span class="spinner"></span> ${loadingText}`;
  },

  /**
   * Kembalikan tombol ke state normal.
   * @param {HTMLElement} btn - Elemen tombol
   */
  btnReset(btn) {
    btn.disabled = false;
    if (btn._originalHTML) btn.innerHTML = btn._originalHTML;
  },

  // ========================
  // UTILITY LAINNYA
  // ========================

  /**
   * Debounce function — menunda eksekusi fungsi hingga user berhenti mengetik.
   * Berguna untuk search input agar tidak spam API setiap ketikan.
   * 
   * @param {Function} func - Fungsi yang di-debounce
   * @param {number} delay - Delay dalam milidetik
   * @returns {Function}
   */
  debounce(func, delay = 300) {
    let timer;
    return (...args) => {
      clearTimeout(timer);
      timer = setTimeout(() => func.apply(this, args), delay);
    };
  },

  /**
   * Inisialisasi global saat DOM ready.
   * Dipanggil di setiap halaman.
   */
  init() {
    this.initSidebar();

    // Tutup dropdown saat klik di luar
    document.addEventListener('click', (e) => {
      document.querySelectorAll('.raz-dropdown.show').forEach(dd => {
        if (!dd.parentElement.contains(e.target)) {
          dd.classList.remove('show');
        }
      });
    });
  }
};

// Jalankan inisialisasi saat DOM siap
document.addEventListener('DOMContentLoaded', () => RAZ.init());
