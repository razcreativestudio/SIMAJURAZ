<?php
/**
 * ============================================================
 * api/RAZapiTransactions.php — API Transaksi POS SIMAJURAZ
 * ============================================================
 * Versi       : 1.0.0
 * Dibuat      : 2026-05-21
 * Diupdate    : 2026-05-21
 * Deskripsi   : Endpoint API untuk transaksi POS.
 *               - GET  ?action=products     → Daftar barang untuk POS
 *               - POST ?action=checkout     → Proses pembayaran
 *               - GET  ?action=receipt&id=X → Data struk
 *               - POST ?action=void         → Void transaksi
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
    case 'products':  getProducts($pdo, $storeId); break;
    case 'checkout':  processCheckout($pdo, $storeId, $user); break;
    case 'receipt':   getReceipt($pdo, $storeId); break;
    case 'void':      voidTransaction($pdo, $storeId, $user); break;
    case 'store_info': getStoreInfo($pdo, $storeId); break;
    default: RAZjsonResponse(false, 'Aksi tidak valid', [], 400);
}

// ============================================================
// Daftar Produk untuk POS (aktif, ada stok)
// ============================================================
function getProducts($pdo, $storeId) {
    $search = $_GET['search'] ?? '';
    $category = $_GET['category'] ?? '';

    $where = "WHERE i.store_id = ? AND i.is_active = 1";
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

    $stmt = $pdo->prepare("SELECT i.id, i.name, i.sku, i.sell_price, i.hpp, i.stock, i.image, 
                           i.category_id, c.name as category_name, c.color as category_color
                           FROM items i LEFT JOIN categories c ON i.category_id = c.id 
                           {$where} ORDER BY i.name ASC");
    $stmt->execute($params);

    // Ambil kategori juga untuk filter
    $catStmt = $pdo->prepare("SELECT id, name, color FROM categories WHERE store_id = ? ORDER BY name");
    $catStmt->execute([$storeId]);

    RAZjsonResponse(true, 'OK', [
        'products'   => $stmt->fetchAll(),
        'categories' => $catStmt->fetchAll(),
    ]);
}

// ============================================================
// Proses Checkout / Pembayaran
// ============================================================
function processCheckout($pdo, $storeId, $user) {
    $input = RAZgetJsonInput();
    $items = $input['items'] ?? [];
    $paymentMethod = $input['payment_method'] ?? 'cash';
    $amountPaid = floatval($input['amount_paid'] ?? 0);
    $discountAmount = floatval($input['discount_amount'] ?? 0);

    // Validasi keranjang tidak kosong
    if (empty($items)) RAZjsonResponse(false, 'Keranjang belanja kosong');

    // Ambil info pajak toko & format invoice
    $storeStmt = $pdo->prepare("SELECT tax_percentage, invoice_format FROM stores WHERE id = ?");
    $storeStmt->execute([$storeId]);
    $store = $storeStmt->fetch();
    $taxPct = floatval($store['tax_percentage'] ?? 0);
    $invoiceFormat = trim($store['invoice_format'] ?? 'INV-{Ymd}-{SEQ5}');

    try {
        $pdo->beginTransaction();

        $subtotal = 0;
        $transactionItems = [];

        // Validasi setiap item dan hitung subtotal
        foreach ($items as $item) {
            $itemId = intval($item['id']);
            $qty = intval($item['qty']);
            if ($qty <= 0) continue;

            // Ambil data barang terbaru dari database
            $itemStmt = $pdo->prepare("SELECT * FROM items WHERE id = ? AND store_id = ? AND is_active = 1");
            $itemStmt->execute([$itemId, $storeId]);
            $product = $itemStmt->fetch();

            if (!$product) {
                $pdo->rollBack();
                RAZjsonResponse(false, "Barang dengan ID {$itemId} tidak ditemukan");
                return;
            }

            // Cek stok cukup
            if ($product['stock'] < $qty) {
                $pdo->rollBack();
                RAZjsonResponse(false, "Stok '{$product['name']}' tidak cukup (sisa: {$product['stock']})");
                return;
            }

            $lineTotal = $product['sell_price'] * $qty;
            $subtotal += $lineTotal;

            $transactionItems[] = [
                'item_id'    => $itemId,
                'item_name'  => $product['name'],
                'qty'        => $qty,
                'hpp'        => $product['hpp'],
                'sell_price' => $product['sell_price'],
                'subtotal'   => $lineTotal,
            ];

            // Kurangi stok (FIFO)
            $pdo->prepare("UPDATE items SET stock = stock - ? WHERE id = ?")->execute([$qty, $itemId]);
        }

        // Hitung pajak dan grand total
        $taxAmount = round($subtotal * ($taxPct / 100), 0);
        $grandTotal = $subtotal - $discountAmount + $taxAmount;
        $changeAmount = max(0, $amountPaid - $grandTotal);

        // Validasi pembayaran cukup
        if ($paymentMethod === 'cash' && $amountPaid < $grandTotal) {
            $pdo->rollBack();
            RAZjsonResponse(false, 'Pembayaran kurang. Kurang: Rp ' . number_format($grandTotal - $amountPaid, 0, ',', '.'));
            return;
        }

        // Generate nomor invoice
        $invoiceNumber = RAZgenerateInvoice($invoiceFormat, $storeId, $pdo);

        // Insert transaksi
        $transStmt = $pdo->prepare("INSERT INTO transactions 
            (store_id, user_id, invoice_number, subtotal, discount_amount, tax_amount, grand_total, payment_method, amount_paid, change_amount, status) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'completed')");
        $transStmt->execute([$storeId, $user['id'], $invoiceNumber, $subtotal, $discountAmount, $taxAmount, $grandTotal, $paymentMethod, $amountPaid, $changeAmount]);
        $transId = $pdo->lastInsertId();

        // Insert detail item transaksi
        $detailStmt = $pdo->prepare("INSERT INTO transaction_items (transaction_id, item_id, item_name, qty, hpp, sell_price, subtotal) VALUES (?, ?, ?, ?, ?, ?, ?)");
        foreach ($transactionItems as $ti) {
            $detailStmt->execute([$transId, $ti['item_id'], $ti['item_name'], $ti['qty'], $ti['hpp'], $ti['sell_price'], $ti['subtotal']]);
        }

        $pdo->commit();

        RAZjsonResponse(true, 'Transaksi berhasil!', [
            'transaction_id'  => $transId,
            'invoice_number'  => $invoiceNumber,
            'grand_total'     => $grandTotal,
            'change_amount'   => $changeAmount,
            'payment_method'  => $paymentMethod,
        ]);

    } catch (Exception $e) {
        $pdo->rollBack();
        RAZjsonResponse(false, 'Transaksi gagal: ' . $e->getMessage(), [], 500);
    }
}

// ============================================================
// Data Struk / Receipt
// ============================================================
function getReceipt($pdo, $storeId) {
    $id = intval($_GET['id'] ?? 0);
    
    $trans = $pdo->prepare("SELECT t.*, u.full_name as cashier_name FROM transactions t JOIN users u ON t.user_id = u.id WHERE t.id = ? AND t.store_id = ?");
    $trans->execute([$id, $storeId]);
    $transaction = $trans->fetch();
    if (!$transaction) RAZjsonResponse(false, 'Transaksi tidak ditemukan');

    $items = $pdo->prepare("SELECT * FROM transaction_items WHERE transaction_id = ?");
    $items->execute([$id]);

    $store = $pdo->prepare("SELECT * FROM stores WHERE id = ?");
    $store->execute([$storeId]);

    RAZjsonResponse(true, 'OK', [
        'transaction' => $transaction,
        'items'       => $items->fetchAll(),
        'store'       => $store->fetch(),
    ]);
}

// ============================================================
// Void Transaksi (Batalkan)
// ============================================================
function voidTransaction($pdo, $storeId, $user) {
    if ($user['role'] !== 'owner') RAZjsonResponse(false, 'Hanya Owner yang bisa void transaksi', [], 403);

    $input = RAZgetJsonInput();
    $id = intval($input['id'] ?? 0);

    $trans = $pdo->prepare("SELECT * FROM transactions WHERE id = ? AND store_id = ? AND status = 'completed'");
    $trans->execute([$id, $storeId]);
    if (!$trans->fetch()) RAZjsonResponse(false, 'Transaksi tidak ditemukan atau sudah void');

    $pdo->beginTransaction();
    // Kembalikan stok
    $items = $pdo->prepare("SELECT item_id, qty FROM transaction_items WHERE transaction_id = ?");
    $items->execute([$id]);
    foreach ($items->fetchAll() as $item) {
        $pdo->prepare("UPDATE items SET stock = stock + ? WHERE id = ?")->execute([$item['qty'], $item['item_id']]);
    }
    // Update status
    $pdo->prepare("UPDATE transactions SET status = 'voided' WHERE id = ?")->execute([$id]);
    $pdo->commit();

    RAZjsonResponse(true, 'Transaksi berhasil di-void');
}

// ============================================================
// Info Toko (untuk struk)
// ============================================================
function getStoreInfo($pdo, $storeId) {
    $stmt = $pdo->prepare("SELECT * FROM stores WHERE id = ?");
    $stmt->execute([$storeId]);
    RAZjsonResponse(true, 'OK', $stmt->fetch());
}
