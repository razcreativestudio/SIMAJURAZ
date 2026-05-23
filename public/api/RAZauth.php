<?php
/**
 * ============================================================
 * api/RAZauth.php — API Autentikasi SIMAJURAZ
 * ============================================================
 * Versi       : 1.0.0
 * Dibuat      : 2026-05-21
 * Diupdate    : 2026-05-21
 * Deskripsi   : Endpoint API untuk proses login dan register.
 *               - POST ?action=login   → Login user
 *               - POST ?action=register → Register owner baru
 *               Semua response dalam format JSON.
 * ============================================================
 */

// Muat dependensi
require_once __DIR__ . '/../RAZconfig.php';
require_once __DIR__ . '/../includes/RAZsession.php';
require_once __DIR__ . '/../includes/RAZhelpers.php';

// Pastikan aplikasi sudah terinstall
if (!RAZisInstalled()) {
    RAZjsonResponse(false, 'Aplikasi belum diinstall', [], 503);
}

// Ambil koneksi database
$pdo = RAZgetConnection();
if (!$pdo) {
    RAZjsonResponse(false, 'Koneksi database gagal', [], 500);
}

// Hanya terima request POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    RAZjsonResponse(false, 'Method tidak diizinkan', [], 405);
}

// Tentukan aksi berdasarkan parameter URL
$action = $_GET['action'] ?? '';

switch ($action) {
    case 'login':
        handleLogin($pdo);
        break;
    case 'register':
        handleRegister($pdo);
        break;
    default:
        RAZjsonResponse(false, 'Aksi tidak valid', [], 400);
}

// ============================================================
// FUNGSI: Handle Login
// ============================================================
function handleLogin($pdo) {
    // Ambil data dari request body (JSON)
    $input = RAZgetJsonInput();
    $username = trim($input['username'] ?? '');
    $password = $input['password'] ?? '';

    // Validasi input
    if (empty($username) || empty($password)) {
        RAZjsonResponse(false, 'Username dan password wajib diisi');
        return;
    }

    // Cari user berdasarkan username
    $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ? AND is_active = 1");
    $stmt->execute([$username]);
    $user = $stmt->fetch();

    // Cek apakah user ditemukan
    if (!$user) {
        RAZjsonResponse(false, 'Username tidak ditemukan atau akun nonaktif');
        return;
    }

    // Verifikasi password dengan bcrypt
    if (!password_verify($password, $user['password'])) {
        RAZjsonResponse(false, 'Password salah');
        return;
    }

    // Ambil data toko jika user bukan superadmin
    $storeName = '';
    $storeLogo = '';
    if ($user['store_id']) {
        $storeStmt = $pdo->prepare("SELECT store_name, store_logo FROM stores WHERE id = ?");
        $storeStmt->execute([$user['store_id']]);
        $store = $storeStmt->fetch();
        if ($store) {
            $storeName = $store['store_name'];
            $storeLogo = $store['store_logo'] ?? '';
        }
    }

    // Set session login
    RAZsetSession([
        'id'         => $user['id'],
        'username'   => $user['username'],
        'full_name'  => $user['full_name'],
        'role'       => $user['role'],
        'store_id'   => $user['store_id'],
        'store_name' => $storeName,
        'store_logo' => $storeLogo,
    ]);

    // Log Activity
    logActivity($pdo, $user['id'], $user['store_id'], 'LOGIN_SUCCESS', "User logged in");

    // Tentukan halaman redirect berdasarkan role
    $redirect = 'RAZdashboard.php';
    if ($user['role'] === 'employee') {
        $redirect = 'RAZpos.php'; // Karyawan langsung ke POS
    }

    RAZjsonResponse(true, 'Login berhasil', [
        'redirect' => $redirect,
        'user' => [
            'full_name' => $user['full_name'],
            'role'      => $user['role'],
        ]
    ]);
}

// ============================================================
// FUNGSI: Handle Register (Owner Baru)
// ============================================================
function handleRegister($pdo) {
    // Ambil data dari request body (JSON)
    $input = RAZgetJsonInput();
    
    $fullName  = trim($input['full_name'] ?? '');
    $username  = trim($input['username'] ?? '');
    $password  = $input['password'] ?? '';
    $storeName = trim($input['store_name'] ?? '');

    // Validasi semua field wajib
    $validation = RAZvalidateRequired($input, ['full_name', 'username', 'password', 'store_name']);
    if (!$validation['valid']) {
        RAZjsonResponse(false, 'Field berikut wajib diisi: ' . implode(', ', $validation['missing']));
        return;
    }

    // Validasi panjang password
    if (strlen($password) < 6) {
        RAZjsonResponse(false, 'Password minimal 6 karakter');
        return;
    }

    // Validasi username unik
    $checkStmt = $pdo->prepare("SELECT id FROM users WHERE username = ?");
    $checkStmt->execute([$username]);
    if ($checkStmt->fetch()) {
        RAZjsonResponse(false, 'Username sudah digunakan, pilih yang lain');
        return;
    }

    try {
        // Mulai transaksi database (agar konsisten)
        $pdo->beginTransaction();

        // 1. Buat user Owner
        $hashedPass = password_hash($password, PASSWORD_BCRYPT);
        $userStmt = $pdo->prepare(
            "INSERT INTO users (username, password, full_name, role, is_active) VALUES (?, ?, ?, 'owner', 1)"
        );
        $userStmt->execute([$username, $hashedPass, $fullName]);
        $userId = $pdo->lastInsertId();

        // 2. Buat toko untuk Owner
        $storeStmt = $pdo->prepare(
            "INSERT INTO stores (owner_id, store_name, receipt_footer) VALUES (?, ?, ?)"
        );
        $storeStmt->execute([$userId, $storeName, 'Terima kasih telah berbelanja!']);
        $storeId = $pdo->lastInsertId();

        // 3. Update user dengan store_id
        $updateStmt = $pdo->prepare("UPDATE users SET store_id = ? WHERE id = ?");
        $updateStmt->execute([$storeId, $userId]);

        // 4. Buat kategori default
        $catStmt = $pdo->prepare("INSERT INTO categories (store_id, name, color) VALUES (?, ?, ?)");
        $catStmt->execute([$storeId, 'Umum', '#4F46E5']);
        $catStmt->execute([$storeId, 'Makanan', '#059669']);
        $catStmt->execute([$storeId, 'Minuman', '#0EA5E9']);

        // Commit transaksi
        $pdo->commit();

        // Set session langsung login
        RAZsetSession([
            'id'         => $userId,
            'username'   => $username,
            'full_name'  => $fullName,
            'role'       => 'owner',
            'store_id'   => $storeId,
            'store_name' => $storeName,
            'store_logo' => '',
        ]);

        // Log Activity
        logActivity($pdo, $userId, $storeId, 'REGISTER_NEW_STORE', "Store registered: $storeName");

        RAZjsonResponse(true, 'Registrasi berhasil! Selamat datang.', [
            'redirect' => 'RAZdashboard.php',
        ]);

    } catch (Exception $e) {
        $pdo->rollBack();
        RAZjsonResponse(false, 'Gagal mendaftar: ' . $e->getMessage(), [], 500);
    }
}
