<?php
/**
 * ============================================================
 * api/RAZapiUsers.php — API Manajemen Karyawan SIMAJURAZ
 * ============================================================
 * Versi       : 1.0.0
 * Dibuat      : 2026-05-21
 * Diupdate    : 2026-05-21
 * Deskripsi   : CRUD karyawan oleh Owner. Employee hanya bisa
 *               dikelola oleh Owner toko yang sama (multi-tenant).
 * ============================================================
 */
require_once __DIR__ . '/../RAZconfig.php';
require_once __DIR__ . '/../includes/RAZsession.php';
require_once __DIR__ . '/../includes/RAZhelpers.php';

RAZrequireOwner(); // Hanya Owner yang boleh akses
$pdo = RAZgetConnection();
if (!$pdo) RAZjsonResponse(false, 'Koneksi database gagal', [], 500);

$user = RAZgetCurrentUser();
$storeId = $user['store_id'];
$action = $_GET['action'] ?? '';

switch ($action) {
    case 'list':    listUsers($pdo, $storeId); break;
    case 'get':     getUser($pdo, $storeId); break;
    case 'create':  createUser($pdo, $storeId); break;
    case 'update':  updateUser($pdo, $storeId); break;
    case 'toggle':  toggleUser($pdo, $storeId); break;
    case 'delete':  deleteUser($pdo, $storeId); break;
    case 'reset_pw': resetPassword($pdo, $storeId); break;
    case 'profile':  getProfile($pdo, $user); break;
    case 'update_profile': updateProfile($pdo, $user); break;
    default: RAZjsonResponse(false, 'Aksi tidak valid', [], 400);
}

// ============================================================
// Daftar Karyawan (hanya milik toko ini)
// ============================================================
function listUsers($pdo, $storeId) {
    $search = $_GET['search'] ?? '';

    $where = "WHERE u.store_id = ? AND u.role = 'employee'";
    $params = [$storeId];

    if ($search) {
        $where .= " AND (u.full_name LIKE ? OR u.username LIKE ?)";
        $params[] = "%{$search}%";
        $params[] = "%{$search}%";
    }

    $stmt = $pdo->prepare("SELECT u.id, u.username, u.full_name, u.role, u.is_active, u.created_at 
                           FROM users u {$where} ORDER BY u.created_at DESC");
    $stmt->execute($params);

    // Hitung statistik
    $totalStmt = $pdo->prepare("SELECT COUNT(*) as total, SUM(CASE WHEN is_active=1 THEN 1 ELSE 0 END) as active FROM users WHERE store_id = ? AND role = 'employee'");
    $totalStmt->execute([$storeId]);
    $stats = $totalStmt->fetch();

    RAZjsonResponse(true, 'OK', [
        'users' => $stmt->fetchAll(),
        'total' => (int)$stats['total'],
        'active' => (int)$stats['active'],
    ]);
}

// ============================================================
// Detail Karyawan
// ============================================================
function getUser($pdo, $storeId) {
    $id = intval($_GET['id'] ?? 0);
    $stmt = $pdo->prepare("SELECT id, username, full_name, role, is_active, created_at FROM users WHERE id = ? AND store_id = ? AND role = 'employee'");
    $stmt->execute([$id, $storeId]);
    $u = $stmt->fetch();
    if (!$u) RAZjsonResponse(false, 'Karyawan tidak ditemukan');
    RAZjsonResponse(true, 'OK', $u);
}

// ============================================================
// Tambah Karyawan
// ============================================================
function createUser($pdo, $storeId) {
    $input = RAZgetJsonInput();
    $fullName = RAZsanitize($input['full_name'] ?? '');
    $username = RAZsanitize($input['username'] ?? '');
    $password = $input['password'] ?? '';

    // Validasi
    if (empty($fullName) || empty($username) || empty($password)) {
        RAZjsonResponse(false, 'Nama, username, dan password wajib diisi');
    }
    if (strlen($password) < 6) RAZjsonResponse(false, 'Password minimal 6 karakter');

    // Cek username unik
    $check = $pdo->prepare("SELECT id FROM users WHERE username = ?");
    $check->execute([$username]);
    if ($check->fetch()) RAZjsonResponse(false, 'Username sudah digunakan');

    $hashed = password_hash($password, PASSWORD_BCRYPT);
    $stmt = $pdo->prepare("INSERT INTO users (store_id, username, password, full_name, role, is_active) VALUES (?, ?, ?, ?, 'employee', 1)");
    $stmt->execute([$storeId, $username, $hashed, $fullName]);

    RAZjsonResponse(true, 'Karyawan berhasil ditambahkan', ['id' => $pdo->lastInsertId()]);
}

// ============================================================
// Update Karyawan
// ============================================================
function updateUser($pdo, $storeId) {
    $input = RAZgetJsonInput();
    $id = intval($input['id'] ?? 0);
    $fullName = RAZsanitize($input['full_name'] ?? '');

    if (!$id || empty($fullName)) RAZjsonResponse(false, 'Data tidak lengkap');

    // Pastikan karyawan milik toko ini
    $check = $pdo->prepare("SELECT id FROM users WHERE id = ? AND store_id = ? AND role = 'employee'");
    $check->execute([$id, $storeId]);
    if (!$check->fetch()) RAZjsonResponse(false, 'Karyawan tidak ditemukan');

    $stmt = $pdo->prepare("UPDATE users SET full_name = ? WHERE id = ?");
    $stmt->execute([$fullName, $id]);
    RAZjsonResponse(true, 'Data karyawan berhasil diperbarui');
}

// ============================================================
// Toggle Aktif/Nonaktif Karyawan
// ============================================================
function toggleUser($pdo, $storeId) {
    $input = RAZgetJsonInput();
    $id = intval($input['id'] ?? 0);

    $stmt = $pdo->prepare("SELECT is_active FROM users WHERE id = ? AND store_id = ? AND role = 'employee'");
    $stmt->execute([$id, $storeId]);
    $u = $stmt->fetch();
    if (!$u) RAZjsonResponse(false, 'Karyawan tidak ditemukan');

    $newStatus = $u['is_active'] ? 0 : 1;
    $pdo->prepare("UPDATE users SET is_active = ? WHERE id = ?")->execute([$newStatus, $id]);
    RAZjsonResponse(true, $newStatus ? 'Karyawan diaktifkan' : 'Karyawan dinonaktifkan');
}

// ============================================================
// Hapus Karyawan (Permanen)
// ============================================================
function deleteUser($pdo, $storeId) {
    $input = RAZgetJsonInput();
    $id = intval($input['id'] ?? 0);

    $check = $pdo->prepare("SELECT id FROM users WHERE id = ? AND store_id = ? AND role = 'employee'");
    $check->execute([$id, $storeId]);
    if (!$check->fetch()) RAZjsonResponse(false, 'Karyawan tidak ditemukan');

    $pdo->prepare("DELETE FROM users WHERE id = ? AND store_id = ?")->execute([$id, $storeId]);
    RAZjsonResponse(true, 'Karyawan berhasil dihapus');
}

// ============================================================
// Reset Password Karyawan
// ============================================================
function resetPassword($pdo, $storeId) {
    $input = RAZgetJsonInput();
    $id = intval($input['id'] ?? 0);
    $newPassword = $input['new_password'] ?? '';

    if (strlen($newPassword) < 6) RAZjsonResponse(false, 'Password baru minimal 6 karakter');

    $check = $pdo->prepare("SELECT id FROM users WHERE id = ? AND store_id = ? AND role = 'employee'");
    $check->execute([$id, $storeId]);
    if (!$check->fetch()) RAZjsonResponse(false, 'Karyawan tidak ditemukan');

    $hashed = password_hash($newPassword, PASSWORD_BCRYPT);
    $pdo->prepare("UPDATE users SET password = ? WHERE id = ?")->execute([$hashed, $id]);
    RAZjsonResponse(true, 'Password berhasil direset');
}

// ============================================================
// Profil Owner (Lihat & Update sendiri)
// ============================================================
function getProfile($pdo, $user) {
    $stmt = $pdo->prepare("SELECT id, username, full_name, role, created_at FROM users WHERE id = ?");
    $stmt->execute([$user['id']]);
    RAZjsonResponse(true, 'OK', $stmt->fetch());
}

function updateProfile($pdo, $user) {
    $input = RAZgetJsonInput();
    $fullName = RAZsanitize($input['full_name'] ?? '');
    $currentPw = $input['current_password'] ?? '';
    $newPw = $input['new_password'] ?? '';

    if (empty($fullName)) RAZjsonResponse(false, 'Nama lengkap wajib diisi');

    // Update nama
    $pdo->prepare("UPDATE users SET full_name = ? WHERE id = ?")->execute([$fullName, $user['id']]);
    $_SESSION['raz_full_name'] = $fullName;

    // Update password jika diisi
    if ($newPw) {
        if (strlen($newPw) < 6) RAZjsonResponse(false, 'Password baru minimal 6 karakter');
        // Verifikasi password lama
        $stmt = $pdo->prepare("SELECT password FROM users WHERE id = ?");
        $stmt->execute([$user['id']]);
        $u = $stmt->fetch();
        if (!password_verify($currentPw, $u['password'])) RAZjsonResponse(false, 'Password lama salah');

        $hashed = password_hash($newPw, PASSWORD_BCRYPT);
        $pdo->prepare("UPDATE users SET password = ? WHERE id = ?")->execute([$hashed, $user['id']]);
    }

    RAZjsonResponse(true, 'Profil berhasil diperbarui');
}
