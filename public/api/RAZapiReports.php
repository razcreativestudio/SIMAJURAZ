<?php
/**
 * ============================================================
 * api/RAZapiReports.php — API Laporan SIMAJURAZ
 * ============================================================
 * Versi       : 1.0.0
 * Dibuat      : 2026-05-21
 * Diupdate    : 2026-05-21
 * Deskripsi   : Endpoint untuk menghasilkan data laporan:
 *               - Riwayat transaksi (filter harian/mingguan/bulanan/tahunan)
 *               - Laporan arus kas
 *               - Laporan laba rugi
 *               - Data untuk export PDF
 * ============================================================
 */
require_once __DIR__ . '/../RAZconfig.php';
require_once __DIR__ . '/../includes/RAZsession.php';
require_once __DIR__ . '/../includes/RAZhelpers.php';

RAZrequireOwner();
$pdo = RAZgetConnection();
if (!$pdo) RAZjsonResponse(false, 'Koneksi database gagal', [], 500);

$user = RAZgetCurrentUser();
$storeId = $user['store_id'];
$action = $_GET['action'] ?? '';

switch ($action) {
    case 'transactions':   reportTransactions($pdo, $storeId); break;
    case 'transaction_detail': transactionDetail($pdo, $storeId); break;
    case 'cashflow':       reportCashflow($pdo, $storeId); break;
    case 'profit_loss':    reportProfitLoss($pdo, $storeId); break;
    case 'inventory_report': reportInventory($pdo, $storeId); break;
    case 'pdf_data':       getPdfData($pdo, $storeId, $user); break;
    default: RAZjsonResponse(false, 'Aksi tidak valid', [], 400);
}

// ============================================================
// Riwayat Transaksi
// ============================================================
function reportTransactions($pdo, $storeId, $returnOnly = false) {
    $dateFrom = $_GET['date_from'] ?? date('Y-m-01');
    $dateTo   = $_GET['date_to'] ?? date('Y-m-d');
    $status   = $_GET['status'] ?? '';
    $payment  = $_GET['payment'] ?? '';
    $page     = max(1, intval($_GET['page'] ?? 1));
    $perPage  = 20;
    $offset   = ($page - 1) * $perPage;

    $where = "WHERE t.store_id = ? AND DATE(t.created_at) BETWEEN ? AND ?";
    $params = [$storeId, $dateFrom, $dateTo];

    if ($status === 'completed' || $status === 'voided') { $where .= " AND t.status = ?"; $params[] = $status; }
    if ($payment) { $where .= " AND t.payment_method = ?"; $params[] = $payment; }

    // Total data
    $countStmt = $pdo->prepare("SELECT COUNT(*) as total FROM transactions t {$where}");
    $countStmt->execute($params);
    $total = $countStmt->fetch()['total'];

    // Data transaksi
    $stmt = $pdo->prepare("SELECT t.*, u.full_name as cashier_name FROM transactions t 
        JOIN users u ON t.user_id = u.id {$where} ORDER BY t.created_at DESC LIMIT {$perPage} OFFSET {$offset}");
    $stmt->execute($params);

    // Ringkasan
    $sumStmt = $pdo->prepare("SELECT COALESCE(SUM(CASE WHEN status='completed' THEN grand_total ELSE 0 END),0) as total_sales,
        COALESCE(SUM(CASE WHEN status='voided' THEN grand_total ELSE 0 END),0) as total_void,
        COUNT(CASE WHEN status='completed' THEN 1 END) as count_success,
        COUNT(CASE WHEN status='voided' THEN 1 END) as count_void
        FROM transactions t {$where}");
    $sumStmt->execute($params);

    $data = [
        'transactions' => $stmt->fetchAll(),
        'total' => (int)$total,
        'page' => $page,
        'pages' => ceil($total / $perPage),
        'summary' => $sumStmt->fetch(),
    ];
    if ($returnOnly) return $data;
    RAZjsonResponse(true, 'OK', $data);
}

// ============================================================
// Detail Satu Transaksi
// ============================================================
function transactionDetail($pdo, $storeId) {
    $id = intval($_GET['id'] ?? 0);
    $trans = $pdo->prepare("SELECT t.*, u.full_name as cashier_name FROM transactions t JOIN users u ON t.user_id = u.id WHERE t.id = ? AND t.store_id = ?");
    $trans->execute([$id, $storeId]);
    $t = $trans->fetch();
    if (!$t) RAZjsonResponse(false, 'Transaksi tidak ditemukan');

    $items = $pdo->prepare("SELECT * FROM transaction_items WHERE transaction_id = ?");
    $items->execute([$id]);

    RAZjsonResponse(true, 'OK', ['transaction' => $t, 'items' => $items->fetchAll()]);
}

// ============================================================
// Laporan Arus Kas
// ============================================================
function reportCashflow($pdo, $storeId, $returnOnly = false) {
    $dateFrom = $_GET['date_from'] ?? date('Y-m-01');
    $dateTo   = $_GET['date_to'] ?? date('Y-m-d');

    $stmt = $pdo->prepare("SELECT cf.*, u.full_name as user_name FROM cash_flows cf 
        JOIN users u ON cf.user_id = u.id WHERE cf.store_id = ? AND DATE(cf.created_at) BETWEEN ? AND ? ORDER BY cf.created_at DESC");
    $stmt->execute([$storeId, $dateFrom, $dateTo]);

    $sumStmt = $pdo->prepare("SELECT type, COALESCE(SUM(amount),0) as total FROM cash_flows 
        WHERE store_id = ? AND DATE(created_at) BETWEEN ? AND ? GROUP BY type");
    $sumStmt->execute([$storeId, $dateFrom, $dateTo]);
    $sums = ['income' => 0, 'expense' => 0];
    foreach ($sumStmt->fetchAll() as $row) $sums[$row['type']] = (float)$row['total'];

    $data = ['cashflows' => $stmt->fetchAll(), 'summary' => $sums];
    if ($returnOnly) return $data;
    RAZjsonResponse(true, 'OK', $data);
}

// ============================================================
// Laporan Laba Rugi
// ============================================================
function reportProfitLoss($pdo, $storeId, $returnOnly = false) {
    $dateFrom = $_GET['date_from'] ?? date('Y-m-01');
    $dateTo   = $_GET['date_to'] ?? date('Y-m-d');

    // Penjualan
    $s = $pdo->prepare("SELECT COALESCE(SUM(grand_total),0) as total, COUNT(*) as count FROM transactions WHERE store_id=? AND DATE(created_at) BETWEEN ? AND ? AND status='completed'");
    $s->execute([$storeId, $dateFrom, $dateTo]);
    $sales = $s->fetch();

    // HPP
    $h = $pdo->prepare("SELECT COALESCE(SUM(ti.hpp*ti.qty),0) as total FROM transaction_items ti JOIN transactions t ON ti.transaction_id=t.id WHERE t.store_id=? AND DATE(t.created_at) BETWEEN ? AND ? AND t.status='completed'");
    $h->execute([$storeId, $dateFrom, $dateTo]);
    $hpp = (float)$h->fetch()['total'];

    // Pemasukan lain
    $i = $pdo->prepare("SELECT COALESCE(SUM(amount),0) as total FROM cash_flows WHERE store_id=? AND type='income' AND DATE(created_at) BETWEEN ? AND ?");
    $i->execute([$storeId, $dateFrom, $dateTo]);
    $income = (float)$i->fetch()['total'];

    // Pengeluaran per kategori
    $e = $pdo->prepare("SELECT category, SUM(amount) as total FROM cash_flows WHERE store_id=? AND type='expense' AND DATE(created_at) BETWEEN ? AND ? GROUP BY category ORDER BY total DESC");
    $e->execute([$storeId, $dateFrom, $dateTo]);
    $expenses = $e->fetchAll();
    $expTotal = array_sum(array_column($expenses, 'total'));

    // Item terlaris
    $top = $pdo->prepare("SELECT ti.item_name, SUM(ti.qty) as total_qty, SUM(ti.subtotal) as total_sales FROM transaction_items ti JOIN transactions t ON ti.transaction_id=t.id WHERE t.store_id=? AND DATE(t.created_at) BETWEEN ? AND ? AND t.status='completed' GROUP BY ti.item_name ORDER BY total_qty DESC LIMIT 10");
    $top->execute([$storeId, $dateFrom, $dateTo]);

    $data = [
        'period'         => ['from' => $dateFrom, 'to' => $dateTo],
        'sales_total'    => (float)$sales['total'],
        'sales_count'    => (int)$sales['count'],
        'hpp_total'      => $hpp,
        'gross_profit'   => (float)$sales['total'] - $hpp,
        'other_income'   => $income,
        'expense_total'  => (float)$expTotal,
        'expense_detail' => $expenses,
        'net_profit'     => (float)$sales['total'] + $income - $hpp - $expTotal,
        'top_items'      => $top->fetchAll(),
    ];
    if ($returnOnly) return $data;
    RAZjsonResponse(true, 'OK', $data);
}

// ============================================================
// Laporan Stok Inventori
// ============================================================
function reportInventory($pdo, $storeId, $returnOnly = false) {
    $stmt = $pdo->prepare("SELECT i.*, c.name as category_name FROM items i LEFT JOIN categories c ON i.category_id=c.id WHERE i.store_id=? AND i.is_active=1 ORDER BY i.name");
    $stmt->execute([$storeId]);
    $items = $stmt->fetchAll();

    $totalValue = 0; $totalHppValue = 0;
    foreach ($items as $item) {
        $totalValue += $item['sell_price'] * $item['stock'];
        $totalHppValue += $item['hpp'] * $item['stock'];
    }

    $data = [
        'items' => $items,
        'total_items' => count($items),
        'total_stock_value' => $totalValue,
        'total_hpp_value' => $totalHppValue,
    ];
    if ($returnOnly) return $data;
    RAZjsonResponse(true, 'OK', $data);
}

// ============================================================
// Data untuk PDF (semua dalam 1 call)
// ============================================================
function getPdfData($pdo, $storeId, $user) {
    $reportType = $_GET['report'] ?? 'profit_loss';
    $dateFrom = $_GET['date_from'] ?? date('Y-m-01');
    $dateTo   = $_GET['date_to'] ?? date('Y-m-d');

    // Info toko
    $store = $pdo->prepare("SELECT * FROM stores WHERE id = ?");
    $store->execute([$storeId]);
    $storeData = $store->fetch();

    $data = ['store' => $storeData, 'generated_by' => $user['full_name'], 'generated_at' => date('Y-m-d H:i:s'), 'period' => ['from' => $dateFrom, 'to' => $dateTo]];

    // Sesuaikan data berdasarkan tipe laporan
    $_GET['date_from'] = $dateFrom;
    $_GET['date_to'] = $dateTo;

    switch ($reportType) {
        case 'transactions':
            $data['report'] = reportTransactions($pdo, $storeId, true);
            break;
        case 'cashflow':
            $data['report'] = reportCashflow($pdo, $storeId, true);
            break;
        case 'profit_loss':
            $data['report'] = reportProfitLoss($pdo, $storeId, true);
            break;
        case 'inventory':
            $data['report'] = reportInventory($pdo, $storeId, true);
            break;
    }

    $data['report_type'] = $reportType;
    RAZjsonResponse(true, 'OK', $data);
}
