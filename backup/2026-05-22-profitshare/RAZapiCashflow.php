<?php
/**
 * ============================================================
 * api/RAZapiCashflow.php — API Keuangan & Kas SIMAJURAZ
 * ============================================================
 * Versi       : 1.0.0
 * Dibuat      : 2026-05-21
 * Diupdate    : 2026-05-21
 * Deskripsi   : Endpoint arus kas, shift, dan ringkasan keuangan.
 * ============================================================
 */
require_once __DIR__ . '/../RAZconfig.php';
require_once __DIR__ . '/../includes/RAZsession.php';
require_once __DIR__ . '/../includes/RAZhelpers.php';

RAZrequireStoreAccess();
$pdo = RAZgetConnection();
if (!$pdo) RAZjsonResponse(false, 'Koneksi database gagal', [], 500);

$user = RAZgetCurrentUser();
$storeId = $user['store_id'];
$action = $_GET['action'] ?? '';

switch ($action) {
    case 'list':       listCashflows($pdo, $storeId); break;
    case 'create':     createCashflow($pdo, $storeId, $user); break;
    case 'delete':     deleteCashflow($pdo, $storeId, $user); break;
    case 'summary':    getSummary($pdo, $storeId); break;
    case 'open_shift': openShift($pdo, $storeId, $user); break;
    case 'close_shift': closeShift($pdo, $storeId, $user); break;
    case 'current_shift': getCurrentShift($pdo, $storeId, $user); break;
    case 'profit_share': getProfitShare($pdo, $storeId); break;
    default: RAZjsonResponse(false, 'Aksi tidak valid', [], 400);
}

// ============================================================
// List Arus Kas dengan filter tanggal & tipe
// ============================================================
function listCashflows($pdo, $storeId) {
    $type = $_GET['type'] ?? '';
    $dateFrom = $_GET['date_from'] ?? date('Y-m-01');
    $dateTo = $_GET['date_to'] ?? date('Y-m-d');
    $page = max(1, intval($_GET['page'] ?? 1));
    $perPage = 15;
    $offset = ($page - 1) * $perPage;

    $where = "WHERE cf.store_id = ? AND DATE(cf.created_at) BETWEEN ? AND ?";
    $params = [$storeId, $dateFrom, $dateTo];

    if ($type === 'income' || $type === 'expense') {
        $where .= " AND cf.type = ?";
        $params[] = $type;
    }

    $countStmt = $pdo->prepare("SELECT COUNT(*) as total FROM cash_flows cf {$where}");
    $countStmt->execute($params);
    $total = $countStmt->fetch()['total'];

    $stmt = $pdo->prepare("SELECT cf.*, u.full_name as user_name FROM cash_flows cf 
        JOIN users u ON cf.user_id = u.id {$where} ORDER BY cf.created_at DESC LIMIT {$perPage} OFFSET {$offset}");
    $stmt->execute($params);

    // Ringkasan
    $sumStmt = $pdo->prepare("SELECT type, COALESCE(SUM(amount),0) as total FROM cash_flows cf {$where} GROUP BY type");
    $sumStmt->execute($params);
    $sums = ['income' => 0, 'expense' => 0];
    foreach ($sumStmt->fetchAll() as $row) $sums[$row['type']] = (float)$row['total'];

    RAZjsonResponse(true, 'OK', [
        'cashflows' => $stmt->fetchAll(),
        'total' => (int)$total,
        'page' => $page,
        'pages' => ceil($total / $perPage),
        'summary' => $sums,
    ]);
}

// ============================================================
// Tambah Arus Kas
// ============================================================
function createCashflow($pdo, $storeId, $user) {
    $input = RAZgetJsonInput();
    $type = $input['type'] ?? '';
    $category = RAZsanitize($input['category'] ?? '');
    $amount = floatval($input['amount'] ?? 0);
    $description = RAZsanitize($input['description'] ?? '');

    if (!in_array($type, ['income', 'expense'])) RAZjsonResponse(false, 'Tipe tidak valid');
    if ($amount <= 0) RAZjsonResponse(false, 'Nominal harus lebih dari 0');
    if (empty($category)) RAZjsonResponse(false, 'Kategori wajib diisi');

    $stmt = $pdo->prepare("INSERT INTO cash_flows (store_id, user_id, type, category, amount, description) VALUES (?,?,?,?,?,?)");
    $stmt->execute([$storeId, $user['id'], $type, $category, $amount, $description]);
    RAZjsonResponse(true, 'Arus kas berhasil dicatat');
}

// ============================================================
// Hapus Arus Kas (Owner Only)
// ============================================================
function deleteCashflow($pdo, $storeId, $user) {
    if ($user['role'] !== 'owner') RAZjsonResponse(false, 'Akses ditolak', [], 403);
    $input = RAZgetJsonInput();
    $id = intval($input['id'] ?? 0);
    $pdo->prepare("DELETE FROM cash_flows WHERE id = ? AND store_id = ?")->execute([$id, $storeId]);
    RAZjsonResponse(true, 'Arus kas berhasil dihapus');
}

// ============================================================
// Ringkasan Keuangan (Laba Rugi)
// ============================================================
function getSummary($pdo, $storeId) {
    $period = $_GET['period'] ?? 'month';
    $dateFrom = $_GET['date_from'] ?? '';
    $dateTo = $_GET['date_to'] ?? '';

    // Tentukan range tanggal berdasarkan periode
    if (!$dateFrom || !$dateTo) {
        switch ($period) {
            case 'today': $dateFrom = $dateTo = date('Y-m-d'); break;
            case 'week': $dateFrom = date('Y-m-d', strtotime('-7 days')); $dateTo = date('Y-m-d'); break;
            case 'month': $dateFrom = date('Y-m-01'); $dateTo = date('Y-m-d'); break;
            case 'year': $dateFrom = date('Y-01-01'); $dateTo = date('Y-m-d'); break;
            default: $dateFrom = date('Y-m-01'); $dateTo = date('Y-m-d');
        }
    }

    // Pendapatan penjualan
    $salesStmt = $pdo->prepare("SELECT COALESCE(SUM(grand_total),0) as total, COUNT(*) as count FROM transactions WHERE store_id=? AND DATE(created_at) BETWEEN ? AND ? AND status='completed'");
    $salesStmt->execute([$storeId, $dateFrom, $dateTo]);
    $sales = $salesStmt->fetch();

    // Total HPP (Cost of Goods Sold)
    $hppStmt = $pdo->prepare("SELECT COALESCE(SUM(ti.hpp * ti.qty),0) as total FROM transaction_items ti JOIN transactions t ON ti.transaction_id=t.id WHERE t.store_id=? AND DATE(t.created_at) BETWEEN ? AND ? AND t.status='completed'");
    $hppStmt->execute([$storeId, $dateFrom, $dateTo]);
    $hpp = $hppStmt->fetch()['total'];

    // Pemasukan lain (kas masuk)
    $incomeStmt = $pdo->prepare("SELECT COALESCE(SUM(amount),0) as total FROM cash_flows WHERE store_id=? AND type='income' AND DATE(created_at) BETWEEN ? AND ?");
    $incomeStmt->execute([$storeId, $dateFrom, $dateTo]);
    $otherIncome = $incomeStmt->fetch()['total'];

    // Pengeluaran (kas keluar)
    $expenseStmt = $pdo->prepare("SELECT COALESCE(SUM(amount),0) as total FROM cash_flows WHERE store_id=? AND type='expense' AND DATE(created_at) BETWEEN ? AND ?");
    $expenseStmt->execute([$storeId, $dateFrom, $dateTo]);
    $expense = $expenseStmt->fetch()['total'];

    $totalRevenue = (float)$sales['total'] + (float)$otherIncome;
    $totalCost = (float)$hpp + (float)$expense;
    $netProfit = $totalRevenue - $totalCost;
    $grossProfit = (float)$sales['total'] - (float)$hpp;

    RAZjsonResponse(true, 'OK', [
        'period'        => ['from' => $dateFrom, 'to' => $dateTo],
        'sales_total'   => (float)$sales['total'],
        'sales_count'   => (int)$sales['count'],
        'hpp_total'     => (float)$hpp,
        'gross_profit'  => $grossProfit,
        'other_income'  => (float)$otherIncome,
        'expense_total' => (float)$expense,
        'total_revenue' => $totalRevenue,
        'total_cost'    => $totalCost,
        'net_profit'    => $netProfit,
    ]);
}

// ============================================================
// Shift Kas
// ============================================================
function openShift($pdo, $storeId, $user) {
    $input = RAZgetJsonInput();
    $openingCash = floatval($input['opening_cash'] ?? 0);
    // Cek apakah sudah ada shift aktif
    $check = $pdo->prepare("SELECT id FROM shifts WHERE store_id=? AND user_id=? AND closed_at IS NULL");
    $check->execute([$storeId, $user['id']]);
    if ($check->fetch()) RAZjsonResponse(false, 'Sudah ada shift aktif. Tutup shift terlebih dahulu.');

    $stmt = $pdo->prepare("INSERT INTO shifts (store_id, user_id, opening_cash) VALUES (?,?,?)");
    $stmt->execute([$storeId, $user['id'], $openingCash]);
    RAZjsonResponse(true, 'Shift dibuka', ['shift_id' => $pdo->lastInsertId()]);
}

function closeShift($pdo, $storeId, $user) {
    $input = RAZgetJsonInput();
    $closingCash = floatval($input['closing_cash'] ?? 0);
    $notes = RAZsanitize($input['notes'] ?? '');

    $stmt = $pdo->prepare("UPDATE shifts SET closing_cash=?, closed_at=datetime('now','localtime'), notes=? WHERE store_id=? AND user_id=? AND closed_at IS NULL");
    $result = $stmt->execute([$closingCash, $notes, $storeId, $user['id']]);
    if ($stmt->rowCount() === 0) RAZjsonResponse(false, 'Tidak ada shift aktif');
    RAZjsonResponse(true, 'Shift ditutup');
}

function getCurrentShift($pdo, $storeId, $user) {
    $stmt = $pdo->prepare("SELECT * FROM shifts WHERE store_id=? AND user_id=? AND closed_at IS NULL ORDER BY opened_at DESC LIMIT 1");
    $stmt->execute([$storeId, $user['id']]);
    $shift = $stmt->fetch();
    RAZjsonResponse(true, 'OK', $shift ?: null);
}

// ============================================================
// Profit Share (Bagi Hasil)
// ============================================================
function getProfitShare($pdo, $storeId) {
    $dateFrom = $_GET['date_from'] ?? date('Y-m-01');
    $dateTo = $_GET['date_to'] ?? date('Y-m-d');
    $percentage = floatval($_GET['percentage'] ?? 50);

    // Hitung laba bersih
    $salesStmt = $pdo->prepare("SELECT COALESCE(SUM(grand_total),0) as total FROM transactions WHERE store_id=? AND DATE(created_at) BETWEEN ? AND ? AND status='completed'");
    $salesStmt->execute([$storeId, $dateFrom, $dateTo]);
    $sales = (float)$salesStmt->fetch()['total'];

    $hppStmt = $pdo->prepare("SELECT COALESCE(SUM(ti.hpp*ti.qty),0) as total FROM transaction_items ti JOIN transactions t ON ti.transaction_id=t.id WHERE t.store_id=? AND DATE(t.created_at) BETWEEN ? AND ? AND t.status='completed'");
    $hppStmt->execute([$storeId, $dateFrom, $dateTo]);
    $hpp = (float)$hppStmt->fetch()['total'];

    $expStmt = $pdo->prepare("SELECT COALESCE(SUM(amount),0) as total FROM cash_flows WHERE store_id=? AND type='expense' AND DATE(created_at) BETWEEN ? AND ?");
    $expStmt->execute([$storeId, $dateFrom, $dateTo]);
    $expense = (float)$expStmt->fetch()['total'];

    $netProfit = $sales - $hpp - $expense;
    $share = round($netProfit * ($percentage / 100));

    RAZjsonResponse(true, 'OK', [
        'net_profit' => $netProfit,
        'percentage' => $percentage,
        'share_amount' => $share,
    ]);
}
