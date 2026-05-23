<?php
/**
 * ============================================================
 * RAZreceipt.php — Cetak Struk Transaksi SIMAJURAZ
 * ============================================================
 * Versi       : 1.0.0
 * Dibuat      : 2026-05-22
 * Deskripsi   : Halaman cetak struk bergaya thermal printer
 *               mendukung lebar 58mm / 80mm.
 * ============================================================
 */
require_once __DIR__ . '/RAZconfig.php';
require_once __DIR__ . '/includes/RAZsession.php';
require_once __DIR__ . '/includes/RAZhelpers.php';

RAZrequireStoreAccess();

$id = intval($_GET['id'] ?? 0);
if (!$id) {
    die("ID Transaksi tidak valid.");
}

$pdo = RAZgetConnection();
$user = RAZgetCurrentUser();
$storeId = $user['store_id'];

// Ambil Transaksi
$stmt = $pdo->prepare("SELECT t.*, u.full_name as cashier_name FROM transactions t JOIN users u ON t.user_id = u.id WHERE t.id = ? AND t.store_id = ?");
$stmt->execute([$id, $storeId]);
$transaction = $stmt->fetch();

if (!$transaction) {
    die("Transaksi tidak ditemukan atau Anda tidak memiliki akses.");
}

// Ambil Item
$stmt = $pdo->prepare("SELECT * FROM transaction_items WHERE transaction_id = ?");
$stmt->execute([$id]);
$items = $stmt->fetchAll();

// Ambil Toko
$stmt = $pdo->prepare("SELECT * FROM stores WHERE id = ?");
$stmt->execute([$storeId]);
$store = $stmt->fetch();

$logoUrl = $store['store_logo'] ? 'uploads/logos/' . htmlspecialchars($store['store_logo']) : null;
$showLogo = (int)($store['receipt_show_logo'] ?? 1);
$header = trim($store['receipt_header'] ?? '');
$footer = trim($store['receipt_footer'] ?? '');
$templateId = (int)($store['receipt_template'] ?? 1);

// Decode template properties (matched with RAZSettings.js logic)
$fonts = ['mono', 'sans'];
$aligns = ['center', 'left'];
$seps = ['dash', 'solid', 'double'];
$widths = ['58mm', '80mm'];

$count = 1;
$tplFont = 'mono';
$tplAlign = 'center';
$tplSep = 'dash';
$tplWidth = '58mm';

foreach ($fonts as $f) {
    foreach ($aligns as $a) {
        foreach ($seps as $s) {
            foreach ($widths as $w) {
                if ($count === $templateId) {
                    $tplFont = $f;
                    $tplAlign = $a;
                    $tplSep = $s;
                    $tplWidth = $w;
                }
                $count++;
            }
        }
    }
}

// Convert to actual styles
$cssFont = ($tplFont === 'sans') ? "font-family: Arial, Helvetica, sans-serif;" : "font-family: 'Courier New', Courier, monospace;";
$cssAlign = ($tplAlign === 'left') ? "text-align: left;" : "text-align: center;";
$cssWidth = ($tplWidth === '80mm') ? "300px" : "220px";

$sepBorder = "1px dashed #000";
if ($tplSep === 'solid') $sepBorder = "1px solid #000";
elseif ($tplSep === 'double') $sepBorder = "3px double #000";
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Struk - <?= htmlspecialchars($transaction['invoice_number']) ?></title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            <?= $cssFont ?>
            font-size: 12px;
            color: #000;
            background: #f0f0f0;
            padding: 20px;
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: flex-start;
        }

        /* Thermal Printer Wrapper */
        .receipt-wrapper {
            background: #fff;
            width: <?= $cssWidth ?>;
            min-height: calc(100vh - 40px);
            padding: 20px;
            box-shadow: 0 4px 10px rgba(0,0,0,0.1);
            position: relative;
        }

        .receipt-content {
            <?= $cssAlign ?>
        }

        /* Header */
        .r-header { margin-bottom: 10px; }
        .r-logo { max-width: 100px; margin-bottom: 8px; filter: grayscale(100%); }
        .r-store-name { font-size: 16px; font-weight: bold; text-transform: uppercase; margin-bottom: 4px; }
        .r-store-info { font-size: 11px; margin-bottom: 2px; }
        .r-custom-header { font-size: 11px; margin-top: 5px; white-space: pre-line; }

        /* Meta Info */
        .r-meta { margin: 15px 0; font-size: 11px; border-top: <?= $sepBorder ?>; border-bottom: <?= $sepBorder ?>; padding: 8px 0; }
        .r-meta-row { display: flex; justify-content: space-between; margin-bottom: 3px; text-align: left; }

        /* Items */
        .r-items { width: 100%; border-collapse: collapse; margin-bottom: 10px; text-align: left; }
        .r-items th { text-align: right; border-bottom: <?= $sepBorder ?>; padding-bottom: 4px; font-weight: normal; }
        .r-items th:first-child { text-align: left; }
        .r-items td { padding: 4px 0; vertical-align: top; }
        .r-item-name { display: block; font-weight: bold; margin-bottom: 2px; }
        .r-item-calc { font-size: 10px; }
        .r-item-subtotal { text-align: right; }

        /* Totals */
        .r-totals { border-top: <?= $sepBorder ?>; padding-top: 8px; margin-bottom: 15px; text-align: left; }
        .r-total-row { display: flex; justify-content: space-between; margin-bottom: 4px; font-size: 11px; }
        .r-grand-total { font-size: 14px; font-weight: bold; margin-top: 5px; padding-top: 5px; border-top: <?= $sepBorder ?>; }

        /* Footer */
        .r-footer { font-size: 11px; border-top: <?= $sepBorder ?>; padding-top: 10px; white-space: pre-line; }
        .r-thanks { font-weight: bold; margin-top: 5px; }

        /* Print Controls */
        .no-print { text-align: center; margin-bottom: 20px; }
        .btn { padding: 8px 16px; border: none; background: #4F46E5; color: white; border-radius: 4px; cursor: pointer; font-family: sans-serif; margin: 0 5px; }
        .btn:hover { background: #4338CA; }
        .btn-secondary { background: #6B7280; }
        .btn-secondary:hover { background: #4B5563; }

        @media print {
            body { background: transparent; padding: 0; }
            .receipt-wrapper { box-shadow: none; width: 100%; padding: 0; margin: 0; }
            .no-print { display: none; }
        }
    </style>
</head>
<body>

<div>
    <div class="no-print">
        <button class="btn" onclick="window.print()">🖨️ Cetak Struk</button>
        <button class="btn btn-secondary" onclick="window.close()">Tutup</button>
    </div>

    <div class="receipt-wrapper" style="<?= (in_array($showLogo, [7, 8]) && $logoUrl) ? "position:relative; z-index:1;" : "" ?>">
        <?php if ($logoUrl && in_array($showLogo, [7, 8])): ?>
            <div style="
                position: absolute; 
                top:0; left:0; right:0; bottom:0; 
                z-index: 0; 
                pointer-events: none;
                opacity: 0.15;
                filter: grayscale(100%);
                <?= $showLogo == 7 ? "background: url('{$logoUrl}') no-repeat center center; background-size: 70%;" : "background: url('{$logoUrl}') repeat; background-size: 50px 50px;" ?>
            "></div>
        <?php endif; ?>

        <div class="receipt-content" style="position: relative; z-index: 1;">
            <div class="r-header">
                <?php 
                if ($logoUrl && in_array($showLogo, [1, 2, 3])) {
                    $align = $showLogo == 2 ? 'left' : ($showLogo == 3 ? 'right' : 'center');
                    echo "<div style='text-align: {$align}; margin-bottom: 10px;'><img src='{$logoUrl}' class='r-logo' style='max-width:80%; max-height:60px; object-fit:contain;' alt='Logo'></div>";
                } elseif ($logoUrl && $showLogo == 1) { // Fallback
                    echo "<img src='{$logoUrl}' class='r-logo' alt='Logo'>";
                }
                ?>
                <div class="r-store-name"><?= htmlspecialchars($store['store_name']) ?></div>
                <?php if ($store['store_address']): ?>
                    <div class="r-store-info"><?= htmlspecialchars($store['store_address']) ?></div>
                <?php endif; ?>
                <?php if ($store['store_phone']): ?>
                    <div class="r-store-info">Telp: <?= htmlspecialchars($store['store_phone']) ?></div>
                <?php endif; ?>
                <?php if ($header): ?>
                    <div class="r-custom-header"><?= nl2br(htmlspecialchars($header)) ?></div>
                <?php endif; ?>
            </div>

            <div class="r-meta">
                <div class="r-meta-row"><span>No.</span> <span><?= htmlspecialchars($transaction['invoice_number']) ?></span></div>
                <div class="r-meta-row"><span>Tgl.</span> <span><?= date('d-m-Y H:i', strtotime($transaction['created_at'])) ?></span></div>
                <div class="r-meta-row"><span>Kasir</span> <span><?= htmlspecialchars($transaction['cashier_name']) ?></span></div>
            </div>

            <table class="r-items">
                <thead>
                    <tr>
                        <th>Item</th>
                        <th style="width:30%">Total</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($items as $item): ?>
                    <tr>
                        <td>
                            <span class="r-item-name"><?= htmlspecialchars($item['item_name']) ?></span>
                            <span class="r-item-calc"><?= $item['qty'] ?> x <?= number_format($item['sell_price'], 0, ',', '.') ?></span>
                        </td>
                        <td class="r-item-subtotal"><?= number_format($item['subtotal'], 0, ',', '.') ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>

        <div class="r-totals">
            <div class="r-total-row"><span>Subtotal</span> <span><?= number_format($transaction['subtotal'], 0, ',', '.') ?></span></div>
            <?php if ($transaction['discount_amount'] > 0): ?>
            <div class="r-total-row"><span>Diskon</span> <span>-<?= number_format($transaction['discount_amount'], 0, ',', '.') ?></span></div>
            <?php endif; ?>
            <?php if ($transaction['tax_amount'] > 0): ?>
            <div class="r-total-row"><span>Pajak</span> <span><?= number_format($transaction['tax_amount'], 0, ',', '.') ?></span></div>
            <?php endif; ?>
            
            <div class="r-total-row r-grand-total"><span>TOTAL</span> <span><?= number_format($transaction['grand_total'], 0, ',', '.') ?></span></div>
            
            <div class="r-total-row" style="margin-top:5px;"><span><?= strtoupper($transaction['payment_method']) ?></span> <span><?= number_format($transaction['amount_paid'], 0, ',', '.') ?></span></div>
            <div class="r-total-row"><span>KEMBALI</span> <span><?= number_format($transaction['change_amount'], 0, ',', '.') ?></span></div>
        </div>

        <div class="r-footer">
            <?php 
            if ($logoUrl && in_array($showLogo, [4, 5, 6])) {
                $align = $showLogo == 5 ? 'left' : ($showLogo == 6 ? 'right' : 'center');
                echo "<div style='text-align: {$align}; margin-top: 10px; margin-bottom: 5px;'><img src='{$logoUrl}' class='r-logo' style='max-width:80%; max-height:60px; object-fit:contain;' alt='Logo'></div>";
            }
            ?>
            <?php if ($footer): ?>
                <div><?= nl2br(htmlspecialchars($footer)) ?></div>
            <?php endif; ?>
            <div class="r-thanks">-- TERIMA KASIH --</div>
        </div>
        </div> <!-- End of receipt-content -->
    </div>
</div>

<?php if (!isset($_GET['preview'])): ?>
<script>
    // Auto print when loaded directly (not in preview iframe)
    window.onload = function() {
        setTimeout(function() {
            window.print();
        }, 500);
    };
</script>
<?php endif; ?>

</body>
</html>
