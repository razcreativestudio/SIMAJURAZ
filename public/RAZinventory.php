<?php
/**
 * ============================================================
 * RAZinventory.php — Manajemen Inventori SIMAJURAZ
 * ============================================================
 * Versi       : 1.0.0
 * Dibuat      : 2026-05-21
 * Diupdate    : 2026-05-21
 * Deskripsi   : Halaman untuk mengelola barang dan kategori.
 *               CRUD barang (nama, SKU, HPP, harga jual, stok),
 *               manajemen kategori, pencarian, filter, pagination.
 *               Hanya Owner yang bisa akses halaman ini.
 * ============================================================
 */
require_once __DIR__ . '/RAZconfig.php';
require_once __DIR__ . '/includes/RAZsession.php';
require_once __DIR__ . '/includes/RAZhelpers.php';
RAZrequireOwner();
$user = RAZgetCurrentUser();
$currentPage = 'inventory';
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inventori — SIMAJURAZ</title>
    <link rel="stylesheet" href="https://unpkg.com/@phosphor-icons/web@2.1.1/src/bold/style.css">
    <link rel="stylesheet" href="assets/css/RAZMain.css">
    <link rel="stylesheet" href="assets/css/RAZComponents.css">
    <link rel="stylesheet" href="assets/css/RAZModal.css">
    <link rel="stylesheet" href="assets/css/RAZInventory.css">
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
            <li><a href="RAZinventory.php" class="active"><span class="menu-icon"><i class="ph-bold ph-package"></i></span><span class="menu-text">Inventori</span></a></li>
            <li><a href="RAZfinance.php"><span class="menu-icon"><i class="ph-bold ph-wallet"></i></span><span class="menu-text">Keuangan</span></a></li>
            <li><a href="RAZreports.php"><span class="menu-icon"><i class="ph-bold ph-chart-bar"></i></span><span class="menu-text">Laporan</span></a></li>
            <li><a href="RAZusers.php"><span class="menu-icon"><i class="ph-bold ph-users"></i></span><span class="menu-text">Karyawan</span></a></li>
        </ul>
        <div class="raz-sidebar-toggle"><button onclick="RAZ.toggleSidebar()"><i class="ph-bold ph-sidebar-simple"></i></button></div>
    </aside>

    <div class="raz-main">
        <!-- TOPBAR -->
        <header class="raz-topbar">
            <div class="raz-topbar-left">
                <button class="raz-btn raz-btn-ghost raz-btn-icon-only raz-btn-sm" onclick="RAZ.toggleMobileSidebar()" id="mobileMenuBtn" style="display:none;"><i class="ph-bold ph-list"></i></button>
                <h1 class="raz-topbar-title">Inventori</h1>
            </div>
            <div class="raz-topbar-right">
                <div style="position:relative;">
                    <button class="raz-topbar-user" onclick="toggleUserDropdown()">
                        <div class="raz-topbar-avatar"><?= strtoupper(substr($user['full_name'],0,2)) ?></div>
                        <div class="raz-topbar-info"><span class="raz-topbar-name"><?= htmlspecialchars($user['full_name']) ?></span><span class="raz-topbar-role"><?= $user['role'] ?></span></div>
                    </button>
                    <div class="raz-dropdown" id="userDropdown">
                        <a href="RAZlogout.php" class="danger"><i class="ph-bold ph-sign-out"></i> Keluar</a>
                    </div>
                </div>
            </div>
        </header>

        <!-- KONTEN -->
        <main class="raz-content">
            <!-- Tab Barang / Kategori -->
            <div class="inv-tabs">
                <button class="inv-tab active" data-tab="tabItems"><i class="ph-bold ph-package"></i> Barang</button>
                <button class="inv-tab" data-tab="tabCategories"><i class="ph-bold ph-tag"></i> Kategori <span class="tab-count" id="catCount">0</span></button>
            </div>

            <!-- ==================== TAB: BARANG ==================== -->
            <div class="inv-tab-content active" id="tabItems">
                <div class="raz-card">
                    <div class="raz-table-header">
                        <div class="raz-table-header-left">
                            <div class="raz-table-search">
                                <span class="search-icon"><i class="ph-bold ph-magnifying-glass"></i></span>
                                <input type="text" id="itemSearch" placeholder="Cari nama atau SKU...">
                            </div>
                            <select class="inv-filter-select" id="catFilter"><option value="">Semua Kategori</option></select>
                        </div>
                        <div class="raz-table-header-right">
                            <button class="raz-btn raz-btn-primary" onclick="openAddItem()">
                                <i class="ph-bold ph-plus"></i> Tambah Barang
                            </button>
                        </div>
                    </div>

                    <div class="raz-table-wrapper">
                        <table class="raz-table">
                            <thead>
                                <tr>
                                    <th>Nama Barang</th>
                                    <th>Kategori</th>
                                    <th>Harga Jual</th>
                                    <th>Stok</th>
                                    <th class="col-action">Aksi</th>
                                </tr>
                            </thead>
                            <tbody id="itemsBody">
                                <tr><td colspan="5"><div class="raz-skeleton raz-skeleton-table-row"></div><div class="raz-skeleton raz-skeleton-table-row"></div><div class="raz-skeleton raz-skeleton-table-row"></div></td></tr>
                            </tbody>
                        </table>
                    </div>

                    <div class="raz-pagination" id="itemsPagination">
                        <span class="raz-pagination-info">Memuat...</span>
                        <div class="raz-pagination-buttons"></div>
                    </div>
                </div>
            </div>

            <!-- ==================== TAB: KATEGORI ==================== -->
            <div class="inv-tab-content" id="tabCategories">
                <div class="raz-card">
                    <div class="raz-card-header">
                        <h3 class="raz-card-title"><i class="ph-bold ph-tag"></i> Daftar Kategori</h3>
                        <button class="raz-btn raz-btn-primary raz-btn-sm" onclick="openAddCategory()">
                            <i class="ph-bold ph-plus"></i> Tambah Kategori
                        </button>
                    </div>
                    <div class="cat-grid" id="catGrid">
                        <div class="raz-skeleton raz-skeleton-card"></div>
                    </div>
                </div>
            </div>
        </main>
    </div>
</div>

<!-- ==================== MODAL: Tambah/Edit Barang ==================== -->
<div class="raz-modal-overlay" id="itemModal">
    <div class="raz-modal modal-lg">
        <div class="raz-modal-header">
            <div class="raz-modal-title"><i class="ph-bold ph-package modal-icon"></i> <span id="itemFormTitle">Tambah Barang</span></div>
            <button class="raz-modal-close" onclick="RAZ.closeModal('itemModal')"><i class="ph-bold ph-x"></i></button>
        </div>
        <div class="raz-modal-body">
            <form id="itemForm" enctype="multipart/form-data">
                <input type="hidden" name="id" id="itemId">
                <div class="raz-form-group">
                    <label class="raz-form-label">Nama Barang <span class="required">*</span></label>
                    <input type="text" name="name" id="itemName" class="raz-form-input" placeholder="Contoh: Kopi Susu Gula Aren" required>
                </div>
                <div class="inv-price-grid">
                    <div class="raz-form-group">
                        <label class="raz-form-label">SKU / Barcode</label>
                        <div style="display:flex; gap:8px;">
                            <input type="text" name="sku" id="itemSku" class="raz-form-input raz-text-mono" placeholder="Contoh: SKU-001" style="flex:1">
                            <button type="button" class="raz-btn raz-btn-primary" onclick="openScanner()" title="Scan Barcode via Kamera" style="padding:0 15px;"><i class="ph-bold ph-camera"></i></button>
                        </div>
                    </div>
                    <div class="raz-form-group">
                        <label class="raz-form-label">Kategori</label>
                        <select name="category_id" id="itemCategory" class="raz-form-input"><option value="">Tanpa Kategori</option></select>
                    </div>
                </div>
                <div class="inv-price-grid">
                    <div class="raz-form-group">
                        <label class="raz-form-label">Harga Pokok (HPP)</label>
                        <input type="number" name="hpp" id="itemHpp" class="raz-form-input" placeholder="0" min="0">
                    </div>
                    <div class="raz-form-group">
                        <label class="raz-form-label">Harga Jual <span class="required">*</span></label>
                        <input type="number" name="sell_price" id="itemSellPrice" class="raz-form-input" placeholder="0" min="0" required>
                    </div>
                </div>
                <div class="inv-margin-info" id="marginInfo" style="display:none;">Margin: Rp 0 (0%)</div>
                <div class="inv-price-grid" style="margin-top:16px;">
                    <div class="raz-form-group">
                        <label class="raz-form-label">Stok Saat Ini</label>
                        <input type="number" name="stock" id="itemStock" class="raz-form-input" value="0" min="0">
                    </div>
                    <div class="raz-form-group">
                        <label class="raz-form-label">Batas Stok Minimum</label>
                        <input type="number" name="min_stock" id="itemMinStock" class="raz-form-input" value="5" min="0">
                    </div>
                </div>
                <div class="raz-form-group">
                    <label class="raz-form-label">Gambar Barang</label>
                    <input type="file" name="image" class="raz-form-input" accept="image/*">
                    <div class="raz-form-hint">Format: JPG, PNG, WebP. Maks 2MB.</div>
                </div>
            </form>
        </div>
        <div class="raz-modal-footer">
            <button class="raz-btn raz-btn-secondary" onclick="RAZ.closeModal('itemModal')"><i class="ph-bold ph-x"></i> Batal</button>
            <button class="raz-btn raz-btn-primary" id="btnSaveItem" onclick="saveItem()"><i class="ph-bold ph-floppy-disk"></i> Simpan</button>
        </div>
    </div>
</div>

<!-- ==================== MODAL: Tambah/Edit Kategori ==================== -->
<div class="raz-modal-overlay" id="catModal">
    <div class="raz-modal modal-sm">
        <div class="raz-modal-header">
            <div class="raz-modal-title"><i class="ph-bold ph-tag modal-icon"></i> <span id="catFormTitle">Tambah Kategori</span></div>
            <button class="raz-modal-close" onclick="RAZ.closeModal('catModal')"><i class="ph-bold ph-x"></i></button>
        </div>
        <div class="raz-modal-body">
            <input type="hidden" id="catId">
            <div class="raz-form-group">
                <label class="raz-form-label">Nama Kategori <span class="required">*</span></label>
                <input type="text" id="catName" class="raz-form-input" placeholder="Contoh: Minuman">
            </div>
            <div class="raz-form-group">
                <label class="raz-form-label">Warna Label</label>
                <input type="color" id="catColor" class="raz-form-input" value="#4F46E5" style="height:44px;padding:6px;">
            </div>
        </div>
        <div class="raz-modal-footer">
            <button class="raz-btn raz-btn-secondary" onclick="RAZ.closeModal('catModal')"><i class="ph-bold ph-x"></i> Batal</button>
            <button class="raz-btn raz-btn-primary" id="btnSaveCat" onclick="saveCategory()"><i class="ph-bold ph-floppy-disk"></i> Simpan</button>
        </div>
    </div>
</div>
</div>

<!-- ==================== MODAL: Scanner Kamera ==================== -->
<div class="raz-modal-overlay" id="scannerModal">
    <div class="raz-modal modal-sm" style="z-index: 1050;"> <!-- z-index lebih tinggi dari itemModal -->
        <div class="raz-modal-header">
            <div class="raz-modal-title"><i class="ph-bold ph-camera modal-icon"></i> Scan Barcode</div>
            <button class="raz-modal-close" onclick="closeScanner()"><i class="ph-bold ph-x"></i></button>
        </div>
        <div class="raz-modal-body" style="padding:0; text-align:center;">
            <div id="reader" style="width:100%; min-height:250px;"></div>
        </div>
        <div class="raz-modal-footer" style="justify-content:center;">
            <button class="raz-btn raz-btn-secondary" onclick="closeScanner()"><i class="ph-bold ph-x"></i> Tutup Kamera</button>
        </div>
    </div>
</div>

<script src="assets/js/RAZMain.js?v=<?= time() ?>"></script>
<script src="assets/js/RAZInventory.js?v=<?= time() ?>"></script>
<script src="https://unpkg.com/html5-qrcode" type="text/javascript"></script>
<script>
const checkMobile=()=>{const b=document.getElementById('mobileMenuBtn');if(b)b.style.display=window.innerWidth<=1024?'flex':'none';};
checkMobile();window.addEventListener('resize',checkMobile);
</script>
</body>
</html>


