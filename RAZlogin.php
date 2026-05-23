<?php
/**
 * ============================================================
 * index.php — Halaman Landing & Autentikasi SIMAJURAZ
 * ============================================================
 * Versi       : 1.0.0
 * Dibuat      : 2026-05-21
 * Diupdate    : 2026-05-21
 * Deskripsi   : Halaman utama SIMAJURAZ yang menampilkan form
 *               Login dan Register. Jika belum install, redirect
 *               ke wizard instalasi. Jika sudah login, redirect
 *               ke dashboard atau POS sesuai role.
 * ============================================================
 */

require_once __DIR__ . '/RAZconfig.php';
require_once __DIR__ . '/includes/RAZsession.php';

// Cek apakah sudah diinstall, jika belum redirect ke installer
if (!RAZisInstalled()) {
    header('Location: RAZinstall.php');
    exit;
}

// Jika sudah login, redirect ke halaman sesuai role
if (RAZisLoggedIn()) {
    $user = RAZgetCurrentUser();
    if ($user['role'] === 'employee') {
        header('Location: RAZpos.php');
    } else {
        header('Location: RAZdashboard.php');
    }
    exit;
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SIMAJURAZ — Sistem Manajemen Jualan</title>
    <meta name="description" content="SIMAJURAZ: Aplikasi Point of Sale multi-tenant oleh RAZ Creative Studio. Kelola toko, inventori, dan keuangan Anda.">

    <!-- Phosphor Icons (Library ikon modern) -->
    <link rel="stylesheet" href="https://unpkg.com/@phosphor-icons/web@2.1.1/src/bold/style.css">
    
    <!-- CSS Design System -->
    <link rel="stylesheet" href="assets/css/RAZMain.css">
    <link rel="stylesheet" href="assets/css/RAZAuth.css">
</head>
<body>
    <div class="auth-page">
        <!-- Efek blob background animasi -->
        <div class="auth-bg-blob blob-1"></div>
        <div class="auth-bg-blob blob-2"></div>
        <div class="auth-bg-blob blob-3"></div>

        <div class="auth-container">
            <!-- ============================
                 PANEL KIRI — Branding
                 ============================ -->
            <div class="auth-brand">
                <div class="auth-brand-content">
                    <div class="auth-brand-logo-custom" style="width:100%; max-width:280px; margin-bottom: 24px;">
                        <img src="assets/images/logo.svg" alt="SIMAJURAZ Logo" style="width: 100%; height: auto; filter: drop-shadow(0 4px 6px rgba(0,0,0,0.1));">
                    </div>
                    <p class="tagline">
                        Sistem Manajemen Jualan modern untuk mengelola toko Anda. 
                        Dari kasir, inventori, hingga laporan keuangan dalam satu platform.
                    </p>
                    <ul class="auth-features">
                        <li>
                            <i class="ph-bold ph-shopping-cart"></i>
                            <span>Point of Sale cepat & responsif</span>
                        </li>
                        <li>
                            <i class="ph-bold ph-package"></i>
                            <span>Manajemen inventori & stok otomatis</span>
                        </li>
                        <li>
                            <i class="ph-bold ph-chart-line-up"></i>
                            <span>Laporan keuangan & ekspor PDF</span>
                        </li>
                        <li>
                            <i class="ph-bold ph-users-three"></i>
                            <span>Multi-user dengan hak akses</span>
                        </li>
                    </ul>
                </div>
            </div>

            <!-- ============================
                 PANEL KANAN — Form Login/Register
                 ============================ -->
            <div class="auth-form-panel">
                <div class="auth-form-header">
                    <h2>Masuk ke Akun</h2>
                    <p>Silakan login dengan akun yang sudah terdaftar.</p>
                </div>

                <!-- Tab Login / Register -->
                <div class="auth-tabs">
                    <button type="button" class="auth-tab active" data-tab="loginSection">
                        <i class="ph-bold ph-sign-in"></i> Masuk
                    </button>
                    <button type="button" class="auth-tab" data-tab="registerSection">
                        <i class="ph-bold ph-user-plus"></i> Daftar
                    </button>
                </div>

                <!-- ========== FORM LOGIN ========== -->
                <div class="auth-form-section active" id="loginSection">
                    <div class="auth-alert" id="loginAlert"></div>

                    <form class="auth-form" id="loginForm">
                        <div class="raz-form-group">
                            <label class="raz-form-label">Username <span class="required">*</span></label>
                            <div class="auth-input-wrapper">
                                <span class="input-icon-left"><i class="ph-bold ph-user"></i></span>
                                <input type="text" name="username" class="raz-form-input" 
                                       placeholder="Masukkan username" autocomplete="username" required>
                            </div>
                        </div>

                        <div class="raz-form-group">
                            <label class="raz-form-label">Password <span class="required">*</span></label>
                            <div class="auth-input-wrapper">
                                <span class="input-icon-left"><i class="ph-bold ph-lock"></i></span>
                                <input type="password" name="password" class="raz-form-input" 
                                       placeholder="Masukkan password" autocomplete="current-password" required>
                                <button type="button" class="toggle-password">
                                    <i class="ph-bold ph-eye"></i>
                                </button>
                            </div>
                        </div>

                        <button type="submit" class="auth-submit">
                            <i class="ph-bold ph-sign-in"></i> Masuk
                        </button>
                    </form>
                </div>

                <!-- ========== FORM REGISTER ========== -->
                <div class="auth-form-section" id="registerSection">
                    <div class="auth-alert" id="registerAlert"></div>

                    <form class="auth-form" id="registerForm">
                        <div class="raz-form-group">
                            <label class="raz-form-label">Nama Lengkap <span class="required">*</span></label>
                            <div class="auth-input-wrapper">
                                <span class="input-icon-left"><i class="ph-bold ph-identification-card"></i></span>
                                <input type="text" name="full_name" class="raz-form-input" 
                                       placeholder="Nama lengkap Anda" required>
                            </div>
                        </div>

                        <div class="raz-form-group">
                            <label class="raz-form-label">Username <span class="required">*</span></label>
                            <div class="auth-input-wrapper">
                                <span class="input-icon-left"><i class="ph-bold ph-user"></i></span>
                                <input type="text" name="username" class="raz-form-input" 
                                       placeholder="Pilih username" autocomplete="username" required>
                            </div>
                        </div>

                        <div class="raz-form-group">
                            <label class="raz-form-label">Password <span class="required">*</span> 
                                <small style="color:var(--raz-text-muted)">(min. 6 karakter)</small>
                            </label>
                            <div class="auth-input-wrapper">
                                <span class="input-icon-left"><i class="ph-bold ph-lock"></i></span>
                                <input type="password" name="password" class="raz-form-input" 
                                       placeholder="Buat password" minlength="6" required>
                                <button type="button" class="toggle-password">
                                    <i class="ph-bold ph-eye"></i>
                                </button>
                            </div>
                        </div>

                        <div class="auth-divider">Informasi Toko</div>

                        <div class="raz-form-group">
                            <label class="raz-form-label">Nama Toko <span class="required">*</span></label>
                            <div class="auth-input-wrapper">
                                <span class="input-icon-left"><i class="ph-bold ph-storefront"></i></span>
                                <input type="text" name="store_name" class="raz-form-input" 
                                       placeholder="Contoh: Toko Berkah Jaya" required>
                            </div>
                        </div>

                        <button type="submit" class="auth-submit">
                            <i class="ph-bold ph-rocket-launch"></i> Daftar & Mulai
                        </button>
                    </form>
                </div>

                <!-- Footer -->
                <div class="auth-form-footer">
                    <p>SIMAJURAZ v<?= RAZ_VERSION ?> — RAZ Creative Studio © <?= date('Y') ?></p>
                </div>
            </div>
        </div>
    </div>

    <!-- JavaScript -->
    <script src="assets/js/RAZMain.js"></script>
    <script src="assets/js/RAZAuth.js"></script>
</body>
</html>
