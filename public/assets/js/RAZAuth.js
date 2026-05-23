/**
 * RAZAuth.js — Logic Autentikasi SIMAJURAZ
 * Versi: 1.0.0 | Dibuat: 2026-05-21
 * Deskripsi: Menangani interaksi form login dan register.
 *            - Tab switching antara login/register
 *            - Validasi form client-side
 *            - Submit form via fetch API (AJAX)
 *            - Toggle visibility password
 */

'use strict';

document.addEventListener('DOMContentLoaded', () => {
  
  // ========================
  // TAB SWITCHING (Login / Register)
  // ========================
  const tabs = document.querySelectorAll('.auth-tab');
  const sections = document.querySelectorAll('.auth-form-section');

  tabs.forEach(tab => {
    tab.addEventListener('click', () => {
      const target = tab.dataset.tab;

      // Update active tab
      tabs.forEach(t => t.classList.remove('active'));
      tab.classList.add('active');

      // Update active section dengan animasi
      sections.forEach(s => s.classList.remove('active'));
      const targetSection = document.getElementById(target);
      if (targetSection) targetSection.classList.add('active');

      // Sembunyikan alert
      hideAlerts();

      // Update header text
      const header = document.querySelector('.auth-form-header h2');
      const desc = document.querySelector('.auth-form-header p');
      if (target === 'loginSection') {
        header.textContent = 'Masuk ke Akun';
        desc.textContent = 'Silakan login dengan akun yang sudah terdaftar.';
      } else {
        header.textContent = 'Daftar Akun Baru';
        desc.textContent = 'Buat akun Owner dan toko Anda sendiri.';
      }
    });
  });

  // ========================
  // TOGGLE PASSWORD VISIBILITY
  // ========================
  document.querySelectorAll('.toggle-password').forEach(btn => {
    btn.addEventListener('click', () => {
      const input = btn.previousElementSibling;
      if (!input) return;

      // Toggle tipe input antara password dan text
      if (input.type === 'password') {
        input.type = 'text';
        btn.innerHTML = '<i class="ph-bold ph-eye-slash"></i>';
      } else {
        input.type = 'password';
        btn.innerHTML = '<i class="ph-bold ph-eye"></i>';
      }
    });
  });

  // ========================
  // FORM LOGIN — Submit Handler
  // ========================
  const loginForm = document.getElementById('loginForm');
  if (loginForm) {
    loginForm.addEventListener('submit', async (e) => {
      e.preventDefault();
      hideAlerts();

      const username = loginForm.querySelector('[name="username"]').value.trim();
      const password = loginForm.querySelector('[name="password"]').value;

      // Validasi client-side
      if (!username || !password) {
        showAlert('loginAlert', 'error', 'Username dan password wajib diisi');
        return;
      }

      // Set tombol ke loading state
      const btn = loginForm.querySelector('.auth-submit');
      setLoading(btn, true, 'Memproses...');

      try {
        // Kirim request ke API
        const response = await fetch('api/RAZauth.php?action=login', {
          method: 'POST',
          headers: { 'Content-Type': 'application/json' },
          body: JSON.stringify({ username, password }),
        });

        const data = await response.json();

        if (data.success) {
          showAlert('loginAlert', 'success', 'Login berhasil! Mengalihkan...');
          // Redirect setelah 1 detik
          setTimeout(() => {
            window.location.href = data.data.redirect;
          }, 1000);
        } else {
          showAlert('loginAlert', 'error', data.message);
          setLoading(btn, false);
        }
      } catch (err) {
        showAlert('loginAlert', 'error', 'Koneksi gagal, periksa server Anda');
        setLoading(btn, false);
      }
    });
  }

  // ========================
  // FORM REGISTER — Submit Handler
  // ========================
  const registerForm = document.getElementById('registerForm');
  if (registerForm) {
    registerForm.addEventListener('submit', async (e) => {
      e.preventDefault();
      hideAlerts();

      const fullName  = registerForm.querySelector('[name="full_name"]').value.trim();
      const username  = registerForm.querySelector('[name="username"]').value.trim();
      const password  = registerForm.querySelector('[name="password"]').value;
      const storeName = registerForm.querySelector('[name="store_name"]').value.trim();

      // Validasi client-side
      if (!fullName || !username || !password || !storeName) {
        showAlert('registerAlert', 'error', 'Semua field wajib diisi');
        return;
      }
      if (password.length < 6) {
        showAlert('registerAlert', 'error', 'Password minimal 6 karakter');
        return;
      }

      // Set tombol ke loading state
      const btn = registerForm.querySelector('.auth-submit');
      setLoading(btn, true, 'Mendaftarkan...');

      try {
        const response = await fetch('api/RAZauth.php?action=register', {
          method: 'POST',
          headers: { 'Content-Type': 'application/json' },
          body: JSON.stringify({ full_name: fullName, username, password, store_name: storeName }),
        });

        const data = await response.json();

        if (data.success) {
          showAlert('registerAlert', 'success', 'Registrasi berhasil! Mengalihkan...');
          setTimeout(() => {
            window.location.href = data.data.redirect;
          }, 1000);
        } else {
          showAlert('registerAlert', 'error', data.message);
          setLoading(btn, false);
        }
      } catch (err) {
        showAlert('registerAlert', 'error', 'Koneksi gagal, periksa server Anda');
        setLoading(btn, false);
      }
    });
  }

  // ========================
  // HELPER FUNCTIONS
  // ========================

  /**
   * Tampilkan alert inline di form.
   * @param {string} alertId - ID elemen alert
   * @param {string} type - 'error' atau 'success'
   * @param {string} message - Pesan yang ditampilkan
   */
  function showAlert(alertId, type, message) {
    const alert = document.getElementById(alertId);
    if (!alert) return;
    alert.className = `auth-alert ${type} show`;
    alert.innerHTML = `<i class="ph-bold ph-${type === 'error' ? 'warning-circle' : 'check-circle'}"></i> ${message}`;
  }

  /** Sembunyikan semua alert */
  function hideAlerts() {
    document.querySelectorAll('.auth-alert').forEach(a => {
      a.classList.remove('show');
    });
  }

  /**
   * Set tombol ke loading atau normal state.
   * @param {HTMLElement} btn - Elemen tombol
   * @param {boolean} loading - True untuk loading state
   * @param {string} text - Teks saat loading
   */
  function setLoading(btn, loading, text = '') {
    if (loading) {
      btn.disabled = true;
      btn._originalHTML = btn.innerHTML;
      btn.innerHTML = `<span class="spinner"></span> ${text}`;
    } else {
      btn.disabled = false;
      if (btn._originalHTML) btn.innerHTML = btn._originalHTML;
    }
  }

  // ========================
  // CEK URL PARAMETER (untuk pesan logout, dll)
  // ========================
  const urlParams = new URLSearchParams(window.location.search);
  if (urlParams.get('msg') === 'logged_out') {
    showAlert('loginAlert', 'success', 'Anda telah berhasil logout');
  }
  if (urlParams.get('error') === 'unauthorized') {
    showAlert('loginAlert', 'error', 'Silakan login terlebih dahulu');
  }
});
