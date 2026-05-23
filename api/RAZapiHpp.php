<?php
/**
 * ============================================================
 * api/RAZapiHpp.php — API Kalkulator HPP SIMAJURAZ
 * ============================================================
 * Versi       : 1.0.0
 * Dibuat      : 2026-05-22
 * Diupdate    : 2026-05-22
 * Deskripsi   : CRUD kalkulasi HPP (Harga Pokok Penjualan).
 *               Mencakup bahan baku, packaging, biaya tambahan,
 *               overhead, margin, dan push ke inventori.
 * ============================================================
 */
ini_set('display_errors', 0);
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
    case 'list':            listHpp($pdo, $storeId); break;
    case 'detail':          detailHpp($pdo, $storeId); break;
    case 'create':          createHpp($pdo, $storeId); break;
    case 'update':          updateHpp($pdo, $storeId); break;
    case 'delete':          deleteHpp($pdo, $storeId); break;
    case 'push_inventory':  pushToInventory($pdo, $storeId); break;
    default: RAZjsonResponse(false, 'Aksi tidak valid', [], 400);
}

// ============================================================
// LIST: Daftar semua kalkulasi HPP
// ============================================================
function listHpp($pdo, $storeId) {
    $stmt = $pdo->prepare("SELECT * FROM hpp_calculations WHERE store_id=? ORDER BY updated_at DESC, id DESC");
    $stmt->execute([$storeId]);
    $items = $stmt->fetchAll();

    // Hitung HPP ringkas untuk setiap item
    foreach ($items as &$item) {
        $item['hpp_total'] = calcHppTotal($pdo, $item);
    }

    RAZjsonResponse(true, 'OK', $items);
}

// ============================================================
// DETAIL: Satu kalkulasi lengkap dengan semua komponen
// ============================================================
function detailHpp($pdo, $storeId) {
    $id = intval($_GET['id'] ?? 0);
    if (!$id) RAZjsonResponse(false, 'ID tidak valid');

    $stmt = $pdo->prepare("SELECT * FROM hpp_calculations WHERE id=? AND store_id=?");
    $stmt->execute([$id, $storeId]);
    $hpp = $stmt->fetch();
    if (!$hpp) RAZjsonResponse(false, 'Data tidak ditemukan');

    // Ambil semua komponen
    $stmt = $pdo->prepare("SELECT * FROM hpp_ingredients WHERE hpp_id=? ORDER BY sort_order ASC, id ASC");
    $stmt->execute([$id]);
    $hpp['ingredients'] = $stmt->fetchAll();

    $stmt = $pdo->prepare("SELECT * FROM hpp_packagings WHERE hpp_id=? ORDER BY sort_order ASC, id ASC");
    $stmt->execute([$id]);
    $hpp['packagings'] = $stmt->fetchAll();

    $stmt = $pdo->prepare("SELECT * FROM hpp_extra_costs WHERE hpp_id=? ORDER BY sort_order ASC, id ASC");
    $stmt->execute([$id]);
    $hpp['extra_costs'] = $stmt->fetchAll();

    // Hitung total
    $calc = calcHppBreakdown($pdo, $hpp);
    $hpp['calc'] = $calc;

    RAZjsonResponse(true, 'OK', $hpp);
}

// ============================================================
// CREATE: Buat kalkulasi HPP baru
// ============================================================
function createHpp($pdo, $storeId) {
    $input = RAZgetJsonInput();
    $name = RAZsanitize($input['product_name'] ?? '');
    if (empty($name)) RAZjsonResponse(false, 'Nama produk/menu wajib diisi');

    $stmt = $pdo->prepare("INSERT INTO hpp_calculations (store_id, product_name) VALUES (?,?)");
    $stmt->execute([$storeId, $name]);
    $id = (int)$pdo->lastInsertId();

    RAZjsonResponse(true, 'Kalkulasi HPP berhasil dibuat', ['id' => $id]);
}

// ============================================================
// UPDATE: Update master + semua komponen (bulk save)
// ============================================================
function updateHpp($pdo, $storeId) {
    $input = RAZgetJsonInput();
    $id = intval($input['id'] ?? 0);
    if (!$id) RAZjsonResponse(false, 'ID tidak valid');

    // Verifikasi kepemilikan
    $check = $pdo->prepare("SELECT id FROM hpp_calculations WHERE id=? AND store_id=?");
    $check->execute([$id, $storeId]);
    if (!$check->fetch()) RAZjsonResponse(false, 'Data tidak ditemukan');

    // Update master
    $stmt = $pdo->prepare("UPDATE hpp_calculations SET product_name=?, portions=?, overhead_pct=?, margin_pct=?, current_sell_price=?, notes=?, updated_at=? WHERE id=? AND store_id=?");
    $stmt->execute([
        RAZsanitize($input['product_name'] ?? ''),
        max(1, intval($input['portions'] ?? 1)),
        floatval($input['overhead_pct'] ?? 15),
        floatval($input['margin_pct'] ?? 30),
        floatval($input['current_sell_price'] ?? 0),
        RAZsanitize($input['notes'] ?? ''),
        date('Y-m-d H:i:s'),
        $id, $storeId
    ]);

    // Rebuild komponen: hapus lama, insert baru
    // --- Bahan ---
    $pdo->prepare("DELETE FROM hpp_ingredients WHERE hpp_id=?")->execute([$id]);
    if (!empty($input['ingredients'])) {
        $stmt = $pdo->prepare("INSERT INTO hpp_ingredients (hpp_id, name, purchase_qty, purchase_unit, purchase_price, portions_yield, sort_order) VALUES (?,?,?,?,?,?,?)");
        foreach ($input['ingredients'] as $i => $ing) {
            $stmt->execute([$id, RAZsanitize($ing['name']??''), floatval($ing['purchase_qty']??1), RAZsanitize($ing['purchase_unit']??'pcs'), floatval($ing['purchase_price']??0), max(1,intval($ing['portions_yield']??1)), $i]);
        }
    }

    // --- Packaging ---
    $pdo->prepare("DELETE FROM hpp_packagings WHERE hpp_id=?")->execute([$id]);
    if (!empty($input['packagings'])) {
        $stmt = $pdo->prepare("INSERT INTO hpp_packagings (hpp_id, name, purchase_qty, purchase_unit, purchase_price, capacity_pcs, usage_per_portion, sort_order) VALUES (?,?,?,?,?,?,?,?)");
        foreach ($input['packagings'] as $i => $pkg) {
            $stmt->execute([$id, RAZsanitize($pkg['name']??''), floatval($pkg['purchase_qty']??1), RAZsanitize($pkg['purchase_unit']??'pack'), floatval($pkg['purchase_price']??0), max(1,intval($pkg['capacity_pcs']??1)), max(1,intval($pkg['usage_per_portion']??1)), $i]);
        }
    }

    // --- Biaya Tambahan ---
    $pdo->prepare("DELETE FROM hpp_extra_costs WHERE hpp_id=?")->execute([$id]);
    if (!empty($input['extra_costs'])) {
        $stmt = $pdo->prepare("INSERT INTO hpp_extra_costs (hpp_id, name, amount, portions_divide, sort_order) VALUES (?,?,?,?,?)");
        foreach ($input['extra_costs'] as $i => $ec) {
            $stmt->execute([$id, RAZsanitize($ec['name']??''), floatval($ec['amount']??0), max(1,intval($ec['portions_divide']??1)), $i]);
        }
    }

    RAZjsonResponse(true, 'Kalkulasi HPP berhasil disimpan');
}

// ============================================================
// DELETE: Hapus kalkulasi + semua komponen
// ============================================================
function deleteHpp($pdo, $storeId) {
    $input = RAZgetJsonInput();
    $id = intval($input['id'] ?? 0);
    if (!$id) RAZjsonResponse(false, 'ID tidak valid');

    $pdo->prepare("DELETE FROM hpp_ingredients WHERE hpp_id=?")->execute([$id]);
    $pdo->prepare("DELETE FROM hpp_packagings WHERE hpp_id=?")->execute([$id]);
    $pdo->prepare("DELETE FROM hpp_extra_costs WHERE hpp_id=?")->execute([$id]);
    $pdo->prepare("DELETE FROM hpp_calculations WHERE id=? AND store_id=?")->execute([$id, $storeId]);

    RAZjsonResponse(true, 'Kalkulasi HPP berhasil dihapus');
}

// ============================================================
// PUSH: Kirim hasil HPP ke tabel items (inventori)
// ============================================================
function pushToInventory($pdo, $storeId) {
    $input = RAZgetJsonInput();
    $id = intval($input['id'] ?? 0);
    if (!$id) RAZjsonResponse(false, 'ID tidak valid');

    $stmt = $pdo->prepare("SELECT * FROM hpp_calculations WHERE id=? AND store_id=?");
    $stmt->execute([$id, $storeId]);
    $hpp = $stmt->fetch();
    if (!$hpp) RAZjsonResponse(false, 'Data tidak ditemukan');

    $calc = calcHppBreakdown($pdo, $hpp);
    $hppPerPortion = round($calc['hpp_total'], 2);
    $sellPrice = round($calc['recommended_price'], 2);

    // Gunakan harga user jika ada, kalau tidak pakai rekomendasi
    if ((float)$hpp['current_sell_price'] > 0) {
        $sellPrice = (float)$hpp['current_sell_price'];
    }

    // Buat item baru di inventori
    $stmt = $pdo->prepare("INSERT INTO items (store_id, name, hpp, sell_price, stock, is_active) VALUES (?,?,?,?,0,1)");
    $stmt->execute([$storeId, $hpp['product_name'], $hppPerPortion, $sellPrice]);

    RAZjsonResponse(true, 'Produk berhasil ditambahkan ke Inventori', [
        'item_id' => (int)$pdo->lastInsertId(),
        'hpp' => $hppPerPortion,
        'sell_price' => $sellPrice
    ]);
}

// ============================================================
// HELPER: Hitung HPP breakdown lengkap
// ============================================================
function calcHppBreakdown($pdo, $hpp) {
    $id = $hpp['id'];

    // A. Biaya Bahan per porsi
    $stmt = $pdo->prepare("SELECT * FROM hpp_ingredients WHERE hpp_id=?");
    $stmt->execute([$id]);
    $ingredients = $stmt->fetchAll();
    $ingredientCost = 0;
    foreach ($ingredients as $ing) {
        $yield = max(1, (int)$ing['portions_yield']);
        $ingredientCost += (float)$ing['purchase_price'] / $yield;
    }

    // B. Biaya Packaging per porsi
    $stmt = $pdo->prepare("SELECT * FROM hpp_packagings WHERE hpp_id=?");
    $stmt->execute([$id]);
    $packagings = $stmt->fetchAll();
    $packagingCost = 0;
    foreach ($packagings as $pkg) {
        $cap = max(1, (int)$pkg['capacity_pcs']);
        $usage = max(1, (int)$pkg['usage_per_portion']);
        $packagingCost += ((float)$pkg['purchase_price'] / $cap) * $usage;
    }

    // C. Biaya Tambahan per porsi
    $stmt = $pdo->prepare("SELECT * FROM hpp_extra_costs WHERE hpp_id=?");
    $stmt->execute([$id]);
    $extras = $stmt->fetchAll();
    $extraCost = 0;
    foreach ($extras as $ec) {
        $div = max(1, (int)$ec['portions_divide']);
        $extraCost += (float)$ec['amount'] / $div;
    }

    // D. Overhead
    $subtotal = $ingredientCost + $packagingCost + $extraCost;
    $overheadPct = (float)($hpp['overhead_pct'] ?? 15);
    $overheadCost = $subtotal * ($overheadPct / 100);

    // HPP Total per porsi
    $hppTotal = $subtotal + $overheadCost;

    // Harga Jual Rekomendasi
    $marginPct = (float)($hpp['margin_pct'] ?? 30);
    $recommendedPrice = ($marginPct < 100) ? $hppTotal / (1 - $marginPct / 100) : $hppTotal * 2;

    // Analisis harga user
    $currentPrice = (float)($hpp['current_sell_price'] ?? 0);
    $actualMarginPct = 0;
    $actualProfit = 0;
    if ($currentPrice > 0 && $hppTotal > 0) {
        $actualProfit = $currentPrice - $hppTotal;
        $actualMarginPct = ($actualProfit / $currentPrice) * 100;
    }

    return [
        'ingredient_cost' => round($ingredientCost, 2),
        'packaging_cost'  => round($packagingCost, 2),
        'extra_cost'      => round($extraCost, 2),
        'subtotal'        => round($subtotal, 2),
        'overhead_pct'    => $overheadPct,
        'overhead_cost'   => round($overheadCost, 2),
        'hpp_total'       => round($hppTotal, 2),
        'margin_pct'      => $marginPct,
        'recommended_price' => round($recommendedPrice, 2),
        'current_price'   => $currentPrice,
        'actual_margin_pct' => round($actualMarginPct, 1),
        'actual_profit'   => round($actualProfit, 2),
    ];
}

// Helper singkat: hitung HPP total saja (untuk list)
function calcHppTotal($pdo, $hpp) {
    $calc = calcHppBreakdown($pdo, $hpp);
    return $calc['hpp_total'];
}
