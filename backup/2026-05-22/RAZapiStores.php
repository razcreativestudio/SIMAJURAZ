<?php
/**
 * ============================================================
 * api/RAZapiStores.php — API Manajemen Toko SIMAJURAZ
 * ============================================================
 * Versi       : 1.0.0
 * Dibuat      : 2026-05-21
 * Diupdate    : 2026-05-21
 * Deskripsi   : Endpoint API untuk data toko dan statistik dashboard.
 *               - GET ?action=dashboard_stats  → Statistik dashboard
 *               - GET ?action=store_profile    → Profil toko
 *               - POST ?action=update_store    → Update profil toko
 *               - GET ?action=admin_stats      → Statistik Super Admin
 * ============================================================
 */

require_once __DIR__ . '/../RAZconfig.php';
require_once __DIR__ . '/../includes/RAZsession.php';
require_once __DIR__ . '/../includes/RAZhelpers.php';

// Pastikan sudah login
RAZrequireLogin();

$pdo = RAZgetConnection();
if (!$pdo) RAZjsonResponse(false, 'Koneksi database gagal', [], 500);

$action = $_GET['action'] ?? '';
$user = RAZgetCurrentUser();

switch ($action) {
    case 'dashboard_stats':
        getDashboardStats($pdo, $user);
        break;
    case 'store_profile':
        getStoreProfile($pdo, $user);
        break;
    case 'update_store':
        updateStoreProfile($pdo, $user);
        break;
    case 'admin_stats':
        getAdminStats($pdo, $user);
        break;
    case 'recent_transactions':
        getRecentTransactions($pdo, $user);
        break;
    case 'top_items':
        getTopItems($pdo, $user);
        break;
    case 'sales_chart':
        getSalesChart($pdo, $user);
        break;
    default:
        RAZjsonResponse(false, 'Aksi tidak valid', [], 400);
}

// ============================================================
// Statistik Dashboard Owner/Employee
// ============================================================
function getDashboardStats($pdo, $user) {
    if (!$user['store_id']) RAZjsonResponse(false, 'Tidak memiliki toko');

    $storeId = $user['store_id'];
    $today = date('Y-m-d');

    // Penjualan hari ini
    $stmt = $pdo->prepare("SELECT COALESCE(SUM(grand_total), 0) as total, COUNT(*) as count 
                           FROM transactions WHERE store_id = ? AND DATE(created_at) = ? AND status = 'completed'");
    $stmt->execute([$storeId, $today]);
    $salesToday = $stmt->fetch();

    // Penjualan bulan ini
    $monthStart = date('Y-m-01');
    $stmt = $pdo->prepare("SELECT COALESCE(SUM(grand_total), 0) as total, COUNT(*) as count 
                           FROM transactions WHERE store_id = ? AND created_at >= ? AND status = 'completed'");
    $stmt->execute([$storeId, $monthStart]);
    $salesMonth = $stmt->fetch();

    // Total produk aktif
    $stmt = $pdo->prepare("SELECT COUNT(*) as total FROM items WHERE store_id = ? AND is_active = 1");
    $stmt->execute([$storeId]);
    $products = $stmt->fetch();

    // Produk stok menipis (dibawah min_stock)
    $stmt = $pdo->prepare("SELECT COUNT(*) as total FROM items WHERE store_id = ? AND is_active = 1 AND stock <= min_stock");
    $stmt->execute([$storeId]);
    $lowStock = $stmt->fetch();

    // Laba hari ini (penjualan - HPP)
    $stmt = $pdo->prepare("SELECT COALESCE(SUM(ti.subtotal - (ti.hpp * ti.qty)), 0) as profit
                           FROM transaction_items ti 
                           JOIN transactions t ON ti.transaction_id = t.id 
                           WHERE t.store_id = ? AND DATE(t.created_at) = ? AND t.status = 'completed'");
    $stmt->execute([$storeId, $today]);
    $profitToday = $stmt->fetch();

    // Pengeluaran hari ini
    $stmt = $pdo->prepare("SELECT COALESCE(SUM(amount), 0) as total FROM cash_flows 
                           WHERE store_id = ? AND type = 'expense' AND DATE(created_at) = ?");
    $stmt->execute([$storeId, $today]);
    $expenseToday = $stmt->fetch();

    RAZjsonResponse(true, 'OK', [
        'sales_today'    => ['total' => (float)$salesToday['total'], 'count' => (int)$salesToday['count']],
        'sales_month'    => ['total' => (float)$salesMonth['total'], 'count' => (int)$salesMonth['count']],
        'products'       => (int)$products['total'],
        'low_stock'      => (int)$lowStock['total'],
        'profit_today'   => (float)$profitToday['profit'],
        'expense_today'  => (float)$expenseToday['total'],
    ]);
}

// ============================================================
// Profil Toko
// ============================================================
function getStoreProfile($pdo, $user) {
    if (!$user['store_id']) RAZjsonResponse(false, 'Tidak memiliki toko');
    $stmt = $pdo->prepare("SELECT * FROM stores WHERE id = ?");
    $stmt->execute([$user['store_id']]);
    $store = $stmt->fetch();
    if (!$store) RAZjsonResponse(false, 'Toko tidak ditemukan');
    RAZjsonResponse(true, 'OK', $store);
}

// ============================================================
// Update Profil Toko (Owner Only)
// ============================================================
function updateStoreProfile($pdo, $user) {
    if ($user['role'] !== 'owner') RAZjsonResponse(false, 'Akses ditolak', [], 403);

    // Cek apakah ada file upload (logo)
    if (isset($_FILES['store_logo']) && $_FILES['store_logo']['error'] === UPLOAD_ERR_OK) {
        $upload = RAZuploadImage($_FILES['store_logo'], __DIR__ . '/../uploads/logos/', 2);
        if (!$upload['success']) RAZjsonResponse(false, $upload['error']);
        $logoFile = $upload['filename'];
    }

    $storeName     = RAZsanitize($_POST['store_name'] ?? '');
    $storeAddress  = RAZsanitize($_POST['store_address'] ?? '');
    $storePhone    = RAZsanitize($_POST['store_phone'] ?? '');
    $taxPercentage = floatval($_POST['tax_percentage'] ?? 0);
    $receiptFooter = RAZsanitize($_POST['receipt_footer'] ?? '');

    if (empty($storeName)) RAZjsonResponse(false, 'Nama toko wajib diisi');

    $sql = "UPDATE stores SET store_name = ?, store_address = ?, store_phone = ?, tax_percentage = ?, receipt_footer = ?";
    $params = [$storeName, $storeAddress, $storePhone, $taxPercentage, $receiptFooter];

    if (isset($logoFile)) {
        $sql .= ", store_logo = ?";
        $params[] = $logoFile;
    }

    $sql .= " WHERE id = ?";
    $params[] = $user['store_id'];

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);

    // Update session
    $_SESSION['raz_store_name'] = $storeName;
    if (isset($logoFile)) $_SESSION['raz_store_logo'] = $logoFile;

    RAZjsonResponse(true, 'Profil toko berhasil diperbarui');
}

// ============================================================
// Statistik Super Admin
// ============================================================
function getAdminStats($pdo, $user) {
    if ($user['role'] !== 'superadmin') RAZjsonResponse(false, 'Akses ditolak', [], 403);

    $totalStores = $pdo->query("SELECT COUNT(*) as total FROM stores")->fetch()['total'];
    $totalUsers = $pdo->query("SELECT COUNT(*) as total FROM users WHERE role != 'superadmin'")->fetch()['total'];
    $totalOwners = $pdo->query("SELECT COUNT(*) as total FROM users WHERE role = 'owner'")->fetch()['total'];
    $totalEmployees = $pdo->query("SELECT COUNT(*) as total FROM users WHERE role = 'employee'")->fetch()['total'];

    // Daftar toko terbaru
    $stores = $pdo->query("SELECT s.*, u.full_name as owner_name FROM stores s JOIN users u ON s.owner_id = u.id ORDER BY s.created_at DESC LIMIT 10")->fetchAll();

    RAZjsonResponse(true, 'OK', [
        'total_stores'    => (int)$totalStores,
        'total_users'     => (int)$totalUsers,
        'total_owners'    => (int)$totalOwners,
        'total_employees' => (int)$totalEmployees,
        'recent_stores'   => $stores,
    ]);
}

// ============================================================
// Transaksi Terbaru
// ============================================================
function getRecentTransactions($pdo, $user) {
    if (!$user['store_id']) RAZjsonResponse(false, 'Tidak memiliki toko');
    $stmt = $pdo->prepare("SELECT t.*, u.full_name as cashier_name FROM transactions t 
                           JOIN users u ON t.user_id = u.id 
                           WHERE t.store_id = ? ORDER BY t.created_at DESC LIMIT 10");
    $stmt->execute([$user['store_id']]);
    RAZjsonResponse(true, 'OK', $stmt->fetchAll());
}

// ============================================================
// Item Terlaris
// ============================================================
function getTopItems($pdo, $user) {
    if (!$user['store_id']) RAZjsonResponse(false, 'Tidak memiliki toko');
    $stmt = $pdo->prepare("SELECT ti.item_name, SUM(ti.qty) as total_qty, SUM(ti.subtotal) as total_sales
                           FROM transaction_items ti 
                           JOIN transactions t ON ti.transaction_id = t.id 
                           WHERE t.store_id = ? AND t.status = 'completed'
                           GROUP BY ti.item_name ORDER BY total_qty DESC LIMIT 5");
    $stmt->execute([$user['store_id']]);
    RAZjsonResponse(true, 'OK', $stmt->fetchAll());
}

// ============================================================
// Data Chart Penjualan 7 Hari Terakhir
// ============================================================
function getSalesChart($pdo, $user) {
    if (!$user['store_id']) RAZjsonResponse(false, 'Tidak memiliki toko');

    $data = [];
    for ($i = 6; $i >= 0; $i--) {
        $date = date('Y-m-d', strtotime("-{$i} days"));
        $stmt = $pdo->prepare("SELECT COALESCE(SUM(grand_total), 0) as total FROM transactions 
                               WHERE store_id = ? AND DATE(created_at) = ? AND status = 'completed'");
        $stmt->execute([$user['store_id'], $date]);
        $row = $stmt->fetch();
        $data[] = [
            'date'  => date('d/m', strtotime($date)),
            'total' => (float)$row['total'],
        ];
    }
    RAZjsonResponse(true, 'OK', $data);
}
