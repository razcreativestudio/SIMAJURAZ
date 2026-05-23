<?php
/**
 * ============================================================
 * api/RAZapiCashflow.php â€” API Keuangan & Kas SIMAJURAZ
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
    // Profit Sharing (Bagi Hasil)
    case 'profit_share':            getProfitShareCalc($pdo, $storeId); break;
    case 'profit_shares_list':      listProfitShares($pdo, $storeId); break;
    case 'profit_shares_add':       addProfitShare($pdo, $storeId, $user); break;
    case 'profit_shares_update':    updateProfitShares($pdo, $storeId, $user); break;
    case 'profit_shares_delete':    deleteProfitShare($pdo, $storeId, $user); break;
    case 'profit_share_report':     generateProfitReport($pdo, $storeId, $user); break;
    case 'profit_share_reports':    listProfitReports($pdo, $storeId); break;
    // Capital & Spoilages
    case 'capital_list':            listCapitalFlows($pdo, $storeId); break;
    case 'capital_add':             addCapitalFlow($pdo, $storeId, $user); break;
    case 'capital_delete':          deleteCapitalFlow($pdo, $storeId, $user); break;
    case 'spoilage_list':           listSpoilages($pdo, $storeId); break;
    case 'spoilage_add':            addSpoilage($pdo, $storeId, $user); break;
    case 'spoilage_delete':         deleteSpoilage($pdo, $storeId, $user); break;
    case 'additional_expense_list': listAdditionalExpenses($pdo, $storeId); break;
    default: RAZjsonResponse(false, 'Aksi tidak valid', [], 400);
}

// ============================================================
// List Pengeluaran Tambahan (Deduct from Share)
// ============================================================
function listAdditionalExpenses($pdo, $storeId) {
    $stmt = $pdo->prepare("SELECT cf.*, ps.name as share_name FROM cash_flows cf 
                           LEFT JOIN profit_shares ps ON cf.deduct_from_share_id = ps.id 
                           WHERE cf.store_id = ? AND cf.type = 'expense' AND cf.deduct_from_share_id > 0 
                           ORDER BY cf.created_at DESC");
    $stmt->execute([$storeId]);
    RAZjsonResponse(true, 'OK', $stmt->fetchAll());
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
    $deductFromShareId = !empty($input['deduct_from_share_id']) ? (int)$input['deduct_from_share_id'] : null;

    if (!in_array($type, ['income', 'expense'])) RAZjsonResponse(false, 'Tipe tidak valid');
    if ($amount <= 0) RAZjsonResponse(false, 'Nominal harus lebih dari 0');
    if (empty($category)) RAZjsonResponse(false, 'Kategori wajib diisi');

    $id = intval($input['id'] ?? 0);
    if ($id > 0) {
        $stmt = $pdo->prepare("UPDATE cash_flows SET type=?, category=?, amount=?, description=?, deduct_from_share_id=? WHERE id=? AND store_id=?");
        $stmt->execute([$type, $category, $amount, $description, $deductFromShareId, $id, $storeId]);
        RAZjsonResponse(true, 'Arus kas berhasil diperbarui');
    } else {
        $stmt = $pdo->prepare("INSERT INTO cash_flows (store_id, user_id, type, category, amount, description, deduct_from_share_id) VALUES (?,?,?,?,?,?,?)");
        $stmt->execute([$storeId, $user['id'], $type, $category, $amount, $description, $deductFromShareId]);
        RAZjsonResponse(true, 'Arus kas berhasil dicatat');
    }
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

    // Pengeluaran (kas keluar yang tidak teratribusi ke share)
    $expenseStmt = $pdo->prepare("SELECT COALESCE(SUM(amount),0) as total FROM cash_flows WHERE store_id=? AND type='expense' AND deduct_from_share_id IS NULL AND DATE(created_at) BETWEEN ? AND ?");
    $expenseStmt->execute([$storeId, $dateFrom, $dateTo]);
    $expense = $expenseStmt->fetch()['total'];

    // Capital Flows
    $stmtCap = $pdo->prepare("SELECT 
        COALESCE(SUM(CASE WHEN type='in' THEN amount ELSE 0 END), 0) as cap_in,
        COALESCE(SUM(CASE WHEN type='out' THEN amount ELSE 0 END), 0) as cap_out 
        FROM capital_flows WHERE store_id=? AND DATE(created_at) BETWEEN ? AND ?");
    $stmtCap->execute([$storeId, $dateFrom, $dateTo]);
    $cap = $stmtCap->fetch();
    $capitalIn = (float)$cap['cap_in'];
    $capitalOut = (float)$cap['cap_out'];
    
    // Spoilages
    $stmtSpoil = $pdo->prepare("SELECT COALESCE(SUM(total_loss),0) as total FROM spoilages WHERE store_id=? AND DATE(created_at) BETWEEN ? AND ?");
    $stmtSpoil->execute([$storeId, $dateFrom, $dateTo]);
    $spoilages = (float)$stmtSpoil->fetch()['total'];

    $netCapital = $capitalIn - $capitalOut;
    $remainingCapital = $netCapital - $hpp - $spoilages;

    $totalRevenue = (float)$sales['total'] + (float)$otherIncome;
    $totalCost = (float)$hpp + (float)$expense + $spoilages;
    $netProfit = $totalRevenue - $totalCost;
    $grossProfit = (float)$sales['total'] - (float)$hpp;

    RAZjsonResponse(true, 'OK', [
        'period'        => ['from' => $dateFrom, 'to' => $dateTo],
        'sales_total'   => (float)$sales['total'],
        'sales_count'   => (int)$sales['count'],
        'hpp_total'     => (float)$hpp,
        'spoilages'     => $spoilages,
        'gross_profit'  => $grossProfit,
        'other_income'  => (float)$otherIncome,
        'expense_total' => (float)$expense,
        'capital_in'    => $capitalIn,
        'capital_remain'=> $remainingCapital,
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

    $stmt = $pdo->prepare("UPDATE shifts SET closing_cash=?, closed_at=?, notes=? WHERE store_id=? AND user_id=? AND closed_at IS NULL");
    $result = $stmt->execute([$closingCash, date('Y-m-d H:i:s'), $notes, $storeId, $user['id']]);
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
// PROFIT SHARING â€” Hitung Keuntungan Bersih untuk Distribusi
// ============================================================

/**
 * Helper: Hitung keuntungan bersih berdasarkan periode
 * Total Pendapatan - (HPP + Pengeluaran) = Keuntungan Bersih
 */
function calcNetProfit($pdo, $storeId, $dateFrom, $dateTo) {
    // Total penjualan
    $stmt = $pdo->prepare("SELECT COALESCE(SUM(grand_total),0) as total FROM transactions WHERE store_id=? AND DATE(created_at) BETWEEN ? AND ? AND status='completed'");
    $stmt->execute([$storeId, $dateFrom, $dateTo]);
    $sales = (float)$stmt->fetch()['total'];

    // Total HPP
    $stmt = $pdo->prepare("SELECT COALESCE(SUM(ti.hpp*ti.qty),0) as total FROM transaction_items ti JOIN transactions t ON ti.transaction_id=t.id WHERE t.store_id=? AND DATE(t.created_at) BETWEEN ? AND ? AND t.status='completed'");
    $stmt->execute([$storeId, $dateFrom, $dateTo]);
    $hpp = (float)$stmt->fetch()['total'];

    // Total Spoilages (Barang Rusak)
    $stmt = $pdo->prepare("SELECT COALESCE(SUM(total_loss),0) as total FROM spoilages WHERE store_id=? AND DATE(created_at) BETWEEN ? AND ?");
    $stmt->execute([$storeId, $dateFrom, $dateTo]);
    $spoilages = (float)$stmt->fetch()['total'];

    // Pemasukan lain (kas masuk)
    $stmt = $pdo->prepare("SELECT COALESCE(SUM(amount),0) as total FROM cash_flows WHERE store_id=? AND type='income' AND DATE(created_at) BETWEEN ? AND ?");
    $stmt->execute([$storeId, $dateFrom, $dateTo]);
    $otherIncome = (float)$stmt->fetch()['total'];

    // Pengeluaran Umum (kas keluar yang TIDAK teratribusi ke share spesifik)
    $stmt = $pdo->prepare("SELECT COALESCE(SUM(amount),0) as total FROM cash_flows WHERE store_id=? AND type='expense' AND deduct_from_share_id IS NULL AND DATE(created_at) BETWEEN ? AND ?");
    $stmt->execute([$storeId, $dateFrom, $dateTo]);
    $expense = (float)$stmt->fetch()['total'];

    // Capital Flows (Modal)
    $stmt = $pdo->prepare("SELECT 
        COALESCE(SUM(CASE WHEN type='in' THEN amount ELSE 0 END), 0) as cap_in,
        COALESCE(SUM(CASE WHEN type='out' THEN amount ELSE 0 END), 0) as cap_out 
        FROM capital_flows WHERE store_id=? AND DATE(created_at) BETWEEN ? AND ?");
    $stmt->execute([$storeId, $dateFrom, $dateTo]);
    $cap = $stmt->fetch();
    $capitalIn = (float)$cap['cap_in'];
    $capitalOut = (float)$cap['cap_out'];
    $netCapital = $capitalIn - $capitalOut;

    // Remaining Capital (Sisa Modal = Modal Bersih - HPP Terpakai - Kerugian Rusak)
    $remainingCapital = $netCapital - $hpp - $spoilages;

    return [
        'sales'        => $sales,
        'hpp'          => $hpp,
        'spoilages'    => $spoilages,
        'other_income' => $otherIncome,
        'expense'      => $expense,
        'capital_in'   => $capitalIn,
        'capital_out'  => $capitalOut,
        'net_capital'  => $netCapital,
        'remaining_capital' => $remainingCapital,
        'total_revenue'=> $sales + $otherIncome,
        'total_cost'   => $hpp + $expense + $spoilages,
        'net_profit'   => ($sales + $otherIncome) - ($hpp + $expense + $spoilages),
    ];
}

/**
 * GET: Hitung distribusi bagi hasil berdasarkan periode
 */
function getProfitShareCalc($pdo, $storeId) {
    $period = $_GET['period'] ?? 'month';
    $dateFrom = $_GET['date_from'] ?? '';
    $dateTo = $_GET['date_to'] ?? '';

    // Tentukan range tanggal berdasarkan periode
    if (!$dateFrom || !$dateTo) {
        switch ($period) {
            case 'today': $dateFrom = $dateTo = date('Y-m-d'); break;
            case 'week':  $dateFrom = date('Y-m-d', strtotime('-7 days')); $dateTo = date('Y-m-d'); break;
            case 'year':  $dateFrom = date('Y-01-01'); $dateTo = date('Y-m-d'); break;
            default:      $dateFrom = date('Y-m-01'); $dateTo = date('Y-m-d');
        }
    }

    $profit = calcNetProfit($pdo, $storeId, $dateFrom, $dateTo);

    // Ambil daftar penerima aktif
    $stmt = $pdo->prepare("SELECT * FROM profit_shares WHERE store_id=? AND is_active=1 ORDER BY sort_order ASC, id ASC");
    $stmt->execute([$storeId]);
    $shares = $stmt->fetchAll();

    // Hitung nominal masing-masing
    $distribution = [];
    $totalPct = 0;
    foreach ($shares as $s) {
        $pct = (float)$s['percentage'];
        $totalPct += $pct;
        
        $baseAmount = round($profit['net_profit'] * ($pct / 100));
        
        // Cek apakah ada pengeluaran spesifik (deduct_from_share_id) untuk ID ini
        $stmtDed = $pdo->prepare("SELECT COALESCE(SUM(amount),0) as total FROM cash_flows WHERE store_id=? AND type='expense' AND deduct_from_share_id=? AND DATE(created_at) BETWEEN ? AND ?");
        $stmtDed->execute([$storeId, $s['id'], $dateFrom, $dateTo]);
        $deduction = (float)$stmtDed->fetch()['total'];
        
        $distribution[] = [
            'id'         => (int)$s['id'],
            'name'       => $s['name'],
            'role_label' => $s['role_label'],
            'percentage' => $pct,
            'base_amount'=> $baseAmount,
            'deduction'  => $deduction,
            'amount'     => $baseAmount - $deduction,
        ];
    }

    RAZjsonResponse(true, 'OK', [
        'period'       => ['from' => $dateFrom, 'to' => $dateTo],
        'profit'       => $profit,
        'distribution' => $distribution,
        'total_pct'    => $totalPct,
    ]);
}

/**
 * GET: Daftar penerima bagi hasil
 */
function listProfitShares($pdo, $storeId) {
    $stmt = $pdo->prepare("SELECT * FROM profit_shares WHERE store_id=? ORDER BY sort_order ASC, id ASC");
    $stmt->execute([$storeId]);
    RAZjsonResponse(true, 'OK', $stmt->fetchAll());
}

/**
 * POST: Tambah penerima bagi hasil baru
 */
function addProfitShare($pdo, $storeId, $user) {
    if ($user['role'] !== 'owner') RAZjsonResponse(false, 'Akses ditolak', [], 403);
    $input = RAZgetJsonInput();
    $name = RAZsanitize($input['name'] ?? '');
    $roleLabel = RAZsanitize($input['role_label'] ?? 'custom');
    $percentage = floatval($input['percentage'] ?? 0);

    if (empty($name)) RAZjsonResponse(false, 'Nama penerima wajib diisi');
    if ($percentage < 0 || $percentage > 100) RAZjsonResponse(false, 'Persentase harus 0-100');

    // Cek total persentase saat ini
    $stmt = $pdo->prepare("SELECT COALESCE(SUM(percentage),0) as total FROM profit_shares WHERE store_id=? AND is_active=1");
    $stmt->execute([$storeId]);
    $currentTotal = (float)$stmt->fetch()['total'];
    if (($currentTotal + $percentage) > 100) {
        RAZjsonResponse(false, 'Total persentase melebihi 100%. Sisa tersedia: ' . (100 - $currentTotal) . '%');
    }

    // Hitung sort_order baru
    $stmt = $pdo->prepare("SELECT COALESCE(MAX(sort_order),0)+1 as next_order FROM profit_shares WHERE store_id=?");
    $stmt->execute([$storeId]);
    $nextOrder = $stmt->fetch()['next_order'];

    $stmt = $pdo->prepare("INSERT INTO profit_shares (store_id, name, role_label, percentage, sort_order) VALUES (?,?,?,?,?)");
    $stmt->execute([$storeId, $name, $roleLabel, $percentage, $nextOrder]);

    RAZjsonResponse(true, 'Penerima bagi hasil berhasil ditambahkan', ['id' => (int)$pdo->lastInsertId()]);
}

/**
 * POST: Update persentase semua penerima (bulk update)
 * Body: { shares: [{id: 1, percentage: 30}, {id: 2, percentage: 40}, ...] }
 */
function updateProfitShares($pdo, $storeId, $user) {
    if ($user['role'] !== 'owner') RAZjsonResponse(false, 'Akses ditolak', [], 403);
    $input = RAZgetJsonInput();
    $shares = $input['shares'] ?? [];

    if (empty($shares)) RAZjsonResponse(false, 'Data penerima kosong');

    // Validasi total persentase
    $totalPct = 0;
    foreach ($shares as $s) {
        $pct = floatval($s['percentage'] ?? 0);
        if ($pct < 0 || $pct > 100) RAZjsonResponse(false, 'Persentase harus 0-100');
        $totalPct += $pct;
    }
    if ($totalPct > 100) RAZjsonResponse(false, 'Total persentase melebihi 100% (saat ini: ' . $totalPct . '%)');

    // Update satu per satu
    $stmt = $pdo->prepare("UPDATE profit_shares SET percentage=?, sort_order=? WHERE id=? AND store_id=?");
    foreach ($shares as $idx => $s) {
        $stmt->execute([floatval($s['percentage']), $idx, intval($s['id']), $storeId]);
    }

    RAZjsonResponse(true, 'Persentase bagi hasil berhasil disimpan');
}

/**
 * POST: Hapus penerima bagi hasil
 */
function deleteProfitShare($pdo, $storeId, $user) {
    if ($user['role'] !== 'owner') RAZjsonResponse(false, 'Akses ditolak', [], 403);
    $input = RAZgetJsonInput();
    $id = intval($input['id'] ?? 0);
    if (!$id) RAZjsonResponse(false, 'ID tidak valid');

    $stmt = $pdo->prepare("DELETE FROM profit_shares WHERE id=? AND store_id=?");
    $stmt->execute([$id, $storeId]);
    RAZjsonResponse(true, 'Penerima bagi hasil berhasil dihapus');
}

/**
 * POST: Generate & simpan snapshot laporan bagi hasil
 */
function generateProfitReport($pdo, $storeId, $user) {
    if ($user['role'] !== 'owner') RAZjsonResponse(false, 'Akses ditolak', [], 403);
    $input = RAZgetJsonInput();
    $dateFrom = $input['date_from'] ?? date('Y-m-01');
    $dateTo = $input['date_to'] ?? date('Y-m-d');
    $notes = RAZsanitize($input['notes'] ?? '');

    $profit = calcNetProfit($pdo, $storeId, $dateFrom, $dateTo);

    // Ambil distribusi aktif
    $stmt = $pdo->prepare("SELECT id, name, role_label, percentage FROM profit_shares WHERE store_id=? AND is_active=1 ORDER BY sort_order ASC");
    $stmt->execute([$storeId]);
    $shares = $stmt->fetchAll();

    $distribution = [];
    foreach ($shares as $s) {
        $pct = (float)$s['percentage'];
        $baseAmount = round($profit['net_profit'] * ($pct / 100));
        
        // Cek apakah ada pengeluaran spesifik (deduct_from_share_id) untuk ID ini
        $stmtDed = $pdo->prepare("SELECT COALESCE(SUM(amount),0) as total FROM cash_flows WHERE store_id=? AND type='expense' AND deduct_from_share_id=? AND DATE(created_at) BETWEEN ? AND ?");
        $stmtDed->execute([$storeId, $s['id'], $dateFrom, $dateTo]);
        $deduction = (float)$stmtDed->fetch()['total'];

        $distribution[] = [
            'name'       => $s['name'],
            'role_label' => $s['role_label'],
            'percentage' => $pct,
            'base_amount'=> $baseAmount,
            'deduction'  => $deduction,
            'amount'     => $baseAmount - $deduction,
        ];
    }

    // Snapshot Capital Flows
    $stmtCap = $pdo->prepare("SELECT * FROM capital_flows WHERE store_id=? AND DATE(created_at) BETWEEN ? AND ?");
    $stmtCap->execute([$storeId, $dateFrom, $dateTo]);
    $capitalSnapshot = $stmtCap->fetchAll();

    // Snapshot Spoilages
    $stmtSpoil = $pdo->prepare("SELECT s.*, i.name as item_name FROM spoilages s JOIN items i ON s.item_id=i.id WHERE s.store_id=? AND DATE(s.created_at) BETWEEN ? AND ?");
    $stmtSpoil->execute([$storeId, $dateFrom, $dateTo]);
    $spoilagesSnapshot = $stmtSpoil->fetchAll();

    // Simpan snapshot ke database
    $reportData = [
        'profit'       => $profit,
        'distribution' => $distribution,
    ];

    $stmt = $pdo->prepare("INSERT INTO profit_share_reports (store_id, period_from, period_to, net_profit, distribution_json, capital_json, spoilages_json, notes, created_by) VALUES (?,?,?,?,?,?,?,?,?)");
    $stmt->execute([
        $storeId, $dateFrom, $dateTo, $profit['net_profit'], 
        json_encode($reportData, JSON_UNESCAPED_UNICODE), 
        json_encode($capitalSnapshot, JSON_UNESCAPED_UNICODE),
        json_encode($spoilagesSnapshot, JSON_UNESCAPED_UNICODE),
        $notes, $user['id']
    ]);

    $reportData['capital'] = $capitalSnapshot;
    $reportData['spoilages'] = $spoilagesSnapshot;

    RAZjsonResponse(true, 'Laporan bagi hasil berhasil di-generate', [
        'report_id' => (int)$pdo->lastInsertId(),
        'data'      => $reportData,
    ]);
}

/**
 * GET: Daftar riwayat laporan bagi hasil
 */
function listProfitReports($pdo, $storeId) {
    $stmt = $pdo->prepare("SELECT psr.*, u.full_name as creator_name FROM profit_share_reports psr LEFT JOIN users u ON psr.created_by=u.id WHERE psr.store_id=? ORDER BY psr.created_at DESC LIMIT 20");
    $stmt->execute([$storeId]);
    $reports = $stmt->fetchAll();

    // Decode JSON untuk setiap laporan
    foreach ($reports as &$r) {
        $r['distribution'] = json_decode($r['distribution_json'] ?? '{}', true);
        $r['capital'] = json_decode($r['capital_json'] ?? '[]', true);
        $r['spoilages'] = json_decode($r['spoilages_json'] ?? '[]', true);
        
        unset($r['distribution_json']);
        unset($r['capital_json']);
        unset($r['spoilages_json']);
    }

    RAZjsonResponse(true, 'OK', $reports);
}

// ============================================================
// CAPITAL FLOWS (MODAL AWAL)
// ============================================================
function listCapitalFlows($pdo, $storeId) {
    $stmt = $pdo->prepare("SELECT c.*, u.full_name as creator_name FROM capital_flows c LEFT JOIN users u ON c.created_by=u.id WHERE c.store_id=? ORDER BY c.created_at DESC");
    $stmt->execute([$storeId]);
    RAZjsonResponse(true, 'OK', $stmt->fetchAll());
}

function addCapitalFlow($pdo, $storeId, $user) {
    if ($user['role'] !== 'owner') RAZjsonResponse(false, 'Akses ditolak', [], 403);
    $input = RAZgetJsonInput();
    
    $type = $input['type'] ?? 'in';
    $source_name = RAZsanitize($input['source_name'] ?? '');
    $amount = floatval($input['amount'] ?? 0);
    $notes = RAZsanitize($input['notes'] ?? '');

    if (!in_array($type, ['in', 'out'])) RAZjsonResponse(false, 'Tipe modal tidak valid');
    if ($amount <= 0) RAZjsonResponse(false, 'Nominal harus lebih dari 0');
    if (empty($source_name)) RAZjsonResponse(false, 'Sumber / Keterangan wajib diisi');

    $id = intval($input['id'] ?? 0);
    if ($id > 0) {
        $stmt = $pdo->prepare("UPDATE capital_flows SET type=?, source_name=?, amount=?, notes=? WHERE id=? AND store_id=?");
        $stmt->execute([$type, $source_name, $amount, $notes, $id, $storeId]);
        RAZjsonResponse(true, 'Modal berhasil diperbarui');
    } else {
        $stmt = $pdo->prepare("INSERT INTO capital_flows (store_id, type, source_name, amount, notes, created_by) VALUES (?,?,?,?,?,?)");
        $stmt->execute([$storeId, $type, $source_name, $amount, $notes, $user['id']]);
        RAZjsonResponse(true, 'Modal berhasil ' . ($type === 'in' ? 'ditambahkan' : 'ditarik'));
    }
}

// ============================================================
// SPOILAGES (BARANG RUSAK/BASI)
// ============================================================
function listSpoilages($pdo, $storeId) {
    $stmt = $pdo->prepare("SELECT s.*, i.name as item_name, u.full_name as creator_name FROM spoilages s JOIN items i ON s.item_id=i.id LEFT JOIN users u ON s.created_by=u.id WHERE s.store_id=? ORDER BY s.created_at DESC");
    $stmt->execute([$storeId]);
    RAZjsonResponse(true, 'OK', $stmt->fetchAll());
}

function addSpoilage($pdo, $storeId, $user) {
    $input = RAZgetJsonInput();
    
    $item_id = (int)($input['item_id'] ?? 0);
    $qty = (int)($input['qty'] ?? 0);
    $notes = RAZsanitize($input['notes'] ?? '');

    if ($item_id <= 0) RAZjsonResponse(false, 'Pilih item inventori terlebih dahulu');
    if ($qty <= 0) RAZjsonResponse(false, 'Jumlah (qty) harus lebih dari 0');

    // Cek item & ambil HPP
    $stmt = $pdo->prepare("SELECT id, name, hpp, stock FROM items WHERE id=? AND store_id=?");
    $stmt->execute([$item_id, $storeId]);
    $item = $stmt->fetch();

    if (!$item) RAZjsonResponse(false, 'Item tidak ditemukan');
    if ($item['stock'] < $qty) RAZjsonResponse(false, 'Stok item (' . $item['stock'] . ') tidak mencukupi untuk jumlah rusak yang diinput.');

    $hpp_value = (float)$item['hpp'];
    $total_loss = $hpp_value * $qty;

    $id = intval($input['id'] ?? 0);
    
    if ($id > 0) {
        // Kembalikan stok lama
        $oldStmt = $pdo->prepare("SELECT item_id, qty FROM spoilages WHERE id=? AND store_id=?");
        $oldStmt->execute([$id, $storeId]);
        $oldSpoil = $oldStmt->fetch();
        if ($oldSpoil) {
            $pdo->prepare("UPDATE items SET stock = stock + ? WHERE id=? AND store_id=?")->execute([$oldSpoil['qty'], $oldSpoil['item_id'], $storeId]);
        }
        // Update
        $stmtSp = $pdo->prepare("UPDATE spoilages SET item_id=?, qty=?, hpp_value=?, total_loss=?, notes=? WHERE id=? AND store_id=?");
        $stmtSp->execute([$item_id, $qty, $hpp_value, $total_loss, $notes, $id, $storeId]);
    } else {
        // Insert spoilage
        $stmtSp = $pdo->prepare("INSERT INTO spoilages (store_id, item_id, qty, hpp_value, total_loss, notes, created_by) VALUES (?,?,?,?,?,?,?)");
        $stmtSp->execute([$storeId, $item_id, $qty, $hpp_value, $total_loss, $notes, $user['id']]);
    }

    // Kurangi stok inventori otomatis
    $stmtUpd = $pdo->prepare("UPDATE items SET stock = stock - ? WHERE id=? AND store_id=?");
    $stmtUpd->execute([$qty, $item_id, $storeId]);

    RAZjsonResponse(true, 'Data barang rusak berhasil disimpan dan stok telah disesuaikan');
}

// ============================================================
// DELETION LOGIC FOR CAPITAL AND SPOILAGES
// ============================================================
function deleteCapitalFlow($pdo, $storeId, $user) {
    if ($user['role'] !== 'owner') RAZjsonResponse(false, 'Akses ditolak', [], 403);
    $input = RAZgetJsonInput();
    $id = intval($input['id'] ?? 0);
    $pdo->prepare("DELETE FROM capital_flows WHERE id = ? AND store_id = ?")->execute([$id, $storeId]);
    RAZjsonResponse(true, 'Riwayat modal berhasil dihapus');
}

function deleteSpoilage($pdo, $storeId, $user) {
    if ($user['role'] !== 'owner') RAZjsonResponse(false, 'Akses ditolak', [], 403);
    $input = RAZgetJsonInput();
    $id = intval($input['id'] ?? 0);
    
    // Kembalikan stok item
    $stmt = $pdo->prepare("SELECT item_id, qty FROM spoilages WHERE id = ? AND store_id = ?");
    $stmt->execute([$id, $storeId]);
    $spoilage = $stmt->fetch();
    
    if ($spoilage) {
        $pdo->prepare("UPDATE items SET stock = stock + ? WHERE id = ? AND store_id = ?")->execute([$spoilage['qty'], $spoilage['item_id'], $storeId]);
        $pdo->prepare("DELETE FROM spoilages WHERE id = ? AND store_id = ?")->execute([$id, $storeId]);
        RAZjsonResponse(true, 'Laporan barang rusak berhasil dihapus dan stok dikembalikan');
    }
    
    RAZjsonResponse(false, 'Data tidak ditemukan');
}


