<?php
/**
 * ============================================================
 * api/RAZapiSettings.php — API Pengaturan Toko SIMAJURAZ
 * ============================================================
 * Versi       : 1.0.0
 * Dibuat      : 2026-05-21
 * Deskripsi   : Endpoint untuk mengambil dan menyimpan pengaturan
 *               toko (profil, kontak, template struk) serta 
 *               keamanan (password owner).
 * ============================================================
 */
require_once __DIR__ . '/../RAZconfig.php';
require_once __DIR__ . '/../includes/RAZsession.php';
require_once __DIR__ . '/../includes/RAZhelpers.php';

ini_set('display_errors', 0); // Cegah output HTML error yang merusak JSON
error_reporting(E_ALL);

RAZrequireOwner(); // Hanya owner yang bisa akses pengaturan toko
$pdo = RAZgetConnection();
if (!$pdo) RAZjsonResponse(false, 'Koneksi database gagal', [], 500);

$user = RAZgetCurrentUser();
$storeId = $user['store_id'];
$action = $_GET['action'] ?? '';

try {
    switch ($action) {
        case 'get': getSettings($pdo, $storeId); break;
        case 'update_profile': updateProfile($pdo, $storeId); break;
        case 'update_receipt': updateReceipt($pdo, $storeId); break;
        case 'update_password': updatePassword($pdo, $user['id']); break;
        default: RAZjsonResponse(false, 'Aksi tidak valid', [], 400);
    }
} catch (Exception $e) {
    RAZjsonResponse(false, 'Exception: ' . $e->getMessage(), [], 500);
}

// ============================================================
// AMBIL DATA PENGATURAN TOKO
// ============================================================
function getSettings($pdo, $storeId) {
    $stmt = $pdo->prepare("SELECT store_name, store_type, store_description, store_address, store_phone, store_logo, receipt_template, receipt_header, receipt_footer, receipt_show_logo FROM stores WHERE id = ?");
    $stmt->execute([$storeId]);
    $store = $stmt->fetch();
    
    if (!$store) RAZjsonResponse(false, 'Toko tidak ditemukan');
    
    // Normalisasi logo url
    if ($store['store_logo']) {
        $store['store_logo_url'] = 'uploads/logos/' . $store['store_logo'];
    } else {
        $store['store_logo_url'] = null;
    }

    RAZjsonResponse(true, 'Data toko berhasil diambil', $store);
}

// ============================================================
// UPDATE PROFIL TOKO (TERMASUK LOGO)
// ============================================================
function updateProfile($pdo, $storeId) {
    $name = trim($_POST['store_name'] ?? '');
    $type = trim($_POST['store_type'] ?? '');
    $desc = trim($_POST['store_description'] ?? '');
    $phone = trim($_POST['store_phone'] ?? '');
    $address = trim($_POST['store_address'] ?? '');

    if (empty($name)) RAZjsonResponse(false, 'Nama toko wajib diisi');

    // Handle Upload Logo
    $logoFileName = null;
    if (isset($_FILES['store_logo']) && $_FILES['store_logo']['error'] === UPLOAD_ERR_OK) {
        $upload = RAZuploadImage($_FILES['store_logo'], __DIR__ . '/../uploads/logos/', 2); // max 2MB
        if (!$upload['success']) RAZjsonResponse(false, $upload['error']);
        
        $logoFileName = $upload['filename'];
        
        // Hapus logo lama
        $stmt = $pdo->prepare("SELECT store_logo FROM stores WHERE id = ?");
        $stmt->execute([$storeId]);
        $oldLogo = $stmt->fetchColumn();
        if ($oldLogo && file_exists(__DIR__ . '/../uploads/logos/' . $oldLogo)) {
            unlink(__DIR__ . '/../uploads/logos/' . $oldLogo);
        }
    }

    if ($logoFileName) {
        $stmt = $pdo->prepare("UPDATE stores SET store_name=?, store_type=?, store_description=?, store_phone=?, store_address=?, store_logo=? WHERE id=?");
        $stmt->execute([$name, $type, $desc, $phone, $address, $logoFileName, $storeId]);
    } else {
        $stmt = $pdo->prepare("UPDATE stores SET store_name=?, store_type=?, store_description=?, store_phone=?, store_address=? WHERE id=?");
        $stmt->execute([$name, $type, $desc, $phone, $address, $storeId]);
    }

    RAZjsonResponse(true, 'Profil toko berhasil diperbarui');
}

// ============================================================
// UPDATE SETTING STRUK
// ============================================================
function updateReceipt($pdo, $storeId) {
    $json = file_get_contents('php://input');
    $data = json_decode($json, true);
    
    if (!$data) RAZjsonResponse(false, 'Data tidak valid');

    $template = intval($data['receipt_template'] ?? 1);
    $header = trim($data['receipt_header'] ?? '');
    $footer = trim($data['receipt_footer'] ?? '');
    $showLogo = isset($data['receipt_show_logo']) ? (int)$data['receipt_show_logo'] : 1;

    $stmt = $pdo->prepare("UPDATE stores SET receipt_template=?, receipt_header=?, receipt_footer=?, receipt_show_logo=? WHERE id=?");
    $stmt->execute([$template, $header, $footer, $showLogo, $storeId]);

    RAZjsonResponse(true, 'Pengaturan struk berhasil diperbarui');
}

// ============================================================
// UPDATE PASSWORD OWNER
// ============================================================
function updatePassword($pdo, $userId) {
    $json = file_get_contents('php://input');
    $data = json_decode($json, true);

    $currentPw = $data['current_password'] ?? '';
    $newPw = $data['new_password'] ?? '';

    if (empty($currentPw) || empty($newPw)) {
        RAZjsonResponse(false, 'Password lama dan baru wajib diisi');
    }
    if (strlen($newPw) < 6) {
        RAZjsonResponse(false, 'Password baru minimal 6 karakter');
    }

    $stmt = $pdo->prepare("SELECT password FROM users WHERE id = ?");
    $stmt->execute([$userId]);
    $user = $stmt->fetch();

    if (!password_verify($currentPw, $user['password'])) {
        RAZjsonResponse(false, 'Password lama tidak sesuai');
    }

    $hashed = password_hash($newPw, PASSWORD_BCRYPT);
    $update = $pdo->prepare("UPDATE users SET password = ? WHERE id = ?");
    $update->execute([$hashed, $userId]);

    RAZjsonResponse(true, 'Password berhasil diubah');
}
