<?php
/**
 * ============================================================
 * RAZhpp.php — Kalkulator HPP (Harga Pokok Penjualan) SIMAJURAZ
 * ============================================================
 * Versi       : 1.0.0
 * Dibuat      : 2026-05-22
 * Diupdate    : 2026-05-22
 * Deskripsi   : Halaman kalkulator HPP per menu/produk.
 *               Mencakup perhitungan bahan baku, packaging,
 *               biaya tambahan, overhead, rekomendasi harga jual,
 *               dan push ke inventori.
 *               Akses: Owner only.
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
    <title>Kalkulator HPP — SIMAJURAZ</title>
    <link rel="stylesheet" href="https://unpkg.com/@phosphor-icons/web@2.1.1/src/bold/style.css">
    <link rel="stylesheet" href="assets/css/RAZMain.css">
    <link rel="stylesheet" href="assets/css/RAZComponents.css">
    <link rel="stylesheet" href="assets/css/RAZModal.css">
    <link rel="stylesheet" href="assets/css/RAZHpp.css">
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
                <a href="RAZfinance.php" class="raz-btn raz-btn-ghost raz-btn-sm" style="margin-right:8px"><i class="ph-bold ph-arrow-left"></i> Keuangan</a>
                <h1 class="raz-topbar-title">Kalkulator HPP</h1>
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
            <div class="hpp-layout">
                <!-- PANEL KIRI: Daftar Menu -->
                <div class="hpp-list-panel">
                    <div class="hpp-list-header">
                        <h3><i class="ph-bold ph-list-bullets"></i> Daftar Menu</h3>
                        <div style="display:flex; gap:8px;">
                            <button class="raz-btn raz-btn-secondary raz-btn-sm" style="flex:1;" onclick="openPrintModal()"><i class="ph-bold ph-printer"></i> Cetak</button>
                            <button class="raz-btn raz-btn-primary raz-btn-sm" style="flex:1;" onclick="RAZ.openModal('newHppModal')"><i class="ph-bold ph-plus"></i> Baru</button>
                        </div>
                    </div>
                    <div class="hpp-list-items" id="hppListItems">
                        <div class="hpp-list-empty"><i class="ph-bold ph-calculator"></i>Memuat...</div>
                    </div>
                </div>

                <!-- PANEL KANAN: Detail Kalkulasi -->
                <div class="hpp-detail-panel" id="hppDetailPanel">
                    <div class="hpp-detail-empty">
                        <i class="ph-bold ph-calculator"></i>
                        <p>Pilih menu dari daftar kiri<br>atau buat kalkulasi baru</p>
                    </div>
                </div>
            </div>
        </main>
    </div>
</div>

<!-- MODAL: Buat HPP Baru -->
<div class="raz-modal-overlay" id="newHppModal">
    <div class="raz-modal modal-sm">
        <div class="raz-modal-header">
            <div class="raz-modal-title"><i class="ph-bold ph-plus-circle modal-icon"></i> Kalkulasi HPP Baru</div>
            <button class="raz-modal-close" onclick="RAZ.closeModal('newHppModal')"><i class="ph-bold ph-x"></i></button>
        </div>
        <div class="raz-modal-body">
            <div class="raz-form-group">
                <label class="raz-form-label">Nama Produk / Menu <span class="required">*</span></label>
                <input type="text" id="hppNewName" class="raz-form-input" placeholder="Contoh: Nasi Goreng, Es Teh, dll">
            </div>
        </div>
        <div class="raz-modal-footer">
            <button class="raz-btn raz-btn-secondary" onclick="RAZ.closeModal('newHppModal')">Batal</button>
            <button class="raz-btn raz-btn-primary" onclick="createNewHpp()"><i class="ph-bold ph-plus"></i> Buat</button>
        </div>
    </div>
</div>

<!-- MODAL: Cetak HPP -->
<div class="raz-modal-overlay" id="printHppModal">
    <div class="raz-modal modal-md">
        <div class="raz-modal-header">
            <div class="raz-modal-title"><i class="ph-bold ph-printer modal-icon"></i> Cetak Laporan HPP</div>
            <button class="raz-modal-close" onclick="RAZ.closeModal('printHppModal')"><i class="ph-bold ph-x"></i></button>
        </div>
        <div class="raz-modal-body">
            <div class="raz-form-group">
                <label class="raz-form-label">Mode Cetak</label>
                <select id="printHppMode" class="raz-form-input" onchange="togglePrintHppMode()">
                    <option value="all">Cetak Semua Menu HPP</option>
                    <option value="selected">Cetak Beberapa Menu Pilihan</option>
                </select>
            </div>
            
            <div id="printHppSelection" style="display:none; margin-top: 15px; border: 1px solid var(--raz-border); border-radius: 8px; max-height: 200px; overflow-y: auto; padding: 10px;">
                <div style="font-size: 0.85rem; color: var(--raz-text-muted); margin-bottom: 10px;"><i class="ph-bold ph-info"></i> Pilih menu yang ingin dicetak:</div>
                <div id="printHppCheckboxes" style="display: flex; flex-direction: column; gap: 8px;">
                    <!-- Checkboxes generated by JS -->
                </div>
            </div>

            <div class="raz-form-group" style="margin-top: 15px;">
                <label style="display: flex; align-items: center; gap: 8px; cursor: pointer;">
                    <input type="checkbox" id="printHppShowSuggestion" checked style="width: 16px; height: 16px; accent-color: var(--raz-primary);">
                    <span>Tampilkan Saran Harga Jual di Laporan</span>
                </label>
            </div>
        </div>
        <div class="raz-modal-footer">
            <button class="raz-btn raz-btn-secondary" onclick="RAZ.closeModal('printHppModal')">Batal</button>
            <button class="raz-btn raz-btn-primary" onclick="executePrintHpp()"><i class="ph-bold ph-printer"></i> Lanjutkan Cetak</button>
        </div>
    </div>
</div>

<!-- Datalist untuk satuan -->
<datalist id="unitList">
    <option value="pcs"><option value="kg"><option value="gram"><option value="liter">
    <option value="ml"><option value="botol"><option value="bungkus"><option value="sachet">
    <option value="lembar"><option value="butir"><option value="ikat"><option value="lusin">
</datalist>

<script src="assets/js/RAZMain.js?v=<?= time() ?>"></script>
<script src="assets/js/RAZHpp.js"></script>
<script>const checkMobile=()=>{const b=document.getElementById('mobileMenuBtn');if(b)b.style.display=window.innerWidth<=1024?'flex':'none';};checkMobile();window.addEventListener('resize',checkMobile);</script>
</body>
</html>
