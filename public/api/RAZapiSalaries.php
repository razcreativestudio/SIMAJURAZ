<?php
require_once __DIR__ . '/../RAZconfig.php';
require_once __DIR__ . '/../includes/RAZsession.php';
require_once __DIR__ . '/../includes/RAZhelpers.php';

$user = RAZgetCurrentUser();
if (!$user) RAZjsonResponse(false, 'Sesi tidak valid', [], 401);
$storeId = $user['store_id'];

$pdo = RAZgetConnection();
if (!$pdo) RAZjsonResponse(false, 'Koneksi database gagal', [], 500);

$action = $_GET['action'] ?? '';

switch ($action) {
    case 'list': listSalaries($pdo, $storeId); break;
    case 'save': saveSalary($pdo, $storeId, $user); break;
    case 'delete': deleteSalary($pdo, $storeId, $user); break;
    case 'get': getSalary($pdo, $storeId); break;
    default: RAZjsonResponse(false, 'Aksi tidak valid', [], 400);
}

function listSalaries($pdo, $storeId) {
    $stmt = $pdo->prepare("SELECT s.*, u.full_name as employee_name, u.role as employee_role 
                           FROM salaries s 
                           JOIN users u ON s.user_id = u.id 
                           WHERE s.store_id = ? 
                           ORDER BY s.payment_date DESC, s.created_at DESC");
    $stmt->execute([$storeId]);
    RAZjsonResponse(true, 'OK', $stmt->fetchAll());
}

function getSalary($pdo, $storeId) {
    $id = intval($_GET['id'] ?? 0);
    if (!$id) RAZjsonResponse(false, 'ID tidak valid');

    $stmt = $pdo->prepare("SELECT s.*, u.full_name as employee_name, u.role as employee_role 
                           FROM salaries s 
                           JOIN users u ON s.user_id = u.id 
                           WHERE s.id = ? AND s.store_id = ?");
    $stmt->execute([$id, $storeId]);
    $salary = $stmt->fetch();
    
    if (!$salary) RAZjsonResponse(false, 'Data gaji tidak ditemukan');

    // Get store info
    $stmtStore = $pdo->prepare("SELECT store_name, store_address, store_phone, store_logo FROM stores WHERE id = ?");
    $stmtStore->execute([$storeId]);
    
    RAZjsonResponse(true, 'OK', [
        'salary' => $salary,
        'store' => $stmtStore->fetch()
    ]);
}

function saveSalary($pdo, $storeId, $user) {
    if ($user['role'] !== 'owner') RAZjsonResponse(false, 'Akses ditolak', [], 403);
    
    $input = RAZgetJsonInput();
    $id = intval($input['id'] ?? 0);
    $user_id = intval($input['user_id'] ?? 0);
    $period_type = RAZsanitize($input['period_type'] ?? 'bulanan');
    $base_salary = floatval($input['base_salary'] ?? 0);
    $bonus = floatval($input['bonus'] ?? 0);
    $deduction = floatval($input['deduction'] ?? 0);
    $payment_date = RAZsanitize($input['payment_date'] ?? date('Y-m-d'));
    $notes = RAZsanitize($input['notes'] ?? '');
    
    if ($user_id <= 0) RAZjsonResponse(false, 'Pilih karyawan terlebih dahulu');
    if ($base_salary <= 0) RAZjsonResponse(false, 'Gaji pokok harus lebih dari 0');
    
    $net_salary = $base_salary + $bonus - $deduction;

    // Get employee name for cashflow description
    $stmtEmp = $pdo->prepare("SELECT full_name FROM users WHERE id = ? AND store_id = ?");
    $stmtEmp->execute([$user_id, $storeId]);
    $emp = $stmtEmp->fetch();
    if (!$emp) RAZjsonResponse(false, 'Karyawan tidak valid');
    
    $cfDescription = "Pembayaran Gaji $period_type - " . $emp['full_name'];
    if ($notes) $cfDescription .= " ($notes)";

    $pdo->beginTransaction();
    try {
        if ($id > 0) {
            // Update
            $stmtGet = $pdo->prepare("SELECT cashflow_id FROM salaries WHERE id = ? AND store_id = ?");
            $stmtGet->execute([$id, $storeId]);
            $sal = $stmtGet->fetch();
            
            // Update Cashflow
            if ($sal && $sal['cashflow_id']) {
                $stmtCf = $pdo->prepare("UPDATE cash_flows SET amount = ?, description = ? WHERE id = ? AND store_id = ?");
                $stmtCf->execute([-$net_salary, $cfDescription, $sal['cashflow_id'], $storeId]);
            }
            
            // Update Salary
            $stmt = $pdo->prepare("UPDATE salaries SET user_id=?, period_type=?, base_salary=?, bonus=?, deduction=?, net_salary=?, payment_date=?, notes=? WHERE id=? AND store_id=?");
            $stmt->execute([$user_id, $period_type, $base_salary, $bonus, $deduction, $net_salary, $payment_date, $notes, $id, $storeId]);
            $msg = "Data gaji berhasil diperbarui";
        } else {
            // Insert Cashflow first
            $stmtCf = $pdo->prepare("INSERT INTO cash_flows (store_id, user_id, type, category, amount, description) VALUES (?, ?, 'expense', 'Gaji Karyawan', ?, ?)");
            $stmtCf->execute([$storeId, $user['id'], -$net_salary, $cfDescription]);
            $cfId = $pdo->lastInsertId();
            
            // Insert Salary
            $stmt = $pdo->prepare("INSERT INTO salaries (store_id, user_id, period_type, base_salary, bonus, deduction, net_salary, payment_date, notes, cashflow_id, created_by) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([$storeId, $user_id, $period_type, $base_salary, $bonus, $deduction, $net_salary, $payment_date, $notes, $cfId, $user['id']]);
            $msg = "Gaji berhasil dicatat dan dipotong dari kas toko";
        }
        $pdo->commit();
        RAZjsonResponse(true, $msg);
    } catch (Exception $e) {
        $pdo->rollBack();
        RAZjsonResponse(false, 'Terjadi kesalahan: ' . $e->getMessage());
    }
}

function deleteSalary($pdo, $storeId, $user) {
    if ($user['role'] !== 'owner') RAZjsonResponse(false, 'Akses ditolak', [], 403);
    
    $input = RAZgetJsonInput();
    $id = intval($input['id'] ?? 0);
    
    $stmtGet = $pdo->prepare("SELECT cashflow_id FROM salaries WHERE id = ? AND store_id = ?");
    $stmtGet->execute([$id, $storeId]);
    $sal = $stmtGet->fetch();
    
    $pdo->beginTransaction();
    try {
        if ($sal && $sal['cashflow_id']) {
            $pdo->prepare("DELETE FROM cash_flows WHERE id = ? AND store_id = ?")->execute([$sal['cashflow_id'], $storeId]);
        }
        $pdo->prepare("DELETE FROM salaries WHERE id = ? AND store_id = ?")->execute([$id, $storeId]);
        
        $pdo->commit();
        RAZjsonResponse(true, 'Data gaji dan riwayat arus kas terkait berhasil dihapus');
    } catch (Exception $e) {
        $pdo->rollBack();
        RAZjsonResponse(false, 'Gagal menghapus data: ' . $e->getMessage());
    }
}
?>
