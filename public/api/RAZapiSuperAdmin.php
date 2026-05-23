<?php
require_once __DIR__ . '/../RAZconfig.php';
require_once __DIR__ . '/../includes/RAZsession.php';
require_once __DIR__ . '/../includes/RAZhelpers.php';

RAZrequireRole(['superadmin'], true);

$pdo = RAZgetConnection();
$action = $_GET['action'] ?? '';

switch ($action) {
    case 'toggle_ban':
        toggleBan($pdo);
        break;
    case 'reset_password':
        resetPassword($pdo);
        break;
    case 'delete_user':
        deleteUser($pdo);
        break;
    default:
        RAZjsonResponse(false, 'Aksi tidak dikenali', [], 400);
}

function toggleBan($pdo) {
    $input = RAZgetJsonInput();
    $userId = $input['user_id'] ?? 0;
    $isActive = $input['is_active'] ?? 1;

    $stmt = $pdo->prepare("UPDATE users SET is_active = ? WHERE id = ? AND role != 'superadmin'");
    if ($stmt->execute([$isActive, $userId])) {
        $statusStr = $isActive == 1 ? "UNBANNED" : "BANNED";
        logActivity($pdo, $_SESSION['raz_user_id'] ?? null, null, "SUPERADMIN_USER_STATUS", "User ID $userId set to $statusStr");
        RAZjsonResponse(true, "Status pengguna berhasil diperbarui.");
    } else {
        RAZjsonResponse(false, "Gagal memperbarui status.");
    }
}

function resetPassword($pdo) {
    $input = RAZgetJsonInput();
    $userId = $input['user_id'] ?? 0;
    $newPassword = $input['new_password'] ?? '';

    if (strlen($newPassword) < 6) {
        RAZjsonResponse(false, "Password minimal 6 karakter.");
        return;
    }

    $hashedPass = password_hash($newPassword, PASSWORD_BCRYPT);
    $stmt = $pdo->prepare("UPDATE users SET password = ? WHERE id = ? AND role != 'superadmin'");
    
    if ($stmt->execute([$hashedPass, $userId])) {
        logActivity($pdo, $_SESSION['raz_user_id'] ?? null, null, "SUPERADMIN_RESET_PASSWORD", "Reset password for User ID $userId");
        RAZjsonResponse(true, "Password berhasil di-reset secara paksa.");
    } else {
        RAZjsonResponse(false, "Gagal me-reset password.");
    }
}

function deleteUser($pdo) {
    $input = RAZgetJsonInput();
    $userId = $input['user_id'] ?? 0;

    $stmt = $pdo->prepare("DELETE FROM users WHERE id = ? AND role != 'superadmin'");
    if ($stmt->execute([$userId])) {
        logActivity($pdo, $_SESSION['raz_user_id'] ?? null, null, "SUPERADMIN_DELETE_USER", "Deleted User ID $userId");
        RAZjsonResponse(true, "Pengguna berhasil dihapus secara permanen.");
    } else {
        RAZjsonResponse(false, "Gagal menghapus pengguna.");
    }
}
?>
