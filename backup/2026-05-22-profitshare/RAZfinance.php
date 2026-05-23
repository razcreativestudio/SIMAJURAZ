<?php
/**
 * ============================================================
 * RAZfinance.php — Manajemen Keuangan SIMAJURAZ
 * ============================================================
 * Versi: 1.0.0 | Dibuat: 2026-05-21 | Diupdate: 2026-05-21
 * Deskripsi: Arus kas, shift, laba rugi, profit share.
 * ============================================================
 */
require_once __DIR__ . '/RAZconfig.php';
require_once __DIR__ . '/includes/RAZsession.php';
require_once __DIR__ . '/includes/RAZhelpers.php';
RAZrequireOwner();
$user = RAZgetCurrentUser();
$currentPage = 'finance';
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Keuangan — SIMAJURAZ</title>
    <link rel="stylesheet" href="https://unpkg.com/@phosphor-icons/web@2.1.1/src/bold/style.css">
    <link rel="stylesheet" href="assets/css/RAZMain.css">
    <link rel="stylesheet" href="assets/css/RAZComponents.css">
    <link rel="stylesheet" href="assets/css/RAZModal.css">
    <link rel="stylesheet" href="assets/css/RAZFinance.css">
</head>
<body>
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
            <li class="raz-sidebar-section">Menu Utama</li>
            <li><a href="RAZdashboard.php"><span class="menu-icon"><i class="ph-bold ph-squares-four"></i></span><span class="menu-text">Dashboard</span></a></li>
            <li><a href="RAZpos.php"><span class="menu-icon"><i class="ph-bold ph-shopping-cart"></i></span><span class="menu-text">Kasir (POS)</span></a></li>
            <li><a href="RAZsettings.php"><span class="menu-icon"><i class="ph-bold ph-gear"></i></span><span class="menu-text">Pengaturan Toko</span></a></li>
            <li class="raz-sidebar-section">Manajemen</li>
            <li><a href="RAZinventory.php"><span class="menu-icon"><i class="ph-bold ph-package"></i></span><span class="menu-text">Inventori</span></a></li>
            <li><a href="RAZfinance.php" class="active"><span class="menu-icon"><i class="ph-bold ph-wallet"></i></span><span class="menu-text">Keuangan</span></a></li>
            <li><a href="RAZreports.php"><span class="menu-icon"><i class="ph-bold ph-chart-bar"></i></span><span class="menu-text">Laporan</span></a></li>
            <li><a href="RAZusers.php"><span class="menu-icon"><i class="ph-bold ph-users"></i></span><span class="menu-text">Karyawan</span></a></li>
        </ul>
        <div class="raz-sidebar-toggle"><button onclick="RAZ.toggleSidebar()"><i class="ph-bold ph-sidebar-simple"></i></button></div>
    </aside>

    <div class="raz-main">
        <header class="raz-topbar">
            <div class="raz-topbar-left">
                <button class="raz-btn raz-btn-ghost raz-btn-icon-only raz-btn-sm" onclick="RAZ.toggleMobileSidebar()" id="mobileMenuBtn" style="display:none;"><i class="ph-bold ph-list"></i></button>
                <h1 class="raz-topbar-title">Keuangan</h1>
            </div>
            <div class="raz-topbar-right">
                <div style="position:relative;">
                    <button class="raz-topbar-user" onclick="toggleUserDropdown()">
                        <div class="raz-topbar-avatar"><?= strtoupper(substr($user['full_name'],0,2)) ?></div>
                        <div class="raz-topbar-info"><span class="raz-topbar-name"><?= htmlspecialchars($user['full_name']) ?></span><span class="raz-topbar-role"><?= $user['role'] ?></span></div>
                    </button>
                    <div class="raz-dropdown" id="userDropdown"><a href="RAZlogout.php" class="danger"><i class="ph-bold ph-sign-out"></i> Keluar</a></div>
                </div>
            </div>
        </header>

        <main class="raz-content">
            <!-- Shift Status -->
            <div id="shiftStatus"></div>

            <!-- Tabs -->
            <div class="fin-tabs">
                <button class="fin-tab active" data-tab="tabSummary"><i class="ph-bold ph-chart-pie"></i> Ringkasan</button>
                <button class="fin-tab" data-tab="tabCashflow"><i class="ph-bold ph-arrows-left-right"></i> Arus Kas</button>
                <button class="fin-tab" data-tab="tabProfit"><i class="ph-bold ph-hand-coins"></i> Bagi Hasil</button>
            </div>

            <!-- ===== TAB: Ringkasan Laba Rugi ===== -->
            <div class="fin-tab-content active" id="tabSummary">
                <div class="fin-filter-bar">
                    <div class="fin-period-chips">
                        <button class="fin-period-chip" data-period="today">Hari Ini</button>
                        <button class="fin-period-chip" data-period="week">7 Hari</button>
                        <button class="fin-period-chip active" data-period="month">Bulan Ini</button>
                        <button class="fin-period-chip" data-period="year">Tahun Ini</button>
                    </div>
                    <span id="finPeriodRange" style="font-size:0.78rem;color:var(--raz-text-muted);margin-left:auto;"></span>
                </div>
                <div class="fin-summary">
                    <div class="fin-summary-card"><div class="fin-label">Penjualan</div><div class="fin-value income" id="finSales">Rp 0</div></div>
                    <div class="fin-summary-card"><div class="fin-label">HPP (Harga Pokok)</div><div class="fin-value expense" id="finHpp">Rp 0</div></div>
                    <div class="fin-summary-card"><div class="fin-label">Laba Kotor</div><div class="fin-value profit" id="finGross">Rp 0</div></div>
                    <div class="fin-summary-card"><div class="fin-label">Pemasukan Lain</div><div class="fin-value income" id="finOtherIncome">Rp 0</div></div>
                    <div class="fin-summary-card"><div class="fin-label">Pengeluaran</div><div class="fin-value expense" id="finExpense">Rp 0</div></div>
                    <div class="fin-summary-card" style="border:2px solid var(--raz-primary)"><div class="fin-label"><strong>LABA BERSIH</strong></div><div class="fin-value profit" id="finNet">Rp 0</div></div>
                </div>
            </div>

            <!-- ===== TAB: Arus Kas ===== -->
            <div class="fin-tab-content" id="tabCashflow">
                <div class="raz-card">
                    <div class="raz-table-header">
                        <div class="raz-table-header-left">
                            <select class="inv-filter-select" id="cfTypeFilter"><option value="">Semua Tipe</option><option value="income">Pemasukan</option><option value="expense">Pengeluaran</option></select>
                            <span class="raz-badge success" style="font-size:0.8rem">Masuk: <span id="cfSumIncome">Rp 0</span></span>
                            <span class="raz-badge danger" style="font-size:0.8rem">Keluar: <span id="cfSumExpense">Rp 0</span></span>
                        </div>
                        <div class="raz-table-header-right">
                            <button class="raz-btn raz-btn-success raz-btn-sm" onclick="openAddCashflow('income')"><i class="ph-bold ph-arrow-down-left"></i> Pemasukan</button>
                            <button class="raz-btn raz-btn-danger raz-btn-sm" onclick="openAddCashflow('expense')"><i class="ph-bold ph-arrow-up-right"></i> Pengeluaran</button>
                        </div>
                    </div>
                    <div class="raz-table-wrapper">
                        <table class="raz-table">
                            <thead><tr><th>Tanggal</th><th>Tipe</th><th>Kategori</th><th>Nominal</th><th>Keterangan</th><th class="col-action">Aksi</th></tr></thead>
                            <tbody id="cfBody"><tr><td colspan="6"><div class="raz-skeleton raz-skeleton-table-row"></div></td></tr></tbody>
                        </table>
                    </div>
                    <div class="raz-pagination"><span class="raz-pagination-info" id="cfPgInfo"></span></div>
                </div>
            </div>

            <!-- ===== TAB: Bagi Hasil ===== -->
            <div class="fin-tab-content" id="tabProfit">
                <div class="raz-card">
                    <div class="fin-profit-share">
                        <h3><i class="ph-bold ph-hand-coins"></i> Kalkulator Bagi Hasil</h3>
                        <div id="shareNet" style="font-size:0.9rem;opacity:0.8;">Laba Bersih: Rp 0</div>
                        <div class="share-amount" id="shareAmount">Rp 0</div>
                        <input type="range" class="share-slider" id="shareSlider" min="0" max="100" value="50">
                        <div class="share-pct" id="sharePct">50%</div>
                    </div>
                </div>
            </div>
        </main>
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
            <input type="hidden" id="cfType">
            <div class="raz-form-group">
                <label class="raz-form-label">Kategori <span class="required">*</span></label>
                <input type="text" id="cfCategory" class="raz-form-input" placeholder="Contoh: Listrik, Gaji, dll">
            </div>
            <div class="raz-form-group">
                <label class="raz-form-label">Nominal <span class="required">*</span></label>
                <input type="number" id="cfAmount" class="raz-form-input" placeholder="0" min="0">
            </div>
            <div class="raz-form-group">
                <label class="raz-form-label">Keterangan</label>
                <input type="text" id="cfDesc" class="raz-form-input" placeholder="Deskripsi opsional">
            </div>
        </div>
        <div class="raz-modal-footer">
            <button class="raz-btn raz-btn-secondary" onclick="RAZ.closeModal('cfModal')"><i class="ph-bold ph-x"></i> Batal</button>
            <button class="raz-btn raz-btn-primary" id="btnSaveCf" onclick="saveCashflow()"><i class="ph-bold ph-floppy-disk"></i> Simpan</button>
        </div>
    </div>
</div>

<!-- MODAL: Buka Shift -->
<div class="raz-modal-overlay" id="openShiftModal">
    <div class="raz-modal modal-sm">
        <div class="raz-modal-header"><div class="raz-modal-title"><i class="ph-bold ph-play modal-icon"></i> Buka Shift</div><button class="raz-modal-close" onclick="RAZ.closeModal('openShiftModal')"><i class="ph-bold ph-x"></i></button></div>
        <div class="raz-modal-body">
            <div class="raz-form-group"><label class="raz-form-label">Modal Awal Kas</label><input type="number" id="shiftOpenCash" class="raz-form-input" placeholder="0" min="0"></div>
        </div>
        <div class="raz-modal-footer"><button class="raz-btn raz-btn-secondary" onclick="RAZ.closeModal('openShiftModal')">Batal</button><button class="raz-btn raz-btn-primary" onclick="submitOpenShift()"><i class="ph-bold ph-play"></i> Buka</button></div>
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
        <div class="raz-modal-footer"><button class="raz-btn raz-btn-secondary" onclick="RAZ.closeModal('closeShiftModal')">Batal</button><button class="raz-btn raz-btn-warning" onclick="submitCloseShift()"><i class="ph-bold ph-lock"></i> Tutup Shift</button></div>
    </div>
</div>

<script src="assets/js/RAZMain.js"></script>
<script src="assets/js/RAZFinance.js"></script>
<script>const checkMobile=()=>{const b=document.getElementById('mobileMenuBtn');if(b)b.style.display=window.innerWidth<=1024?'flex':'none';};checkMobile();window.addEventListener('resize',checkMobile);</script>
</body>
</html>


