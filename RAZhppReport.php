<?php
/**
 * ============================================================
 * RAZhppReport.php — Laporan Kalkulasi HPP Profesional
 * ============================================================
 * Versi       : 1.0.0
 * Dibuat      : 2026-05-22
 * Deskripsi   : Menghasilkan layout A4 / Letter untuk dicetak.
 * ============================================================
 */
require_once __DIR__ . '/RAZconfig.php';
require_once __DIR__ . '/includes/RAZsession.php';
require_once __DIR__ . '/includes/RAZhelpers.php';

RAZrequireOwner();

$user = RAZgetCurrentUser();
$storeId = $user['store_id'];
$pdo = RAZgetConnection();

$mode = $_GET['mode'] ?? 'all';
$ids = $_GET['ids'] ?? '';
$showSuggest = isset($_GET['suggest']) ? (int)$_GET['suggest'] : 1;

// Ambil data toko
$stmt = $pdo->prepare("SELECT * FROM stores WHERE id = ?");
$stmt->execute([$storeId]);
$store = $stmt->fetch();

$logoUrl = $store['store_logo'] ? 'uploads/logos/' . htmlspecialchars($store['store_logo']) : null;

// Query HPP
$sql = "SELECT * FROM hpp_calculations WHERE store_id = ?";
$params = [$storeId];

if ($mode === 'selected' || $mode === 'single') {
    $idArray = explode(',', $ids);
    $idArray = array_filter(array_map('intval', $idArray));
    if (!empty($idArray)) {
        $placeholders = str_repeat('?,', count($idArray) - 1) . '?';
        $sql .= " AND id IN ($placeholders)";
        $params = array_merge($params, $idArray);
    } else {
        die("Tidak ada ID yang dipilih.");
    }
}

$sql .= " ORDER BY product_name ASC";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$calculations = $stmt->fetchAll();

if (empty($calculations)) {
    die("Data HPP tidak ditemukan.");
}

// Helper untuk kalkulasi HPP secara server-side
function calculateHppDetail($pdo, $hpp) {
    $id = $hpp['id'];
    
    // Ingredients
    $stmt = $pdo->prepare("SELECT * FROM hpp_ingredients WHERE hpp_id = ? ORDER BY sort_order ASC");
    $stmt->execute([$id]);
    $ingredients = $stmt->fetchAll();
    
    $ing_cost = 0;
    foreach ($ingredients as &$ing) {
        $ing['subtotal'] = 0;
        if ($ing['portions_yield'] > 0) {
            $ing['subtotal'] = $ing['purchase_price'] / $ing['portions_yield'];
        }
        $ing_cost += $ing['subtotal'];
    }
    
    // Packaging
    $stmt = $pdo->prepare("SELECT * FROM hpp_packagings WHERE hpp_id = ? ORDER BY sort_order ASC");
    $stmt->execute([$id]);
    $packagings = $stmt->fetchAll();
    
    $pkg_cost = 0;
    foreach ($packagings as &$pkg) {
        $pkg['subtotal'] = 0;
        if ($pkg['purchase_qty'] > 0 && $pkg['capacity_pcs'] > 0) {
            // Price per pc
            $price_per_pc = $pkg['purchase_price'] / ($pkg['purchase_qty'] * $pkg['capacity_pcs']);
            // Usage per portion
            $pkg['subtotal'] = $price_per_pc * $pkg['usage_per_portion'];
        }
        $pkg_cost += $pkg['subtotal'];
    }
    
    // Extra Costs
    $stmt = $pdo->prepare("SELECT * FROM hpp_extra_costs WHERE hpp_id = ? ORDER BY sort_order ASC");
    $stmt->execute([$id]);
    $extras = $stmt->fetchAll();
    
    $ext_cost = 0;
    foreach ($extras as &$ext) {
        $ext['subtotal'] = 0;
        if (isset($ext['portions_divide']) && $ext['portions_divide'] > 0) {
            $ext['subtotal'] = $ext['amount'] / $ext['portions_divide'];
        }
        $ext_cost += $ext['subtotal'];
    }
    
    $base_hpp = $ing_cost + $pkg_cost + $ext_cost;
    $overhead_cost = $base_hpp * ($hpp['overhead_pct'] / 100);
    $total_hpp = $base_hpp + $overhead_cost;
    
    $margin_cost = $total_hpp * ($hpp['margin_pct'] / 100);
    $suggested_price = $total_hpp + $margin_cost;
    
    // Pembulatan harga jual (ke 500 terdekat ke atas)
    $suggested_rounded = ceil($suggested_price / 500) * 500;
    
    $actual_price = $hpp['current_sell_price'];
    $actual_profit = $actual_price > 0 ? $actual_price - $total_hpp : 0;
    $actual_margin_pct = $total_hpp > 0 && $actual_price > 0 ? ($actual_profit / $total_hpp) * 100 : 0;
    
    return [
        'base' => $hpp,
        'ingredients' => $ingredients,
        'packagings' => $packagings,
        'extras' => $extras,
        'ing_cost' => $ing_cost,
        'pkg_cost' => $pkg_cost,
        'ext_cost' => $ext_cost,
        'overhead_cost' => $overhead_cost,
        'total_hpp' => $total_hpp,
        'margin_cost' => $margin_cost,
        'suggested_price' => $suggested_price,
        'suggested_rounded' => $suggested_rounded,
        'actual_price' => $actual_price,
        'actual_profit' => $actual_profit,
        'actual_margin_pct' => round($actual_margin_pct, 1)
    ];
}

$fullData = [];
$totalAllHpp = 0;
foreach ($calculations as $c) {
    $detail = calculateHppDetail($pdo, $c);
    $fullData[] = $detail;
    $totalAllHpp += $detail['total_hpp'];
}

?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan HPP - <?= htmlspecialchars($store['store_name']) ?></title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary: #4F46E5;
            --primary-dark: #4338CA;
            --text-main: #1F2937;
            --text-muted: #6B7280;
            --border: #E5E7EB;
            --bg: #F3F4F6;
            --success: #10B981;
            --danger: #EF4444;
            --warning: #F59E0B;
        }

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
            font-family: 'Inter', sans-serif;
        }

        body {
            background-color: var(--bg);
            color: var(--text-main);
            line-height: 1.5;
            font-size: 11pt;
            padding: 20px;
        }

        .page-container {
            width: 100%;
            max-width: 210mm; /* A4 Width */
            margin: 0 auto;
            background: #fff;
            padding: 20mm;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
            border-radius: 8px;
        }

        /* Header */
        .report-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            border-bottom: 2px solid var(--primary);
            padding-bottom: 15px;
            margin-bottom: 20px;
        }

        .store-info h1 {
            font-size: 24pt;
            color: var(--primary-dark);
            margin-bottom: 5px;
        }

        .store-info p {
            color: var(--text-muted);
            font-size: 10pt;
        }

        .report-meta {
            text-align: right;
        }
        
        .report-meta h2 {
            font-size: 16pt;
            color: var(--text-main);
            margin-bottom: 5px;
        }

        /* Summary Table */
        .summary-section {
            margin-bottom: 30px;
        }

        .summary-title {
            font-size: 14pt;
            font-weight: 600;
            margin-bottom: 10px;
            color: var(--primary-dark);
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
            font-size: 10pt;
        }

        th {
            background: var(--bg);
            color: var(--text-main);
            font-weight: 600;
            text-align: left;
            padding: 10px;
            border: 1px solid var(--border);
        }

        td {
            padding: 10px;
            border: 1px solid var(--border);
        }

        .text-right { text-align: right; }
        .text-center { text-align: center; }

        /* Detail Card */
        .hpp-card {
            border: 1px solid var(--border);
            border-radius: 8px;
            margin-bottom: 20px;
            page-break-inside: avoid;
            overflow: hidden;
        }

        .hpp-card-header {
            background: var(--primary);
            color: #fff;
            padding: 12px 15px;
            font-size: 14pt;
            font-weight: 600;
            display: flex;
            justify-content: space-between;
        }

        .hpp-card-body {
            padding: 15px;
        }

        .detail-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 15px;
        }

        .detail-col h4 {
            font-size: 11pt;
            color: var(--text-muted);
            margin-bottom: 8px;
            border-bottom: 1px solid var(--border);
            padding-bottom: 4px;
        }
        
        .detail-table th, .detail-table td {
            padding: 6px;
            font-size: 9pt;
        }

        /* Final Calculation Box */
        .calc-box {
            background: var(--bg);
            border: 1px solid var(--border);
            border-radius: 6px;
            padding: 15px;
            margin-top: 15px;
        }

        .calc-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 5px;
            font-size: 10pt;
        }
        
        .calc-row.total {
            font-weight: 700;
            font-size: 12pt;
            border-top: 1px dashed var(--border);
            padding-top: 5px;
            margin-top: 5px;
        }

        .calc-row.margin {
            color: var(--success);
        }
        
        .calc-row.price {
            color: var(--primary-dark);
            font-weight: 700;
        }

        .status-badge {
            display: inline-block;
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 9pt;
            font-weight: 600;
            color: #fff;
        }
        .status-good { background: var(--success); }
        .status-warning { background: var(--warning); }
        .status-danger { background: var(--danger); }

        /* Print Controls */
        .no-print {
            text-align: center;
            margin-bottom: 20px;
        }

        .btn {
            padding: 10px 20px;
            border: none;
            background: var(--primary);
            color: white;
            border-radius: 6px;
            cursor: pointer;
            font-weight: 600;
            font-size: 11pt;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }

        .btn:hover { background: var(--primary-dark); }

        /* Print CSS */
        @media print {
            body {
                background: transparent;
                padding: 0;
            }
            .page-container {
                box-shadow: none;
                margin: 0;
                padding: 0;
                width: 100%;
                max-width: 100%;
            }
            .no-print {
                display: none;
            }
            @page {
                size: A4 portrait;
                margin: 15mm;
            }
        }
    </style>
</head>
<body>

<div class="no-print">
    <button class="btn" onclick="window.print()">🖨️ Cetak Laporan HPP</button>
</div>

<div class="page-container">
    <header class="report-header">
        <div class="store-info">
            <?php if ($logoUrl): ?>
                <img src="<?= $logoUrl ?>" alt="Logo" style="max-height: 50px; margin-bottom: 10px;">
            <?php endif; ?>
            <h1><?= htmlspecialchars($store['store_name']) ?></h1>
            <p><?= htmlspecialchars($store['store_address'] ?? 'Alamat tidak tersedia') ?></p>
            <p><?= htmlspecialchars($store['store_phone'] ?? '') ?></p>
        </div>
        <div class="report-meta">
            <h2>Laporan Analisis HPP</h2>
            <p>Tanggal Cetak: <?= date('d M Y, H:i') ?></p>
            <p>Dicetak oleh: <?= htmlspecialchars($user['full_name']) ?></p>
        </div>
    </header>

    <?php if (count($fullData) > 1): ?>
    <section class="summary-section">
        <div class="summary-title">Ringkasan Kalkulasi (<?= count($fullData) ?> Menu)</div>
        <table>
            <thead>
                <tr>
                    <th>Nama Menu</th>
                    <th class="text-right">Total HPP</th>
                    <?php if ($showSuggest): ?>
                        <th class="text-right">Saran Harga</th>
                    <?php endif; ?>
                    <th class="text-right">Harga Aktual</th>
                    <th class="text-right">Margin / Profit</th>
                    <th class="text-center">Status</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($fullData as $d): ?>
                <tr>
                    <td><strong><?= htmlspecialchars($d['base']['product_name']) ?></strong></td>
                    <td class="text-right"><?= number_format($d['total_hpp'], 0, ',', '.') ?></td>
                    <?php if ($showSuggest): ?>
                        <td class="text-right" style="color:var(--text-muted)"><?= number_format($d['suggested_rounded'], 0, ',', '.') ?></td>
                    <?php endif; ?>
                    <td class="text-right"><strong><?= number_format($d['actual_price'], 0, ',', '.') ?></strong></td>
                    <td class="text-right">
                        <?= number_format($d['actual_profit'], 0, ',', '.') ?><br>
                        <small style="color: <?= $d['actual_margin_pct'] >= $d['base']['margin_pct'] ? 'var(--success)' : ($d['actual_margin_pct'] > 0 ? 'var(--warning)' : 'var(--danger)') ?>">
                            (<?= $d['actual_margin_pct'] ?>%)
                        </small>
                    </td>
                    <td class="text-center">
                        <?php if ($d['actual_price'] <= 0): ?>
                            <span class="status-badge status-warning">Belum Diset</span>
                        <?php elseif ($d['actual_margin_pct'] >= $d['base']['margin_pct']): ?>
                            <span class="status-badge status-good">Aman</span>
                        <?php elseif ($d['actual_margin_pct'] > 0): ?>
                            <span class="status-badge status-warning">Rendah</span>
                        <?php else: ?>
                            <span class="status-badge status-danger">Rugi</span>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </section>
    <?php endif; ?>

    <section class="details-section">
        <div class="summary-title" style="margin-top: 30px;">Rincian Kalkulasi Per Menu</div>
        
        <?php foreach ($fullData as $d): ?>
        <div class="hpp-card">
            <div class="hpp-card-header">
                <div><?= htmlspecialchars($d['base']['product_name']) ?></div>
                <div>HPP: Rp <?= number_format($d['total_hpp'], 0, ',', '.') ?> / porsi</div>
            </div>
            
            <div class="hpp-card-body">
                <div class="detail-grid">
                    
                    <!-- Bahan Baku -->
                    <div class="detail-col">
                        <h4>Bahan Baku (Ingredients)</h4>
                        <table class="detail-table">
                            <thead>
                                <tr>
                                    <th>Item</th>
                                    <th>Porsi/Yield</th>
                                    <th class="text-right">Subtotal</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if(empty($d['ingredients'])): ?>
                                <tr><td colspan="3" class="text-center" style="color:#999">- Tidak ada bahan baku -</td></tr>
                                <?php else: ?>
                                    <?php foreach($d['ingredients'] as $i): ?>
                                    <tr>
                                        <td><?= htmlspecialchars($i['name']) ?></td>
                                        <td>1/<?= $i['portions_yield'] ?></td>
                                        <td class="text-right"><?= number_format($i['subtotal'], 0, ',', '.') ?></td>
                                    </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                            <tfoot>
                                <tr>
                                    <th colspan="2" class="text-right">Total Bahan</th>
                                    <th class="text-right"><?= number_format($d['ing_cost'], 0, ',', '.') ?></th>
                                </tr>
                            </tfoot>
                        </table>

                        <!-- Packaging -->
                        <h4 style="margin-top: 15px;">Kemasan (Packaging)</h4>
                        <table class="detail-table">
                            <tbody>
                                <?php if(empty($d['packagings'])): ?>
                                <tr><td colspan="2" class="text-center" style="color:#999">- Tidak ada kemasan -</td></tr>
                                <?php else: ?>
                                    <?php foreach($d['packagings'] as $i): ?>
                                    <tr>
                                        <td><?= htmlspecialchars($i['name']) ?></td>
                                        <td class="text-right"><?= number_format($i['subtotal'], 0, ',', '.') ?></td>
                                    </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>

                    <!-- Kolom Kanan (Extra Cost & Final Calc) -->
                    <div class="detail-col">
                        <h4>Biaya Operasional Langsung</h4>
                        <table class="detail-table">
                            <tbody>
                                <?php if(empty($d['extras'])): ?>
                                <tr><td colspan="2" class="text-center" style="color:#999">- Tidak ada biaya operasional -</td></tr>
                                <?php else: ?>
                                    <?php foreach($d['extras'] as $i): ?>
                                    <tr>
                                        <td><?= htmlspecialchars($i['name']) ?></td>
                                        <td class="text-right"><?= number_format($i['subtotal'], 0, ',', '.') ?></td>
                                    </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>

                        <div class="calc-box">
                            <div class="calc-row">
                                <span>Bahan + Kemasan + Operasional</span>
                                <span>Rp <?= number_format($d['ing_cost'] + $d['pkg_cost'] + $d['ext_cost'], 0, ',', '.') ?></span>
                            </div>
                            <div class="calc-row">
                                <span>Overhead (<?= $d['base']['overhead_pct'] ?>%)</span>
                                <span>Rp <?= number_format($d['overhead_cost'], 0, ',', '.') ?></span>
                            </div>
                            <div class="calc-row total">
                                <span>TOTAL HPP (Modal per porsi)</span>
                                <span>Rp <?= number_format($d['total_hpp'], 0, ',', '.') ?></span>
                            </div>
                            
                            <?php if ($showSuggest): ?>
                            <div class="calc-row margin" style="margin-top:10px;">
                                <span>Target Margin Profit (<?= $d['base']['margin_pct'] ?>%)</span>
                                <span>+ Rp <?= number_format($d['margin_cost'], 0, ',', '.') ?></span>
                            </div>
                            <div class="calc-row price">
                                <span>Saran Harga Jual (Dibulatkan)</span>
                                <span>Rp <?= number_format($d['suggested_rounded'], 0, ',', '.') ?></span>
                            </div>
                            <?php endif; ?>

                            <div class="calc-row price" style="margin-top:10px; border-top: 1px solid var(--border); padding-top: 5px;">
                                <span>Harga Jual Aktual</span>
                                <span>Rp <?= number_format($d['actual_price'], 0, ',', '.') ?></span>
                            </div>
                            <div class="calc-row">
                                <span>Profit / Margin Aktual</span>
                                <span style="color: <?= $d['actual_margin_pct'] >= $d['base']['margin_pct'] ? 'var(--success)' : ($d['actual_margin_pct'] > 0 ? 'var(--warning)' : 'var(--danger)') ?>; font-weight: 700;">
                                    Rp <?= number_format($d['actual_profit'], 0, ',', '.') ?> (<?= $d['actual_margin_pct'] ?>%)
                                </span>
                            </div>
                        </div>

                    </div>

                </div>
            </div>
        </div>
        <?php endforeach; ?>

    </section>
</div>

</body>
</html>
