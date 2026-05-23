<?php
/**
 * ============================================================
 * RAZfinance.php Ã¢â‚¬â€ Manajemen Keuangan SIMAJURAZ
 * ============================================================
 * Versi: 1.0.0 | Dibuat: 2026-05-21 | Diupdate: 2026-05-21
 * Deskripsi: Arus kas, shift, laba rugi, profit share.
 * ============================================================
 */
require_once __DIR__ . '/RAZconfig.php';
require_once __DIR__ . '/includes/RAZsession.php';
require_once __DIR__ . '/includes/RAZhelpers.php';
require_once __DIR__ . '/includes/RAZlang.php';
RAZrequireOwner();
$user = RAZgetCurrentUser();
$currentPage = 'finance';
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Keuangan Ã¢â‚¬â€ SIMAJURAZ</title>
    <link rel="stylesheet" href="https://unpkg.com/@phosphor-icons/web@2.1.1/src/bold/style.css">
    <link rel="stylesheet" href="assets/css/RAZMain.css">
    <link rel="stylesheet" href="assets/css/RAZComponents.css">
    <link rel="stylesheet" href="assets/css/RAZModal.css">
    <link rel="stylesheet" href="assets/css/RAZFinance.css">
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
            <li><a href="RAZfinance.php" class="active"><span class="menu-icon"><i class="ph-bold ph-wallet"></i></span><span class="menu-text"><?= t('menu_finance') ?></span></a></li>
            <li><a href="RAZreports.php"><span class="menu-icon"><i class="ph-bold ph-chart-bar"></i></span><span class="menu-text"><?= t('menu_reports') ?></span></a></li>
            <li><a href="RAZusers.php"><span class="menu-icon"><i class="ph-bold ph-users"></i></span><span class="menu-text"><?= t('menu_employees') ?></span></a></li>
        </ul>
        <div class="raz-sidebar-toggle"><button onclick="RAZ.toggleSidebar()"><i class="ph-bold ph-sidebar-simple"></i></button></div>
    </aside>

    <div class="raz-main">
        <header class="raz-topbar">
            <div class="raz-topbar-left">
                <button class="raz-btn raz-btn-ghost raz-btn-icon-only raz-btn-sm" onclick="RAZ.toggleMobileSidebar()" id="mobileMenuBtn" style="display:none;"><i class="ph-bold ph-list"></i></button>
                <h1 class="raz-topbar-title"><?= t('menu_finance') ?></h1>
            </div>
            <div class="raz-topbar-right" style="display:flex; align-items:center;">
                    <!-- Language & Theme Shortcuts -->
                    <a href="?lang=<?= (isset($current_lang) && $current_lang === 'id') ? 'en' : 'id' ?>" class="nav-action-icon raz-btn-icon" style="color:var(--raz-text); font-size:1rem; font-weight:700; text-decoration:none; display:none; align-items:center; justify-content:center; width:36px; height:36px; border-radius:50%; border:1px solid var(--raz-border); margin-right: 8px;">
                        <?= strtoupper((isset($current_lang) && $current_lang === 'id') ? 'en' : 'id') ?>
                    </a>
                    <a href="#" id="theme-toggle" class="nav-action-icon raz-btn-icon" style="color:var(--raz-text); font-size:1.2rem; text-decoration:none; display:none; align-items:center; justify-content:center; width:36px; height:36px; border-radius:50%; border:1px solid var(--raz-border); margin-right: 16px;">
                        <i class="ph-bold ph-sun"></i>
                    </a>
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
            <!-- Shift Status -->
            <div id="shiftStatus"></div>

            <!-- Tabs -->
            <div class="fin-tabs">
                <button class="fin-tab active" data-tab="tabSummary"><i class="ph-bold ph-chart-pie"></i> <?= t('fin_tab_summary') ?></button>
                <button class="fin-tab" data-tab="tabCashflow"><i class="ph-bold ph-arrows-left-right"></i> <?= t('fin_tab_cashflow') ?></button>
                <button class="fin-tab" data-tab="tabProfit"><i class="ph-bold ph-hand-coins"></i> <?= t('fin_tab_profit') ?></button>
                <a href="RAZhpp.php" class="fin-tab" style="text-decoration:none;color:inherit;"><i class="ph-bold ph-calculator"></i> <?= t('fin_tab_cogs') ?></a>
            </div>

            <!-- ===== TAB: Ringkasan Laba Rugi ===== -->
            <div class="fin-tab-content active" id="tabSummary">
                <div class="fin-filter-bar">
                    <div class="fin-period-chips">
                        <button class="fin-period-chip" data-period="today"><?= t('fin_period_today') ?></button>
                        <button class="fin-period-chip" data-period="week"><?= t('fin_period_week') ?></button>
                        <button class="fin-period-chip active" data-period="month"><?= t('fin_period_month') ?></button>
                        <button class="fin-period-chip" data-period="year"><?= t('fin_period_year') ?></button>
                    </div>
                    <span id="finPeriodRange" style="font-size:0.78rem;color:var(--raz-text-muted);margin-left:auto;"></span>
                </div>
                <div class="fin-summary">
                    <div class="fin-summary-card"><div class="fin-label"><?= t('fin_lbl_sales') ?></div><div class="fin-value income" id="finSales">Rp 0</div></div>
                    <div class="fin-summary-card"><div class="fin-label"><?= t('fin_lbl_hpp') ?></div><div class="fin-value expense" id="finHpp">Rp 0</div></div>
                    <div class="fin-summary-card"><div class="fin-label"><?= t('fin_lbl_gross') ?></div><div class="fin-value profit" id="finGross">Rp 0</div></div>
                    <div class="fin-summary-card"><div class="fin-label"><?= t('fin_lbl_other_income') ?></div><div class="fin-value income" id="finOtherIncome">Rp 0</div></div>
                    <div class="fin-summary-card"><div class="fin-label"><?= t('fin_lbl_expense') ?></div><div class="fin-value expense" id="finExpense">Rp 0</div></div>
                    <div class="fin-summary-card"><div class="fin-label"><?= t('fin_lbl_spoilage') ?></div><div class="fin-value danger" id="finSpoilage" style="color:var(--raz-danger)">Rp 0</div></div>
                    <div class="fin-summary-card" style="border:2px solid var(--raz-primary)"><div class="fin-label"><strong><?= t('fin_lbl_net') ?></strong></div><div class="fin-value profit" id="finNet">Rp 0</div></div>
                </div>
                
                <div class="fin-summary" style="margin-top:20px;">
                    <div class="fin-summary-card"><div class="fin-label"><?= t('fin_lbl_cap_in') ?></div><div class="fin-value income" id="finCapIn">Rp 0</div></div>
                    <div class="fin-summary-card"><div class="fin-label"><?= t('fin_lbl_cap_remain') ?></div><div class="fin-value profit" id="finCapRemain">Rp 0</div></div>
                </div>
            </div>

            <!-- ===== TAB: Arus Kas ===== -->
            <div class="fin-tab-content" id="tabCashflow">
                <div class="raz-card">
                    <div class="raz-table-header">
                        <div class="raz-table-header-left">
                            <select class="inv-filter-select" id="cfTypeFilter"><option value=""><?= t('fin_filter_all') ?></option><option value="income"><?= t('fin_type_income') ?></option><option value="expense"><?= t('fin_type_expense') ?></option></select>
                            <span class="raz-badge success" style="font-size:0.8rem"><?= t('fin_lbl_in') ?>: <span id="cfSumIncome">Rp 0</span></span>
                            <span class="raz-badge danger" style="font-size:0.8rem"><?= t('fin_lbl_out') ?>: <span id="cfSumExpense">Rp 0</span></span>
                        </div>
                        <div class="raz-table-header-right">
                            <button class="raz-btn raz-btn-success raz-btn-sm" onclick="openAddCashflow('income')"><i class="ph-bold ph-arrow-down-left"></i> <?= t('fin_type_income') ?></button>
                            <button class="raz-btn raz-btn-danger raz-btn-sm" onclick="openAddCashflow('expense')"><i class="ph-bold ph-arrow-up-right"></i> <?= t('fin_type_expense') ?></button>
                        </div>
                    </div>
                    <div class="raz-table-wrapper">
                        <table class="raz-table">
                            <thead><tr><th><?= t('fin_table_date') ?></th><th><?= t('fin_table_type') ?></th><th><?= t('inv_table_category') ?></th><th><?= t('fin_table_amount') ?></th><th><?= t('fin_table_desc') ?></th><th class="col-action"><?= t('inv_table_actions') ?></th></tr></thead>
                            <tbody id="cfBody"><tr><td colspan="6"><div class="raz-skeleton raz-skeleton-table-row"></div></td></tr></tbody>
                        </table>
                    </div>
                    <div class="raz-pagination"><span class="raz-pagination-info" id="cfPgInfo"></span></div>
                </div>
            </div>

            <!-- ===== TAB: Bagi Hasil (Profit Sharing) ===== -->
            <div class="fin-tab-content" id="tabProfit">

                <!-- Grid untuk Riwayat Modal dan Barang Rusak -->
                <div style="display:grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 20px; margin-bottom: 20px;">
                    <!-- Modal Awal -->
                    <div class="raz-card">
                        <div class="raz-table-header" style="flex-wrap:wrap; gap:10px;">
                            <div class="raz-table-header-left">
                                <h3 style="font-size:1rem;"><i class="ph-bold ph-bank"></i> Modal Awal</h3>
                            </div>
                            <div class="raz-table-header-right">
                                <button class="raz-btn raz-btn-success raz-btn-sm" onclick="openAddCapitalModal('in')"><i class="ph-bold ph-download-simple"></i> Tambah</button>
                                <button class="raz-btn raz-btn-danger raz-btn-sm" onclick="openAddCapitalModal('out')"><i class="ph-bold ph-upload-simple"></i> Tarik</button>
                            </div>
                        </div>
                        <div class="raz-table-wrapper" style="max-height: 250px; overflow-y: auto;">
                            <table class="raz-table">
                                <thead><tr><th>Tgl/Sumber</th><th><?= t('fin_table_amount') ?></th></tr></thead>
                                <tbody id="capBody"><tr><td colspan="2"><div class="raz-skeleton raz-skeleton-table-row"></div></td></tr></tbody>
                            </table>
                        </div>
                    </div>

                    <!-- Barang Rusak -->
                    <div class="raz-card">
                        <div class="raz-table-header" style="flex-wrap:wrap; gap:10px;">
                            <div class="raz-table-header-left">
                                <h3 style="font-size:1rem;"><i class="ph-bold ph-trash"></i> Barang Basi/Rusak</h3>
                            </div>
                            <div class="raz-table-header-right">
                                <button class="raz-btn raz-btn-danger raz-btn-sm" onclick="openAddSpoilageModal()"><i class="ph-bold ph-warning"></i> Input</button>
                            </div>
                        </div>
                        <div class="raz-table-wrapper" style="max-height: 250px; overflow-y: auto;">
                            <table class="raz-table">
                                <thead><tr><th>Barang</th><th>Kerugian</th></tr></thead>
                                <tbody id="spoilBody"><tr><td colspan="2"><div class="raz-skeleton raz-skeleton-table-row"></div></td></tr></tbody>
                            </table>
                        </div>
                    </div>

                    <!-- Pengeluaran Tambahan -->
                    <div class="raz-card">
                        <div class="raz-table-header" style="flex-wrap:wrap; gap:10px;">
                            <div class="raz-table-header-left">
                                <h3 style="font-size:1rem;"><i class="ph-bold ph-money"></i> Pengeluaran Tambahan</h3>
                            </div>
                            <div class="raz-table-header-right">
                                <button class="raz-btn raz-btn-danger raz-btn-sm" onclick="openAddCashflow('expense')"><i class="ph-bold ph-minus-circle"></i> Input</button>
                            </div>
                        </div>
                        <div class="raz-table-wrapper" style="max-height: 250px; overflow-y: auto;">
                            <table class="raz-table">
                                <thead><tr><th>Keterangan/Potong</th><th><?= t('fin_table_amount') ?></th></tr></thead>
                                <tbody id="addExpenseBody"><tr><td colspan="2"><div class="raz-skeleton raz-skeleton-table-row"></div></td></tr></tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- Header: Keuntungan Bersih -->
                <div class="ps-net-card">
                    <div class="ps-net-label"><i class="ph-bold ph-hand-coins"></i> Keuntungan Bersih Periode</div>
                    <div class="ps-net-amount" id="psNetAmount">Rp 0</div>
                    <div class="ps-net-breakdown" id="psBreakdown">
                        Pendapatan <span id="psRevenue">Rp 0</span> &minus; Biaya <span id="psCost">Rp 0</span>
                    </div>
                    <div class="ps-period-chips">
                        <button class="ps-chip" data-period="today"><?= t('fin_period_today') ?></button>
                        <button class="ps-chip active" data-period="month"><?= t('fin_period_month') ?></button>
                        <button class="ps-chip" data-period="week"><?= t('fin_period_week') ?></button>
                        <button class="ps-chip" data-period="year"><?= t('fin_period_year') ?></button>
                    </div>
                </div>

                <!-- Distribusi Bagi Hasil -->
                <div class="raz-card" style="margin-top:20px;">
                    <div class="raz-card-header">
                        <h3 class="raz-card-title"><i class="ph-bold ph-chart-pie"></i> Distribusi Bagi Hasil</h3>
                        <div class="ps-pct-indicator" id="psPctIndicator">
                            <span id="psTotalPct">0</span>% dari 100%
                        </div>
                    </div>
                    <div class="raz-card-body">
                        <!-- Daftar Penerima -->
                        <div id="psSharesList" class="ps-shares-list">
                            <!-- Diisi oleh JavaScript -->
                            <div class="ps-empty"><i class="ph-bold ph-users-three"></i><p><?= t('fin_empty_shares') ?></p></div>
                        </div>

                        <!-- Progress Total -->
                        <div class="ps-total-bar-wrap">
                            <div class="ps-total-bar">
                                <div class="ps-total-bar-fill" id="psTotalBarFill" style="width:0%"></div>
                            </div>
                            <div class="ps-total-info">
                                <span>Sisa: <strong id="psRemaining">100</strong>%</span>
                                <span id="psTotalStatus" class="ps-status-ok"><i class="ph-bold ph-check-circle"></i> Tersedia</span>
                            </div>
                        </div>

                        <!-- Action Buttons -->
                        <div class="ps-actions">
                            <button class="raz-btn raz-btn-primary raz-btn-sm" onclick="openAddShareModal()"><i class="ph-bold ph-plus"></i> <?= t('fin_btn_add_share') ?></button>
                            <button class="raz-btn raz-btn-success raz-btn-sm" onclick="saveAllShares()" id="btnSaveShares" style="display:none;"><i class="ph-bold ph-floppy-disk"></i> <?= t('fin_btn_save_share') ?></button>
                        </div>
                    </div>
                </div>

                <!-- Riwayat Laporan -->
                <div class="raz-card" style="margin-top:20px;">
                    <div class="raz-card-header">
                        <h3 class="raz-card-title"><i class="ph-bold ph-file-text"></i> Riwayat Laporan Bagi Hasil</h3>
                        <button class="raz-btn raz-btn-primary raz-btn-sm" onclick="generateReport()"><i class="ph-bold ph-file-pdf"></i> Generate Laporan</button>
                    </div>
                    <div class="raz-card-body">
                        <div id="psReportsList" class="ps-reports-list">
                            <div class="ps-empty-sm"><i class="ph-bold ph-file-dashed"></i> <?= t('fin_empty_report') ?></div>
                        </div>
                    </div>
                </div>

            </div>
        </main>
    </div>
</div>

<!-- MODAL: Tambah Penerima Bagi Hasil -->
<div class="raz-modal-overlay" id="addShareModal">
    <div class="raz-modal modal-sm">
        <div class="raz-modal-header">
            <div class="raz-modal-title"><i class="ph-bold ph-user-plus modal-icon"></i> <?= t('fin_modal_add_share') ?></div>
            <button class="raz-modal-close" onclick="RAZ.closeModal('addShareModal')"><i class="ph-bold ph-x"></i></button>
        </div>
        <div class="raz-modal-body">
            <div class="raz-form-group">
                <label class="raz-form-label">Nama Penerima <span class="required">*</span></label>
                <input type="text" id="psName" class="raz-form-input" placeholder="Contoh: Investor A, Kas Toko">
            </div>
            <div class="raz-form-group">
                <label class="raz-form-label">Peran / Label</label>
                <select id="psRoleLabel" class="raz-form-input">
                    <option value="kas_toko">Kas Toko</option>
                    <option value="owner">Owner / Pemilik</option>
                    <option value="investor">Investor</option>
                    <option value="bonus">Bonus Karyawan</option>
                    <option value="custom" selected>Lainnya</option>
                </select>
            </div>
            <div class="raz-form-group">
                <label class="raz-form-label">Persentase (%)</label>
                <input type="number" id="psPercentage" class="raz-form-input" placeholder="0" min="0" max="100" step="0.5">
                <div class="raz-form-hint">Sisa tersedia: <strong id="psAvailPct">100</strong>%</div>
            </div>
        </div>
        <div class="raz-modal-footer">
            <button class="raz-btn raz-btn-secondary" onclick="RAZ.closeModal('addShareModal')"><i class="ph-bold ph-x"></i> <?= t('inv_btn_cancel') ?></button>
            <button class="raz-btn raz-btn-primary" id="btnAddShare" onclick="submitAddShare()"><i class="ph-bold ph-plus"></i> <?= t('btn_add') ?? 'Tambah' ?></button>
        </div>
    </div>
</div>

<!-- MODAL: Generate Laporan Bagi Hasil -->
<div class="raz-modal-overlay" id="generateReportModal">
    <div class="raz-modal modal-sm">
        <div class="raz-modal-header">
            <div class="raz-modal-title"><i class="ph-bold ph-file-pdf modal-icon"></i> Generate Laporan</div>
            <button class="raz-modal-close" onclick="RAZ.closeModal('generateReportModal')"><i class="ph-bold ph-x"></i></button>
        </div>
        <div class="raz-modal-body">
            <p style="font-size:0.85rem;color:var(--raz-text-muted);margin-bottom:16px;"><?= t('fin_share_helper') ?></p>
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;">
                <div class="raz-form-group">
                    <label class="raz-form-label">Dari Tanggal</label>
                    <input type="date" id="rptDateFrom" class="raz-form-input">
                </div>
                <div class="raz-form-group">
                    <label class="raz-form-label">Sampai Tanggal</label>
                    <input type="date" id="rptDateTo" class="raz-form-input">
                </div>
            </div>
            <div class="raz-form-group">
                <label class="raz-form-label">Catatan (Opsional)</label>
                <input type="text" id="rptNotes" class="raz-form-input" placeholder="Contoh: Laporan Bulan Mei 2026">
            </div>
        </div>
        <div class="raz-modal-footer">
            <button class="raz-btn raz-btn-secondary" onclick="RAZ.closeModal('generateReportModal')"><?= t('inv_btn_cancel') ?? 'Batal' ?></button>
            <button class="raz-btn raz-btn-primary" id="btnGenReport" onclick="submitGenerateReport()"><i class="ph-bold ph-file-pdf"></i> <?= t('fin_btn_generate') ?></button>
        </div>
    </div>
</div>

<!-- MODAL: Catat Arus Kas -->
<div class="raz-modal-overlay" id="cfModal">
    <div class="raz-modal modal-sm">
        <div class="raz-modal-header">
            <div class="raz-modal-title"><i class="ph-bold ph-money modal-icon"></i> <span id="cfFormTitle">Catat Kas</span></div>
            <button class="raz-modal-close" onclick="RAZ.closeModal('cfModal')"><i class="ph-bold ph-x"></i></button>
        </div>
        <div class="raz-modal-body">
            <input type="hidden" id="cfId"><input type="hidden" id="cfType">
            <div class="raz-form-group">
                <label class="raz-form-label">Kategori <span class="required">*</span></label>
                <input type="text" id="cfCategory" class="raz-form-input" placeholder="Contoh: Listrik, Gaji, dll">
            </div>
            <div class="raz-form-group">
                <label class="raz-form-label"><?= t('fin_lbl_cap_amount') ?> <span class="required">*</span></label>
                <input type="number" id="cfAmount" class="raz-form-input" placeholder="0" min="0">
            </div>
            <div class="raz-form-group" id="cfDeductWrapper" style="display:none;">
                <label class="raz-form-label">Potong dari Jatah Keuntungan?</label>
                <select id="cfDeductShare" class="raz-form-input">
                    <option value=""><?= t('fin_opt_cut_cash') ?></option>
                </select>
                <small style="color:var(--raz-text-muted);font-size:0.75rem;"><?= t('fin_helper_cut_cash') ?></small>
            </div>
            <div class="raz-form-group">
                <label class="raz-form-label"><?= t('fin_lbl_notes') ?></label>
                <input type="text" id="cfDesc" class="raz-form-input" placeholder="Opsional">
            </div>
        </div>
        <div class="raz-modal-footer">
            <button class="raz-btn raz-btn-secondary" onclick="RAZ.closeModal('cfModal')"><i class="ph-bold ph-x"></i> <?= t('inv_btn_cancel') ?></button>
            <button class="raz-btn raz-btn-primary" id="btnSaveCf" onclick="saveCashflow()"><i class="ph-bold ph-floppy-disk"></i> <?= t('inv_btn_save') ?></button>
        </div>
    </div>
</div>

<!-- MODAL: Buka Shift -->
<div class="raz-modal-overlay" id="openShiftModal">
    <div class="raz-modal modal-sm">
        <div class="raz-modal-header"><div class="raz-modal-title"><i class="ph-bold ph-play modal-icon"></i> Buka Shift</div><button class="raz-modal-close" onclick="RAZ.closeModal('openShiftModal')"><i class="ph-bold ph-x"></i></button></div>
        <div class="raz-modal-body">
            <div class="raz-form-group"><label class="raz-form-label"><?= t('fin_lbl_open_cash') ?></label><input type="number" id="shiftOpenCash" class="raz-form-input" placeholder="0" min="0"></div>
        </div>
        <div class="raz-modal-footer"><button class="raz-btn raz-btn-secondary" onclick="RAZ.closeModal('openShiftModal')"><?= t('inv_btn_cancel') ?? 'Batal' ?></button><button class="raz-btn raz-btn-primary" onclick="submitOpenShift()"><i class="ph-bold ph-play"></i> <?= t('fin_btn_open') ?></button></div>
    </div>
</div>

<!-- MODAL: Tutup Shift -->
<div class="raz-modal-overlay" id="closeShiftModal">
    <div class="raz-modal modal-sm">
        <div class="raz-modal-header"><div class="raz-modal-title"><i class="ph-bold ph-lock modal-icon"></i> Tutup Shift</div><button class="raz-modal-close" onclick="RAZ.closeModal('closeShiftModal')"><i class="ph-bold ph-x"></i></button></div>
        <div class="raz-modal-body">
            <div class="raz-form-group"><label class="raz-form-label">Kas Akhir</label><input type="number" id="shiftCloseCash" class="raz-form-input" placeholder="Hitung uang di laci" min="0"></div>
            <div class="raz-form-group"><label class="raz-form-label">Catatan</label><input type="text" id="shiftNotes" class="raz-form-input" placeholder="Catatan opsional"></div>
        </div>
        <div class="raz-modal-footer"><button class="raz-btn raz-btn-secondary" onclick="RAZ.closeModal('closeShiftModal')"><?= t('inv_btn_cancel') ?? 'Batal' ?></button><button class="raz-btn raz-btn-warning" onclick="submitCloseShift()"><i class="ph-bold ph-lock"></i> <?= t('fin_btn_close_shift') ?></button></div>
    </div>
</div>

<script src="assets/js/RAZMain.js?v=<?= time() ?>"></script>
<script src="assets/js/RAZFinance.js"></script>
<script src="assets/js/RAZProfitShare.js"></script>
<script>
window.FIN_LANG = {
    empty_shares: '<?= t('fin_empty_shares') ?>',
    empty_report: '<?= t('fin_empty_report') ?>',
    empty_hpp: '<?= t('fin_empty_hpp') ?>',
    empty_cf_title: '<?= t('fin_empty_cf_title') ?>',
    empty_cf_desc: '<?= t('fin_empty_cf_desc') ?>',
    empty_cap: '<?= t('fin_empty_cap') ?>',
    empty_spoil: '<?= t('fin_empty_spoil') ?>',
    empty_exp: '<?= t('fin_empty_exp') ?>'
};const checkMobile=()=>{const b=document.getElementById('mobileMenuBtn');if(b)b.style.display=window.innerWidth<=1024?'flex':'none';};checkMobile();window.addEventListener('resize',checkMobile);</script>
<!-- MODAL: Tambah Modal -->
<div class="raz-modal-overlay" id="addCapitalModal">
    <div class="raz-modal modal-sm">
        <div class="raz-modal-header"><div class="raz-modal-title"><i class="ph-bold ph-bank modal-icon"></i> <span id="capFormTitle"><?= t('fin_modal_add_cap') ?></span></div><button class="raz-modal-close" onclick="RAZ.closeModal('addCapitalModal')"><i class="ph-bold ph-x"></i></button></div>
        <div class="raz-modal-body">
            <input type="hidden" id="capId"><input type="hidden" id="capType">
            <div class="raz-form-group"><label class="raz-form-label"><?= t('fin_lbl_cap_source') ?> <span class="required">*</span></label><input type="text" id="capSource" class="raz-form-input" placeholder="Misal: Dana Pribadi, Investor X"></div>
            <div class="raz-form-group"><label class="raz-form-label"><?= t('fin_lbl_cap_amount') ?> <span class="required">*</span></label><input type="number" id="capAmount" class="raz-form-input" placeholder="0" min="0"></div>
            <div class="raz-form-group"><label class="raz-form-label"><?= t('fin_lbl_cap_notes') ?></label><input type="text" id="capNotes" class="raz-form-input" placeholder="Opsional"></div>
        </div>
        <div class="raz-modal-footer"><button class="raz-btn raz-btn-secondary" onclick="RAZ.closeModal('addCapitalModal')"><?= t('inv_btn_cancel') ?? 'Batal' ?></button><button class="raz-btn raz-btn-primary" id="btnSaveCap" onclick="saveCapital()"><i class="ph-bold ph-floppy-disk"></i> <?= t('inv_btn_save') ?></button></div>
    </div>
</div>

<!-- MODAL: Barang Rusak / Basi -->
<div class="raz-modal-overlay" id="addSpoilageModal">
    <div class="raz-modal modal-sm">
        <div class="raz-modal-header"><div class="raz-modal-title"><i class="ph-bold ph-trash modal-icon"></i> <?= t('fin_modal_spoil') ?></div><button class="raz-modal-close" onclick="RAZ.closeModal('addSpoilageModal')"><i class="ph-bold ph-x"></i></button></div>
        <div class="raz-modal-body">
            <input type="hidden" id="spoilId"><div class="raz-form-group"><label class="raz-form-label"><?= t('fin_lbl_spoil_item') ?> <span class="required">*</span></label>
                <select id="spoilItem" class="raz-form-input"></select>
            </div>
            <div class="raz-form-group"><label class="raz-form-label">Jumlah (Qty) yang Rusak <span class="required">*</span></label><input type="number" id="spoilQty" class="raz-form-input" placeholder="0" min="1"></div>
            <div class="raz-form-group"><label class="raz-form-label"><?= t('fin_lbl_spoil_notes') ?></label><input type="text" id="spoilNotes" class="raz-form-input" placeholder="Misal: Basi, jatuh, dll"></div>
        </div>
        <div class="raz-modal-footer"><button class="raz-btn raz-btn-secondary" onclick="RAZ.closeModal('addSpoilageModal')"><?= t('inv_btn_cancel') ?? 'Batal' ?></button><button class="raz-btn raz-btn-danger" id="btnSaveSpoil" onclick="saveSpoilage()"><i class="ph-bold ph-warning"></i> <?= t('fin_btn_save_spoil') ?></button></div>
    </div>
</div>
</body>
</html>






