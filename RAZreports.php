<?php
/**
 * ============================================================
 * RAZreports.php — Modul Laporan & Ekspor PDF SIMAJURAZ
 * ============================================================
 * Versi: 1.0.0 | Dibuat: 2026-05-21 | Diupdate: 2026-05-21
 * Deskripsi: Laporan transaksi, arus kas, laba rugi, inventori.
 *            Bisa di-export ke PDF via browser print.
 * ============================================================
 */
require_once __DIR__ . '/RAZconfig.php';
require_once __DIR__ . '/includes/RAZsession.php';
require_once __DIR__ . '/includes/RAZhelpers.php';
require_once __DIR__ . '/includes/RAZlang.php';
RAZrequireOwner();
$user = RAZgetCurrentUser();
$currentPage = 'reports';
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan — SIMAJURAZ</title>
    <link rel="stylesheet" href="https://unpkg.com/@phosphor-icons/web@2.1.1/src/bold/style.css">
    <link rel="stylesheet" href="assets/css/RAZMain.css">
    <link rel="stylesheet" href="assets/css/RAZComponents.css">
    <link rel="stylesheet" href="assets/css/RAZModal.css">
    <link rel="stylesheet" href="assets/css/RAZFinance.css">
    <link rel="stylesheet" href="assets/css/RAZReports.css">
</head>
<body class="<?= (isset($_COOKIE['raz_theme']) && $_COOKIE['raz_theme'] === 'dark') ? 'dark-mode' : '' ?>">
<div class="raz-app">
    <!-- SIDEBAR -->
    <aside class="raz-sidebar" id="sidebar">
        <div class="raz-sidebar-logo">
            <?php if ($user['store_logo']): ?>
                <img src="uploads/logos/<?= htmlspecialchars($user['store_logo']) ?>" alt="Logo">
            <?php else: ?>
                <div style="width:36px;height:36px;background:rgba(255,255,255,0.15);border-radius:8px;display:flex;align-items:center;justify-content:center;font-size:1.2rem;"><i class="ph-bold ph-storefront"></i></div>
            <?php endif; ?>
            <span><?= htmlspecialchars($user['store_name'] ?: 'SIMAJURAZ') ?></span>
        </div>
        <ul class="raz-sidebar-menu">
            <li class="raz-sidebar-section"><?= t('menu_main') ?></li>
            <li><a href="RAZdashboard.php"><span class="menu-icon"><i class="ph-bold ph-squares-four"></i></span><span class="menu-text"><?= t('menu_dashboard') ?></span></a></li>
            <li><a href="RAZpos.php"><span class="menu-icon"><i class="ph-bold ph-shopping-cart"></i></span><span class="menu-text"><?= t('menu_pos') ?></span></a></li>
            <li><a href="RAZsettings.php"><span class="menu-icon"><i class="ph-bold ph-gear"></i></span><span class="menu-text"><?= t('menu_settings') ?></span></a></li>
            <li class="raz-sidebar-section"><?= t('menu_manage') ?></li>
            <li><a href="RAZinventory.php"><span class="menu-icon"><i class="ph-bold ph-package"></i></span><span class="menu-text"><?= t('menu_inventory') ?></span></a></li>
            <li><a href="RAZfinance.php"><span class="menu-icon"><i class="ph-bold ph-wallet"></i></span><span class="menu-text"><?= t('menu_finance') ?></span></a></li>
            <li><a href="RAZreports.php" class="active"><span class="menu-icon"><i class="ph-bold ph-chart-bar"></i></span><span class="menu-text"><?= t('menu_reports') ?></span></a></li>
            <li><a href="RAZusers.php"><span class="menu-icon"><i class="ph-bold ph-users"></i></span><span class="menu-text"><?= t('menu_employees') ?></span></a></li>
        </ul>
        <div class="raz-sidebar-toggle"><button onclick="RAZ.toggleSidebar()"><i class="ph-bold ph-sidebar-simple"></i></button></div>
    </aside>

    <div class="raz-main">
        <header class="raz-topbar">
            <div class="raz-topbar-left">
                <button class="raz-btn raz-btn-ghost raz-btn-icon-only raz-btn-sm" onclick="RAZ.toggleMobileSidebar()" id="mobileMenuBtn" style="display:none;"><i class="ph-bold ph-list"></i></button>
                <h1 class="raz-topbar-title"><?= t('menu_reports') ?></h1>
            </div>
            <div class="raz-topbar-right" style="display:flex; align-items:center;">
                    <!-- Language & Theme Shortcuts -->
                    <a href="?lang=<?= (isset($current_lang) && $current_lang === 'id') ? 'en' : 'id' ?>" class="nav-action-icon raz-btn-icon" style="color:var(--raz-text); font-size:1rem; font-weight:700; text-decoration:none; display:none; align-items:center; justify-content:center; width:36px; height:36px; border-radius:50%; border:1px solid var(--raz-border); margin-right: 8px;">
                        <?= strtoupper((isset($current_lang) && $current_lang === 'id') ? 'en' : 'id') ?>
                    </a>
                    <a href="#" id="theme-toggle" class="nav-action-icon raz-btn-icon" style="color:var(--raz-text); font-size:1.2rem; text-decoration:none; display:none; align-items:center; justify-content:center; width:36px; height:36px; border-radius:50%; border:1px solid var(--raz-border); margin-right: 16px;">
                        <i class="ph-bold ph-sun"></i>
                    </a>
                <button class="rpt-export-btn" onclick="exportPDF()"><i class="ph-bold ph-file-pdf"></i> <?= t('rep_btn_export') ?></button>
                <div style="position:relative;">
                    <button class="raz-topbar-user" onclick="toggleUserDropdown()">
                        <div class="raz-topbar-avatar"><?= strtoupper(substr($user['full_name'],0,2)) ?></div>
                        <div class="raz-topbar-info"><span class="raz-topbar-name"><?= htmlspecialchars($user['full_name']) ?></span><span class="raz-topbar-role"><?= $user['role'] ?></span></div>
                    </button>
                    <div class="raz-dropdown" id="userDropdown"><a href="RAZlogout.php" class="danger"><i class="ph-bold ph-sign-out"></i> <?= t('topbar_logout') ?></a></div>
                </div>
            </div>
        </header>

        <main class="raz-content">
            <!-- Pilih Jenis Laporan -->
            <div class="rpt-types">
                <div class="rpt-type-card active" data-report="profit_loss">
                    <i class="ph-bold ph-chart-pie"></i>
                    <div class="rpt-type-name"><?= t('fin_tab_summary') ?></div>
                    <div class="rpt-type-desc">Ringkasan pendapatan & beban</div>
                </div>
                <div class="rpt-type-card" data-report="transactions">
                    <i class="ph-bold ph-receipt"></i>
                    <div class="rpt-type-name"><?= t('rep_table_trx') ?></div>
                    <div class="rpt-type-desc">Riwayat semua transaksi</div>
                </div>
                <div class="rpt-type-card" data-report="cashflow">
                    <i class="ph-bold ph-arrows-left-right"></i>
                    <div class="rpt-type-name"><?= t('fin_tab_cashflow') ?></div>
                    <div class="rpt-type-desc">Pemasukan & pengeluaran</div>
                </div>
                <div class="rpt-type-card" data-report="inventory">
                    <i class="ph-bold ph-package"></i>
                    <div class="rpt-type-name"><?= t('menu_inventory') ?></div>
                    <div class="rpt-type-desc">Stok & valuasi barang</div>
                </div>
            </div>

            <div class="rpt-filter">
                <label><?= t('rep_filter_start') ?>:</label>
                <input type="date" id="rptDateFrom">
                <label><?= t('rep_filter_end') ?>:</label>
                <input type="date" id="rptDateTo">
            </div>

            <!-- ===== SECTION: Laba Rugi ===== -->
            <div class="rpt-section active" id="rpt_profit_loss">
                <div class="raz-card">
                    <div class="raz-card-header"><h3 class="raz-card-title"><i class="ph-bold ph-chart-pie"></i> Laporan Laba Rugi</h3></div>
                    <table class="rpt-pl-table"><tbody id="plBody"><tr><td colspan="2"><div class="raz-skeleton raz-skeleton-table-row"></div></td></tr></tbody></table>
                </div>
                <div class="raz-card" style="margin-top:20px;">
                    <div class="raz-card-header"><h3 class="raz-card-title"><i class="ph-bold ph-trophy"></i> Item Terlaris</h3></div>
                    <table class="raz-table"><thead><tr><th>Nama</th><th>Qty</th><th>Total</th></tr></thead><tbody id="plTopItems"><tr><td colspan="3"><div class="raz-skeleton raz-skeleton-table-row"></div></td></tr></tbody></table>
                </div>
            </div>

            <!-- ===== SECTION: Transaksi ===== -->
            <div class="rpt-section" id="rpt_transactions">
                <div class="rpt-summary">
                    <div class="rpt-sum-card"><div class="rpt-sum-label">Total Penjualan</div><div class="rpt-sum-value" id="trSumSales" style="color:var(--raz-success)">Rp 0</div></div>
                    <div class="rpt-sum-card"><div class="rpt-sum-label">Jumlah</div><div class="rpt-sum-value" id="trSumCount">0</div></div>
                    <div class="rpt-sum-card"><div class="rpt-sum-label">Void</div><div class="rpt-sum-value" id="trSumVoid" style="color:var(--raz-danger)">0</div></div>
                </div>
                <div class="raz-card">
                    <div class="raz-table-wrapper">
                        <table class="raz-table"><thead><tr><th>Invoice</th><th>Tanggal</th><th>Kasir</th><th>Metode</th><th>Total</th><th>Status</th><th>Aksi</th></tr></thead>
                        <tbody id="trBody"><tr><td colspan="7"><div class="raz-skeleton raz-skeleton-table-row"></div></td></tr></tbody></table>
                    </div>
                    <div class="raz-pagination"><span class="raz-pagination-info" id="trPgInfo"></span></div>
                </div>
            </div>

            <!-- ===== SECTION: Arus Kas ===== -->
            <div class="rpt-section" id="rpt_cashflow">
                <div class="rpt-summary">
                    <div class="rpt-sum-card"><div class="rpt-sum-label">Pemasukan</div><div class="rpt-sum-value" id="cfRptIncome" style="color:var(--raz-success)">Rp 0</div></div>
                    <div class="rpt-sum-card"><div class="rpt-sum-label">Pengeluaran</div><div class="rpt-sum-value" id="cfRptExpense" style="color:var(--raz-danger)">Rp 0</div></div>
                    <div class="rpt-sum-card"><div class="rpt-sum-label">Saldo Bersih</div><div class="rpt-sum-value" id="cfRptNet">Rp 0</div></div>
                </div>
                <div class="raz-card">
                    <div class="raz-table-wrapper">
                        <table class="raz-table"><thead><tr><th>Tanggal</th><th>Tipe</th><th>Kategori</th><th>Nominal</th><th>Keterangan</th></tr></thead>
                        <tbody id="cfRptBody"><tr><td colspan="5"><div class="raz-skeleton raz-skeleton-table-row"></div></td></tr></tbody></table>
                    </div>
                </div>
            </div>

            <!-- ===== SECTION: Inventori ===== -->
            <div class="rpt-section" id="rpt_inventory">
                <div class="rpt-summary">
                    <div class="rpt-sum-card"><div class="rpt-sum-label">Total Barang</div><div class="rpt-sum-value" id="invRptTotal">0</div></div>
                    <div class="rpt-sum-card"><div class="rpt-sum-label">Nilai Stok (Jual)</div><div class="rpt-sum-value" id="invRptValue" style="color:var(--raz-primary)">Rp 0</div></div>
                    <div class="rpt-sum-card"><div class="rpt-sum-label">Nilai Stok (HPP)</div><div class="rpt-sum-value" id="invRptHpp">Rp 0</div></div>
                </div>
                <div class="raz-card">
                    <div class="raz-table-wrapper">
                        <table class="raz-table"><thead><tr><th>Nama</th><th>Kategori</th><th>HPP</th><th>Harga Jual</th><th>Stok</th><th>Nilai Total</th></tr></thead>
                        <tbody id="invRptBody"><tr><td colspan="6"><div class="raz-skeleton raz-skeleton-table-row"></div></td></tr></tbody></table>
                    </div>
                </div>
            </div>
        </main>
    </div>
</div>

<script>
window.RPT_LANG = {
    title_trx: '<?= t('rep_table_trx') ?>',
    title_cashflow: '<?= t('fin_tab_cashflow') ?>',
    title_profit_loss: '<?= t('rep_stat_profit') ?>',
    title_inventory: '<?= t('inv_title') ?>',
    col_desc: '<?= t('fin_table_desc') ?>',
    col_amount: '<?= t('fin_table_amount') ?>',
    col_date: '<?= t('fin_table_date') ?>',
    col_type: '<?= t('fin_table_type') ?>',
    col_cat: '<?= t('inv_table_category') ?>',
    col_name: '<?= t('usr_table_name') ?>',
    col_qty: '<?= t('fin_cogs_qty') ?>',
    col_total: '<?= t('fin_cogs_price') ?>',
    col_status: '<?= t('usr_table_status') ?>',
    col_stock: '<?= t('inv_table_stock') ?>',
    lbl_income: 'PENDAPATAN',
    lbl_expense: 'PENGELUARAN',
    lbl_net: 'LABA BERSIH',
    lbl_gross: 'LABA KOTOR',
    lbl_total_in: 'Total Pendapatan',
    lbl_total_out: 'Total Pengeluaran',
    lbl_cogs: 'HARGA POKOK PENJUALAN',
    lbl_sales: 'Penjualan',
    lbl_other: 'Pemasukan Lain-lain',
    lbl_cogs_desc: 'HPP Barang Terjual',
    lbl_sign: 'Mengetahui',
    lbl_owner: 'Manajemen / Owner',
    lbl_period: 'Periode',
    lbl_print_date: 'Dicetak pada'
};
</script>
<script src="assets/js/RAZMain.js?v=<?= time() ?>"></script>
<script src="assets/js/RAZReports.js?v=<?= time() ?>"></script>
<script>const checkMobile=()=>{const b=document.getElementById('mobileMenuBtn');if(b)b.style.display=window.innerWidth<=1024?'flex':'none';};checkMobile();window.addEventListener('resize',checkMobile);</script>
</body>
</html>


