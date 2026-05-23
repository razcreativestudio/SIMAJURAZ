<?php
/**
 * ============================================================
 * RAZpos.php — Point of Sale Interface SIMAJURAZ
 * ============================================================
 * Versi       : 1.0.0
 * Dibuat      : 2026-05-21
 * Diupdate    : 2026-05-21
 * Deskripsi   : Antarmuka kasir full-screen untuk transaksi.
 *               Layout 2 kolom: etalase barang (kiri) dan
 *               keranjang belanja (kanan). Mendukung pencarian
 *               cepat, barcode scanner, pembayaran multi-metode.
 *               Akses: Owner dan Employee.
 * ============================================================
 */
require_once __DIR__ . '/RAZconfig.php';
require_once __DIR__ . '/includes/RAZsession.php';
require_once __DIR__ . '/includes/RAZhelpers.php';
require_once __DIR__ . '/includes/RAZlang.php';
RAZrequireStoreAccess();
$user = RAZgetCurrentUser();
$currentPage = 'pos';
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kasir POS — SIMAJURAZ</title>
    <link rel="stylesheet" href="https://unpkg.com/@phosphor-icons/web@2.1.1/src/bold/style.css">
    <link rel="stylesheet" href="assets/css/RAZMain.css">
    <link rel="stylesheet" href="assets/css/RAZComponents.css">
    <link rel="stylesheet" href="assets/css/RAZModal.css">
    <link rel="stylesheet" href="assets/css/RAZPos.css">
</head>
<body class="pos-page <?= (isset($_COOKIE['raz_theme']) && $_COOKIE['raz_theme'] === 'dark') ? 'dark-mode' : '' ?>">
<div class="raz-app">
    <div class="raz-main">
        <!-- TOPBAR POS (Simplified) -->
        <header class="raz-topbar pos-topbar">
            <div class="raz-topbar-left">
                <a href="RAZdashboard.php" class="pos-back">
                    <i class="ph-bold ph-arrow-left"></i> <?= t('pos_back') ?>
                </a>
                <h1 class="raz-topbar-title" style="font-size:1rem;">
                    <i class="ph-bold ph-shopping-cart" style="color:var(--raz-primary)"></i> <?= t('pos_title') ?>
                </h1>
            </div>
            <div class="raz-topbar-right" style="display:flex; align-items:center;">
                    <!-- Language & Theme Shortcuts -->
                    <a href="?lang=<?= (isset($current_lang) && $current_lang === 'id') ? 'en' : 'id' ?>" class="nav-action-icon raz-btn-icon" style="color:var(--raz-text); font-size:1rem; font-weight:700; text-decoration:none; display:none; align-items:center; justify-content:center; width:36px; height:36px; border-radius:50%; border:1px solid var(--raz-border); margin-right: 8px;">
                        <?= strtoupper((isset($current_lang) && $current_lang === 'id') ? 'en' : 'id') ?>
                    </a>
                    <a href="#" id="theme-toggle" class="nav-action-icon raz-btn-icon" style="color:var(--raz-text); font-size:1.2rem; text-decoration:none; display:none; align-items:center; justify-content:center; width:36px; height:36px; border-radius:50%; border:1px solid var(--raz-border); margin-right: 16px;">
                        <i class="ph-bold ph-sun"></i>
                    </a>
                <span style="font-size:0.82rem;color:var(--raz-text-muted);">
                    <i class="ph-bold ph-user"></i> <?= htmlspecialchars($user['full_name']) ?>
                </span>
                <span class="raz-badge primary"><?= htmlspecialchars($user['store_name']) ?></span>
            </div>
        </header>

        <!-- KONTEN POS -->
        <main class="raz-content">
            <div class="pos-layout">
                <!-- ========== ETALASE BARANG (Kiri) ========== -->
                <div class="pos-products">
                    <!-- Search Bar -->
                    <div class="pos-search-bar">
                        <div class="pos-search-input" style="display:flex; gap:8px; align-items:center;">
                            <div style="position:relative; flex:1">
                                <span class="search-icon" style="position:absolute; left:12px; top:50%; transform:translateY(-50%);"><i class="ph-bold ph-barcode"></i></span>
                                <input type="text" id="posSearch" placeholder="<?= t('pos_search') ?>" autofocus style="width:100%; padding-left:35px; border-radius:8px; border:1px solid #d1d5db; height:40px;">
                            </div>
                            <button class="raz-btn raz-btn-primary" onclick="openScanner()" title="Scan via Kamera" style="height:40px; padding:0 15px;"><i class="ph-bold ph-camera"></i></button>
                        </div>
                    </div>

                    <!-- Category Filter Chips -->
                    <div class="pos-categories" id="posCats">
                        <button class="pos-cat-chip active"><?= t('pos_cat_all') ?></button>
                    </div>

                    <!-- Product Grid -->
                    <div class="pos-grid-wrap">
                        <div class="pos-grid" id="posGrid">
                            <!-- Skeleton loading -->
                            <div class="pos-item" style="pointer-events:none"><div class="raz-skeleton" style="height:80px;margin-bottom:10px"></div><div class="raz-skeleton raz-skeleton-text"></div><div class="raz-skeleton raz-skeleton-text" style="width:60%"></div></div>
                            <div class="pos-item" style="pointer-events:none"><div class="raz-skeleton" style="height:80px;margin-bottom:10px"></div><div class="raz-skeleton raz-skeleton-text"></div><div class="raz-skeleton raz-skeleton-text" style="width:60%"></div></div>
                            <div class="pos-item" style="pointer-events:none"><div class="raz-skeleton" style="height:80px;margin-bottom:10px"></div><div class="raz-skeleton raz-skeleton-text"></div><div class="raz-skeleton raz-skeleton-text" style="width:60%"></div></div>
                        </div>
                    </div>
                </div>

                <!-- ========== KERANJANG (Kanan) ========== -->
                <div class="pos-cart">
                    <div class="pos-cart-header">
                        <div class="pos-cart-title">
                            <i class="ph-bold ph-receipt"></i> <?= t('pos_cart_title') ?>
                            <span class="pos-cart-count" id="cartCount">0</span>
                        </div>
                        <button class="raz-btn raz-btn-ghost raz-btn-sm" onclick="clearCart()" data-tooltip="Kosongkan">
                            <i class="ph-bold ph-trash"></i>
                        </button>
                    </div>

                    <!-- Cart Items -->
                    <div class="pos-cart-items" id="cartItems">
                        <div class="pos-cart-empty">
                            <i class="ph-bold ph-shopping-cart"></i>
                            <p><?= t('pos_cart_empty') ?></p>
                        </div>
                    </div>

                    <!-- Cart Summary -->
                    <div class="pos-cart-summary" id="cartSummary" style="display:none;">
                        <div class="pos-summary-row">
                            <span class="pos-summary-label"><?= t('pos_subtotal') ?></span>
                            <span id="sumSubtotal">Rp 0</span>
                        </div>
                        <div class="pos-summary-row">
                            <span class="pos-summary-label" id="sumTaxLabel"><?= t('pos_tax') ?> (0%)</span>
                            <span id="sumTax">Rp 0</span>
                        </div>
                        <div class="pos-summary-row total">
                            <span><?= t('pos_total') ?></span>
                            <span id="sumTotal">Rp 0</span>
                        </div>

                        <button class="pos-pay-btn" id="payBtn" onclick="openPayment()" disabled>
                            <i class="ph-bold ph-credit-card"></i> <?= t('pos_pay') ?>
                        </button>
                    </div>
                </div>
            </div>
        </main>
    </div>
</div>

<!-- ==================== MODAL: Pembayaran ==================== -->
<div class="raz-modal-overlay" id="payModal">
    <div class="raz-modal">
        <div class="raz-modal-header">
            <div class="raz-modal-title"><i class="ph-bold ph-credit-card modal-icon"></i> <?= t('pos_modal_payment') ?></div>
            <button class="raz-modal-close" onclick="RAZ.closeModal('payModal')"><i class="ph-bold ph-x"></i></button>
        </div>
        <div class="raz-modal-body">
            <!-- Total -->
            <div style="text-align:center;margin-bottom:20px;">
                <div style="font-size:0.85rem;color:var(--raz-text-muted);"><?= t('pos_total_pay') ?></div>
                <div style="font-size:2rem;font-weight:800;color:var(--raz-primary);" id="payTotal">Rp 0</div>
            </div>

            <!-- Metode Pembayaran -->
            <div class="pay-method-grid">
                <div class="pay-method-card selected" data-method="cash" onclick="selectPayMethod('cash')">
                    <i class="ph-bold ph-money"></i><span><?= t('pos_pay_cash') ?></span>
                </div>
                <div class="pay-method-card" data-method="transfer" onclick="selectPayMethod('transfer')">
                    <i class="ph-bold ph-bank"></i><span><?= t('pos_pay_transfer') ?></span>
                </div>
                <div class="pay-method-card" data-method="qris" onclick="selectPayMethod('qris')">
                    <i class="ph-bold ph-qr-code"></i><span>QRIS</span>
                </div>
            </div>

            <!-- Input Nominal (Cash Only) -->
            <div id="cashInputGroup">
                <div class="raz-form-group">
                    <label class="raz-form-label"><?= t('pos_amount_paid') ?></label>
                    <input type="number" id="payAmount" class="raz-form-input" placeholder="<?= t('pos_placeholder_amount') ?>" oninput="calculateChange()" style="font-size:1.2rem;font-weight:700;text-align:center;">
                </div>
                <!-- Quick Amount Buttons -->
                <div style="display:flex;gap:6px;flex-wrap:wrap;margin-bottom:12px;">
                    <button class="raz-btn raz-btn-secondary raz-btn-sm" onclick="document.getElementById('payAmount').value=10000;calculateChange()">10k</button>
                    <button class="raz-btn raz-btn-secondary raz-btn-sm" onclick="document.getElementById('payAmount').value=20000;calculateChange()">20k</button>
                    <button class="raz-btn raz-btn-secondary raz-btn-sm" onclick="document.getElementById('payAmount').value=50000;calculateChange()">50k</button>
                    <button class="raz-btn raz-btn-secondary raz-btn-sm" onclick="document.getElementById('payAmount').value=100000;calculateChange()">100k</button>
                    <button class="raz-btn raz-btn-secondary raz-btn-sm" onclick="setExactAmount()"><?= t('pos_exact_amount') ?></button>
                </div>
            </div>

            <!-- Kembalian -->
            <div class="pay-change" id="payChange" style="display:none;">
                <div class="pay-change-label"><?= t('pos_change') ?></div>
                <div class="pay-change-amount" id="changeAmount">Rp 0</div>
            </div>
        </div>
        <div class="raz-modal-footer">
            <button class="raz-btn raz-btn-secondary" onclick="RAZ.closeModal('payModal')"><i class="ph-bold ph-x"></i> <?= t('pos_btn_cancel') ?></button>
            <button class="raz-btn raz-btn-success raz-btn-lg" id="btnProcessPay" onclick="processPayment()">
                <i class="ph-bold ph-check-circle"></i> <?= t('pos_btn_process') ?>
            </button>
        </div>
    </div>
</div>

<!-- ==================== MODAL: Struk ==================== -->
<div class="raz-modal-overlay" id="receiptModal">
    <div class="raz-modal modal-sm">
        <div class="raz-modal-header">
            <div class="raz-modal-title"><i class="ph-bold ph-receipt modal-icon"></i> <?= t('pos_modal_receipt') ?></div>
            <button class="raz-modal-close" onclick="RAZ.closeModal('receiptModal')"><i class="ph-bold ph-x"></i></button>
        </div>
        <div class="raz-modal-body" id="receiptContent"></div>
        <div class="raz-modal-footer" style="justify-content:center; flex-wrap:wrap; gap:8px;">
            <button class="raz-btn raz-btn-secondary" onclick="RAZ.closeModal('receiptModal')"><i class="ph-bold ph-x"></i> <?= t('pos_btn_close') ?></button>
            <button class="raz-btn" style="background:#10B981; color:white; border-color:#10B981;" onclick="downloadReceipt()"><i class="ph-bold ph-download-simple"></i> Unduh</button>
            <button class="raz-btn raz-btn-success" onclick="shareReceipt()"><i class="ph-bold ph-whatsapp-logo"></i> <?= t('pos_btn_share') ?></button>
            <button class="raz-btn raz-btn-primary" onclick="printReceipt()"><i class="ph-bold ph-printer"></i> <?= t('pos_btn_print') ?></button>
        </div>
    </div>
</div>
</div>

<!-- ==================== MODAL: Scanner Kamera ==================== -->
<div class="raz-modal-overlay" id="scannerModal">
    <div class="raz-modal modal-sm">
        <div class="raz-modal-header">
            <div class="raz-modal-title"><i class="ph-bold ph-camera modal-icon"></i> <?= t('pos_modal_scanner') ?></div>
            <button class="raz-modal-close" onclick="closeScanner()"><i class="ph-bold ph-x"></i></button>
        </div>
        <div class="raz-modal-body" style="padding:0; text-align:center;">
            <div id="reader" style="width:100%; min-height:250px;"></div>
        </div>
        <div class="raz-modal-footer" style="justify-content:center;">
            <button class="raz-btn raz-btn-secondary" onclick="closeScanner()"><i class="ph-bold ph-x"></i> <?= t('pos_btn_close_camera') ?></button>
        </div>
    </div>
</div>

<script>
window.POS_LANG = {
    all: "<?= t('pos_cat_all') ?>",
    cart_empty: "<?= t('pos_cart_empty') ?>",
    out_stock: "<?= t('pos_out_stock') ?>",
    stock: "<?= t('pos_stock') ?>",
    stock_limit: "<?= t('pos_stock_limit') ?>",
    stock_warning: "<?= t('pos_stock_warning') ?>",
    only_left: "<?= t('pos_only_left') ?>",
    exceed_stock: "<?= t('pos_exceed_stock') ?>",
    cart_cleared: "<?= t('pos_cart_cleared') ?>"
};
</script>
<script src="assets/js/RAZMain.js?v=<?= time() ?>"></script>
<script src="assets/js/RAZPos.js?v=<?= time() ?>"></script>
<script src="https://unpkg.com/html5-qrcode" type="text/javascript"></script>
<script>
// Set uang pas ke total
function setExactAmount() {
  const totalText = document.getElementById('payTotal')?.textContent || '0';
  const total = parseInt(totalText.replace(/[^\d]/g, '')) || 0;
  document.getElementById('payAmount').value = total;
  calculateChange();
}
</script>
</body>
</html>
