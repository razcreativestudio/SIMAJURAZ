<?php
/**
 * ============================================================
 * api/RAZapiItems.php — API Inventori & Kategori SIMAJURAZ
 * ============================================================
 * Versi       : 1.0.0
 * Dibuat      : 2026-05-21
 * Diupdate    : 2026-05-21
 * Deskripsi   : Endpoint API untuk CRUD barang dan kategori.
 *               Semua data diisolasi per store_id (multi-tenant).
 * ============================================================
 */

require_once __DIR__ . '/../RAZconfig.php';
require_once __DIR__ . '/../includes/RAZsession.php';
require_once __DIR__ . '/../includes/RAZhelpers.php';

RAZrequireLogin();
$pdo = RAZgetConnection();
if (!$pdo) RAZjsonResponse(false, 'Koneksi database gagal', [], 500);

$user = RAZgetCurrentUser();
$storeId = $user['store_id'];
if (!$storeId) RAZjsonResponse(false, 'Tidak memiliki toko', [], 403);

$action = $_GET['action'] ?? '';
$method = $_SERVER['REQUEST_METHOD'];

switch ($action) {
    // === ITEMS ===
    case 'list':         listItems($pdo, $storeId); break;
    case 'get':          getItem($pdo, $storeId); break;
    case 'create':       if ($method==='POST') createItem($pdo, $storeId, $user); break;
    case 'update':       if ($method==='POST') updateItem($pdo, $storeId); break;
    case 'delete':       if ($method==='POST') deleteItem($pdo, $storeId); break;
    // === CATEGORIES ===
    case 'categories':   listCategories($pdo, $storeId); break;
    case 'create_cat':   if ($method==='POST') createCategory($pdo, $storeId); break;
    case 'update_cat':   if ($method==='POST') updateCategory($pdo, $storeId); break;
    case 'delete_cat':   if ($method==='POST') deleteCategory($pdo, $storeId); break;
    // === STOCK ===
    case 'low_stock':    getLowStock($pdo, $storeId); break;
    default: RAZjsonResponse(false, 'Aksi tidak valid', [], 400);
}

// ============================================================
// LIST ITEMS — Daftar barang dengan filter, search, pagination
// ============================================================
function listItems($pdo, $storeId) {
    $search   = $_GET['search'] ?? '';
    $category = $_GET['category'] ?? '';
    $page     = max(1, intval($_GET['page'] ?? 1));
    $perPage  = max(10, min(50, intval($_GET['per_page'] ?? 10)));
    $offset   = ($page - 1) * $perPage;

    // Bangun query dengan filter
    $where = "WHERE i.store_id = ?";
    $params = [$storeId];

    if ($search) {
        $where .= " AND (i.name LIKE ? OR i.sku LIKE ?)";
        $params[] = "%{$search}%";
        $params[] = "%{$search}%";
    }
    if ($category) {
        $where .= " AND i.category_id = ?";
        $params[] = $category;
    }

    // Hitung total data
    $countStmt = $pdo->prepare("SELECT COUNT(*) as total FROM items i {$where}");
    $countStmt->execute($params);
    $total = $countStmt->fetch()['total'];

    // Ambil data dengan pagination dan join kategori
    $sql = "SELECT i.*, c.name as category_name, c.color as category_color 
            FROM items i 
            LEFT JOIN categories c ON i.category_id = c.id 
            {$where} 
            ORDER BY i.created_at DESC 
            LIMIT {$perPage} OFFSET {$offset}";
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);

    RAZjsonResponse(true, 'OK', [
        'items'    => $stmt->fetchAll(),
        'total'    => (int)$total,
        'page'     => $page,
        'per_page' => $perPage,
        'pages'    => ceil($total / $perPage),
    ]);
}

// ============================================================
// GET ITEM — Detail satu barang
// ============================================================
function getItem($pdo, $storeId) {
    $id = intval($_GET['id'] ?? 0);
    $stmt = $pdo->prepare("SELECT * FROM items WHERE id = ? AND store_id = ?");
    $stmt->execute([$id, $storeId]);
    $item = $stmt->fetch();
    if (!$item) RAZjsonResponse(false, 'Barang tidak ditemukan');
    RAZjsonResponse(true, 'OK', $item);
}

// ============================================================
// CREATE ITEM — Tambah barang baru
// ============================================================
function createItem($pdo, $storeId, $user) {
    if ($user['role'] !== 'owner') RAZjsonResponse(false, 'Akses ditolak', [], 403);

    // Cek apakah ada file upload (gambar)
    $imageName = null;
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $upload = RAZuploadImage($_FILES['image'], __DIR__ . '/../uploads/items/', 2);
        if (!$upload['success']) RAZjsonResponse(false, $upload['error']);
        $imageName = $upload['filename'];
    }

    $name       = RAZsanitize($_POST['name'] ?? '');
    $sku        = RAZsanitize($_POST['sku'] ?? '');
    $categoryId = intval($_POST['category_id'] ?? 0) ?: null;
    $hpp        = floatval($_POST['hpp'] ?? 0);
    $sellPrice  = floatval($_POST['sell_price'] ?? 0);
    $stock      = intval($_POST['stock'] ?? 0);
    $minStock   = intval($_POST['min_stock'] ?? 5);

    if (empty($name)) RAZjsonResponse(false, 'Nama barang wajib diisi');
    if ($sellPrice <= 0) RAZjsonResponse(false, 'Harga jual harus lebih dari 0');

    // Cek SKU unik di toko ini
    if ($sku) {
        $check = $pdo->prepare("SELECT id FROM items WHERE sku = ? AND store_id = ?");
        $check->execute([$sku, $storeId]);
        if ($check->fetch()) RAZjsonResponse(false, 'SKU/Barcode sudah digunakan');
    }

    $stmt = $pdo->prepare("INSERT INTO items (store_id, category_id, name, sku, hpp, sell_price, stock, min_stock, image) 
                           VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->execute([$storeId, $categoryId, $name, $sku, $hpp, $sellPrice, $stock, $minStock, $imageName]);

    RAZjsonResponse(true, 'Barang berhasil ditambahkan', ['id' => $pdo->lastInsertId()]);
}

// ============================================================
// UPDATE ITEM — Edit barang
// ============================================================
function updateItem($pdo, $storeId) {
    $id = intval($_POST['id'] ?? 0);
    if (!$id) RAZjsonResponse(false, 'ID barang tidak valid');

    // Cek barang ada dan milik toko ini
    $check = $pdo->prepare("SELECT * FROM items WHERE id = ? AND store_id = ?");
    $check->execute([$id, $storeId]);
    if (!$check->fetch()) RAZjsonResponse(false, 'Barang tidak ditemukan');

    // Handle upload gambar baru
    $imageUpdate = '';
    $imageParam = [];
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $upload = RAZuploadImage($_FILES['image'], __DIR__ . '/../uploads/items/', 2);
        if ($upload['success']) {
            $imageUpdate = ', image = ?';
            $imageParam = [$upload['filename']];
        }
    }

    $name       = RAZsanitize($_POST['name'] ?? '');
    $sku        = RAZsanitize($_POST['sku'] ?? '');
    $categoryId = intval($_POST['category_id'] ?? 0) ?: null;
    $hpp        = floatval($_POST['hpp'] ?? 0);
    $sellPrice  = floatval($_POST['sell_price'] ?? 0);
    $stock      = intval($_POST['stock'] ?? 0);
    $minStock   = intval($_POST['min_stock'] ?? 5);
    $isActive   = intval($_POST['is_active'] ?? 1);

    if (empty($name)) RAZjsonResponse(false, 'Nama barang wajib diisi');

    // Cek SKU unik (kecuali milik sendiri)
    if ($sku) {
        $skuCheck = $pdo->prepare("SELECT id FROM items WHERE sku = ? AND store_id = ? AND id != ?");
        $skuCheck->execute([$sku, $storeId, $id]);
        if ($skuCheck->fetch()) RAZjsonResponse(false, 'SKU/Barcode sudah digunakan');
    }

    $sql = "UPDATE items SET name=?, sku=?, category_id=?, hpp=?, sell_price=?, stock=?, min_stock=?, is_active=? {$imageUpdate} WHERE id=? AND store_id=?";
    $params = array_merge([$name, $sku, $categoryId, $hpp, $sellPrice, $stock, $minStock, $isActive], $imageParam, [$id, $storeId]);
    $pdo->prepare($sql)->execute($params);

    RAZjsonResponse(true, 'Barang berhasil diperbarui');
}

// ============================================================
// DELETE ITEM — Hapus barang (soft-delete: nonaktifkan)
// ============================================================
function deleteItem($pdo, $storeId) {
    $input = RAZgetJsonInput();
    $id = intval($input['id'] ?? 0);
    if (!$id) RAZjsonResponse(false, 'ID barang tidak valid');

    $stmt = $pdo->prepare("UPDATE items SET is_active = 0 WHERE id = ? AND store_id = ?");
    $stmt->execute([$id, $storeId]);
    RAZjsonResponse(true, 'Barang berhasil dihapus');
}

// ============================================================
// CATEGORIES
// ============================================================
function listCategories($pdo, $storeId) {
    $stmt = $pdo->prepare("SELECT c.*, (SELECT COUNT(*) FROM items WHERE category_id = c.id AND is_active = 1) as item_count 
                           FROM categories c WHERE c.store_id = ? ORDER BY c.name");
    $stmt->execute([$storeId]);
    RAZjsonResponse(true, 'OK', $stmt->fetchAll());
}

function createCategory($pdo, $storeId) {
    $input = RAZgetJsonInput();
    $name = RAZsanitize($input['name'] ?? '');
    $color = $input['color'] ?? '#4F46E5';
    if (empty($name)) RAZjsonResponse(false, 'Nama kategori wajib diisi');

    $stmt = $pdo->prepare("INSERT INTO categories (store_id, name, color) VALUES (?, ?, ?)");
    $stmt->execute([$storeId, $name, $color]);
    RAZjsonResponse(true, 'Kategori berhasil ditambahkan', ['id' => $pdo->lastInsertId()]);
}

function updateCategory($pdo, $storeId) {
    $input = RAZgetJsonInput();
    $id = intval($input['id'] ?? 0);
    $name = RAZsanitize($input['name'] ?? '');
    $color = $input['color'] ?? '#4F46E5';
    if (!$id || empty($name)) RAZjsonResponse(false, 'Data tidak lengkap');

    $stmt = $pdo->prepare("UPDATE categories SET name=?, color=? WHERE id=? AND store_id=?");
    $stmt->execute([$name, $color, $id, $storeId]);
    RAZjsonResponse(true, 'Kategori berhasil diperbarui');
}

function deleteCategory($pdo, $storeId) {
    $input = RAZgetJsonInput();
    $id = intval($input['id'] ?? 0);

    // Pindahkan item ke tanpa kategori sebelum hapus
    $pdo->prepare("UPDATE items SET category_id = NULL WHERE category_id = ? AND store_id = ?")->execute([$id, $storeId]);
    $pdo->prepare("DELETE FROM categories WHERE id = ? AND store_id = ?")->execute([$id, $storeId]);
    RAZjsonResponse(true, 'Kategori berhasil dihapus');
}

function getLowStock($pdo, $storeId) {
    $stmt = $pdo->prepare("SELECT id, name, sku, stock, min_stock FROM items WHERE store_id = ? AND is_active = 1 AND stock <= min_stock ORDER BY stock ASC LIMIT 20");
    $stmt->execute([$storeId]);
    RAZjsonResponse(true, 'OK', $stmt->fetchAll());
}
