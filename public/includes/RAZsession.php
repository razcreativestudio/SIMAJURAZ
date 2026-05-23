<?php
/**
 * ============================================================
 * RAZsession.php — Session Management & Role Guard
 * ============================================================
 * Versi       : 1.0.0
 * Dibuat      : 2026-05-21
 * Diupdate    : 2026-05-21
 * Deskripsi   : Menangani session PHP untuk autentikasi pengguna.
 *               Menyediakan fungsi guard/middleware untuk memastikan
 *               hanya pengguna dengan role tertentu yang bisa
 *               mengakses halaman tertentu.
 * ============================================================
 */

// Mulai session jika belum dimulai
if (session_status() === PHP_SESSION_NONE) {
    // Konfigurasi session yang lebih aman
    session_set_cookie_params([
        'lifetime' => 86400,    // 24 jam
        'path'     => '/',      // Berlaku di seluruh path
        'httponly' => true,     // Tidak bisa diakses via JavaScript
        'samesite' => 'Lax',   // Proteksi CSRF ringan
    ]);
    session_start();
}

/**
 * Cek apakah pengguna sudah login.
 * 
 * @return bool True jika sudah login
 */
function RAZisLoggedIn() {
    return isset($_SESSION['raz_user_id']) && !empty($_SESSION['raz_user_id']);
}

/**
 * Ambil data user yang sedang login dari session.
 * 
 * @return array|null Data user atau null jika belum login
 */
function RAZgetCurrentUser() {
    if (!RAZisLoggedIn()) {
        return null;
    }
    
    return [
        'id'         => $_SESSION['raz_user_id'],
        'username'   => $_SESSION['raz_username'] ?? '',
        'full_name'  => $_SESSION['raz_full_name'] ?? '',
        'role'       => $_SESSION['raz_role'] ?? '',
        'store_id'   => $_SESSION['raz_store_id'] ?? null,
        'store_name' => $_SESSION['raz_store_name'] ?? '',
        'store_logo' => $_SESSION['raz_store_logo'] ?? '',
    ];
}

/**
 * Simpan data user ke session setelah login berhasil.
 * 
 * @param array $userData Data user dari database
 */
function RAZsetSession($userData) {
    // Regenerate session ID untuk keamanan (cegah session fixation)
    session_regenerate_id(true);
    
    $_SESSION['raz_user_id']    = $userData['id'];
    $_SESSION['raz_username']   = $userData['username'];
    $_SESSION['raz_full_name']  = $userData['full_name'];
    $_SESSION['raz_role']       = $userData['role'];
    $_SESSION['raz_store_id']   = $userData['store_id'] ?? null;
    $_SESSION['raz_store_name'] = $userData['store_name'] ?? '';
    $_SESSION['raz_store_logo'] = $userData['store_logo'] ?? '';
}

/**
 * Hapus session (logout).
 */
function RAZdestroySession() {
    // Hapus semua data session
    $_SESSION = [];
    
    // Hapus cookie session
    if (ini_get('session.use_cookies')) {
        $params = session_get_cookie_params();
        setcookie(
            session_name(), '', time() - 42000,
            $params['path'], $params['domain'],
            $params['secure'], $params['httponly']
        );
    }
    
    // Hancurkan session
    session_destroy();
}

/**
 * Guard/Middleware: Pastikan pengguna sudah login.
 * Jika belum login, redirect ke halaman login.
 */
function RAZrequireLogin() {
    if (!RAZisLoggedIn()) {
        header('Location: RAZlogin.php?error=unauthorized');
        exit;
    }
}

/**
 * Guard/Middleware: Pastikan pengguna memiliki role tertentu.
 * Jika role tidak sesuai, redirect ke dashboard.
 * 
 * @param array $allowedRoles Daftar role yang diizinkan
 */
function RAZrequireRole($allowedRoles = []) {
    RAZrequireLogin(); // Pastikan login dulu
    
    $user = RAZgetCurrentUser();
    
    // Cek apakah role user ada di daftar yang diizinkan
    if (!in_array($user['role'], $allowedRoles)) {
        header('Location: RAZdashboard.php?error=forbidden');
        exit;
    }
}

/**
 * Guard khusus: Hanya Super Admin yang boleh akses.
 */
function RAZrequireSuperAdmin() {
    RAZrequireRole(['superadmin']);
}

/**
 * Guard khusus: Hanya Owner yang boleh akses.
 */
function RAZrequireOwner() {
    RAZrequireRole(['owner']);
}

/**
 * Guard khusus: Owner dan Employee boleh akses (untuk POS, dll).
 */
function RAZrequireStoreAccess() {
    RAZrequireRole(['owner', 'employee']);
}

/**
 * Cek apakah user saat ini adalah role tertentu.
 * 
 * @param string $role Role yang dicek
 * @return bool
 */
function RAZisRole($role) {
    $user = RAZgetCurrentUser();
    return $user && $user['role'] === $role;
}
