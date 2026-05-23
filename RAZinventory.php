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
require_once __DIR__ . '/includes/RAZlang.php';
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
            <li><a href="RAZinventory.php" class="active"><span class="menu-icon"><i class="ph-bold ph-package"></i></span><span class="menu-text"><?= t('menu_inventory') ?></span></a></li>
            <li><a href="RAZfinance.php"><span class="menu-icon"><i class="ph-bold ph-wallet"></i></span><span class="menu-text"><?= t('menu_finance') ?></span></a></li>
            <li><a href="RAZreports.php"><span class="menu-icon"><i class="ph-bold ph-chart-bar"></i></span><span class="menu-text"><?= t('menu_reports') ?></span></a></li>
            <li><a href="RAZusers.php"><span class="menu-icon"><i class="ph-bold ph-users"></i></span><span class="menu-text"><?= t('menu_employees') ?></span></a></li>
        </ul>
        <div class="raz-sidebar-toggle"><button onclick="RAZ.toggleSidebar()"><i class="ph-bold ph-sidebar-simple"></i></button></div>
    </aside>

    <div class="raz-main">
        <!-- TOPBAR -->
        <header class="raz-topbar">
            <div class="raz-topbar-left">
                <button class="raz-btn raz-btn-ghost raz-btn-icon-only raz-btn-sm" onclick="RAZ.toggleMobileSidebar()" id="mobileMenuBtn" style="display:none;"><i class="ph-bold ph-list"></i></button>
                <h1 class="raz-topbar-title"><?= t('menu_inventory') ?></h1>
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
                    <div class="raz-dropdown" id="userDropdown">
                        <a href="RAZlogout.php" class="danger"><i class="ph-bold ph-sign-out"></i> <?= t('topbar_logout') ?></a>
                    </div>
                </div>
            </div>
        </header>

        <!-- KONTEN -->
        <main class="raz-content">
            <!-- Tab Barang / Kategori -->
            <div class="inv-tabs">
                <button class="inv-tab active" data-tab="tabItems"><i class="ph-bold ph-package"></i> <?= t('inv_tab_items') ?></button>
                <button class="inv-tab" data-tab="tabCategories"><i class="ph-bold ph-tag"></i> <?= t('inv_tab_categories') ?> <span class="tab-count" id="catCount">0</span></button>
            </div>

            <!-- ==================== TAB: BARANG ==================== -->
            <div class="inv-tab-content active" id="tabItems">
                <div class="raz-card">
                    <div class="raz-table-header">
                        <div class="raz-table-header-left">
                            <div class="raz-table-search">
                                <span class="search-icon"><i class="ph-bold ph-magnifying-glass"></i></span>
                                <input type="text" id="itemSearch" placeholder="<?= t('inv_search') ?>">
                            </div>
                            <select class="inv-filter-select" id="catFilter"><option value=""><?= t('inv_tab_categories') ?></option></select>
                        </div>
                        <div class="raz-table-header-right">
                            <button class="raz-btn raz-btn-primary" onclick="openAddItem()">
                                <i class="ph-bold ph-plus"></i> <?= t('inv_btn_add') ?>
                            </button>
                        </div>
                    </div>

                    <div class="raz-table-wrapper">
                        <table class="raz-table">
                            <thead>
                                <tr>
                                    <th><?= t('inv_form_name') ?></th>
                                    <th><?= t('inv_table_category') ?></th>
                                    <th><?= t('inv_table_price') ?></th>
                                    <th><?= t('inv_table_stock') ?></th>
                                    <th class="col-action"><?= t('inv_table_actions') ?></th>
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
                        <h3 class="raz-card-title"><i class="ph-bold ph-tag"></i> <?= t('inv_cat_title') ?></h3>
                        <button class="raz-btn raz-btn-primary raz-btn-sm" onclick="openAddCategory()">
                            <i class="ph-bold ph-plus"></i> <?= t('inv_cat_add') ?>
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
            <div class="raz-modal-title"><i class="ph-bold ph-package modal-icon"></i> <span id="itemFormTitle"><?= t('inv_modal_add_title') ?></span></div>
            <button class="raz-modal-close" onclick="RAZ.closeModal('itemModal')"><i class="ph-bold ph-x"></i></button>
        </div>
        <div class="raz-modal-body">
            <form id="itemForm" enctype="multipart/form-data">
                <input type="hidden" name="id" id="itemId">
                <div class="raz-form-group">
                    <label class="raz-form-label"><?= t('inv_form_name') ?> <span class="required">*</span></label>
                    <input type="text" name="name" id="itemName" class="raz-form-input" placeholder="Contoh: Kopi Susu Gula Aren" required>
                </div>
                <div class="inv-price-grid">
                    <div class="raz-form-group">
                        <label class="raz-form-label"><?= t('inv_form_sku') ?></label>
                        <div style="display:flex; gap:8px;">
                            <input type="text" name="sku" id="itemSku" class="raz-form-input raz-text-mono" placeholder="Contoh: SKU-001" style="flex:1">
                            <button type="button" class="raz-btn raz-btn-primary" onclick="openScanner()" title="Scan Barcode via Kamera" style="padding:0 15px;"><i class="ph-bold ph-camera"></i></button>
                        </div>
                    </div>
                    <div class="raz-form-group">
                        <label class="raz-form-label"><?= t('inv_form_cat') ?></label>
                        <select name="category_id" id="itemCategory" class="raz-form-input"><option value="">Tanpa Kategori</option></select>
                    </div>
                </div>
                <div class="inv-price-grid">
                    <div class="raz-form-group">
                        <label class="raz-form-label"><?= t('inv_form_cogs') ?></label>
                        <input type="number" name="hpp" id="itemHpp" class="raz-form-input" placeholder="0" min="0">
                    </div>
                    <div class="raz-form-group">
                        <label class="raz-form-label"><?= t('inv_form_price') ?> <span class="required">*</span></label>
                        <input type="number" name="sell_price" id="itemSellPrice" class="raz-form-input" placeholder="0" min="0" required>
                    </div>
                </div>
                <div class="inv-margin-info" id="marginInfo" style="display:none;">Margin: Rp 0 (0%)</div>
                <div class="inv-price-grid" style="margin-top:16px;">
                    <div class="raz-form-group">
                        <label class="raz-form-label"><?= t('inv_form_stock') ?></label>
                        <input type="number" name="stock" id="itemStock" class="raz-form-input" value="0" min="0">
                    </div>
                    <div class="raz-form-group">
                        <label class="raz-form-label"><?= t('inv_form_min_stock') ?></label>
                        <input type="number" name="min_stock" id="itemMinStock" class="raz-form-input" value="5" min="0">
                    </div>
                </div>
                <div class="raz-form-group">
                    <label class="raz-form-label"><?= t('inv_form_photo') ?></label>
                    <input type="file" name="image" class="raz-form-input" accept="image/*">
                    <div class="raz-form-hint">Format: JPG, PNG, WebP. Maks 2MB.</div>
                </div>
            </form>
        </div>
        <div class="raz-modal-footer">
            <button class="raz-btn raz-btn-secondary" onclick="RAZ.closeModal('itemModal')"><i class="ph-bold ph-x"></i> <?= t('inv_btn_cancel') ?></button>
            <button class="raz-btn raz-btn-primary" id="btnSaveItem" onclick="saveItem()"><i class="ph-bold ph-floppy-disk"></i> <?= t('inv_btn_save') ?></button>
        </div>
    </div>
</div>

<!-- ==================== MODAL: Tambah/Edit Kategori ==================== -->
<div class="raz-modal-overlay" id="catModal">
    <div class="raz-modal modal-sm">
        <div class="raz-modal-header">
            <div class="raz-modal-title"><i class="ph-bold ph-tag modal-icon"></i> <span id="catFormTitle"><?= t('inv_cat_add') ?></span></div>
            <button class="raz-modal-close" onclick="RAZ.closeModal('catModal')"><i class="ph-bold ph-x"></i></button>
        </div>
        <div class="raz-modal-body">
            <input type="hidden" id="catId">
            <div class="raz-form-group">
                <label class="raz-form-label"><?= t('inv_cat_name') ?> <span class="required">*</span></label>
                <input type="text" id="catName" class="raz-form-input" placeholder="Contoh: Minuman">
            </div>
            <div class="raz-form-group">
                <label class="raz-form-label">Warna Label</label>
                <input type="color" id="catColor" class="raz-form-input" value="#4F46E5" style="height:44px;padding:6px;">
            </div>
        </div>
        <div class="raz-modal-footer">
            <button class="raz-btn raz-btn-secondary" onclick="RAZ.closeModal('catModal')"><i class="ph-bold ph-x"></i> <?= t('inv_btn_cancel') ?></button>
            <button class="raz-btn raz-btn-primary" id="btnSaveCat" onclick="saveCategory()"><i class="ph-bold ph-floppy-disk"></i> <?= t('inv_btn_save') ?></button>
        </div>
    </div>
</div>
</div>

<!-- ==================== MODAL: Scanner Kamera ==================== -->
<div class="raz-modal-overlay" id="scannerModal">
    <div class="raz-modal modal-sm" style="z-index: 1050;"> <!-- z-index lebih tinggi dari itemModal -->
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
window.INV_LANG = {
    empty_item_title: '<?= t('inv_empty_title') ?? 'Belum Ada Barang' ?>',
    empty_item_desc: '<?= t('inv_empty_desc') ?? 'Klik tombol Tambah Barang untuk memulai.' ?>',
    page_info: '<?= t('inv_page_info') ?? 'Halaman {page} dari {pages} ({total} barang)' ?>',
    items_count: '<?= t('inv_items_count') ?? 'barang' ?>',
    add_item: '<?= t('inv_modal_add_title') ?>',
    edit_item: '<?= t('inv_modal_edit_title') ?>',
    del_title: '<?= t('inv_del_title') ?? 'Hapus Barang?' ?>',
    del_msg: '<?= t('inv_del_msg') ?? '{name} akan dinonaktifkan.' ?>',
    del_cat_title: '<?= t('inv_del_cat_title') ?? 'Hapus Kategori?' ?>',
    del_cat_msg: '<?= t('inv_del_cat_msg') ?? '{name} akan dihapus.' ?>',
    btn_yes: '<?= t('btn_yes_delete') ?? 'Ya, Hapus' ?>'
};
</script>

<script>
window.INV_LANG = {
    empty_item_title: '<?= t('inv_empty_title') ?? 'Belum Ada Barang' ?>',
    empty_item_desc: '<?= t('inv_empty_desc') ?? 'Klik tombol Tambah Barang untuk memulai.' ?>',
    page_info: '<?= t('inv_page_info') ?? 'Halaman {page} dari {pages} ({total} barang)' ?>',
    items_count: '<?= t('inv_items_count') ?? 'barang' ?>',
    add_item: '<?= t('inv_modal_add_title') ?>',
    edit_item: '<?= t('inv_modal_edit_title') ?>',
    del_title: '<?= t('inv_del_title') ?? 'Hapus Barang?' ?>',
    del_msg: '<?= t('inv_del_msg') ?? '{name} akan dinonaktifkan.' ?>',
    del_cat_title: '<?= t('inv_del_cat_title') ?? 'Hapus Kategori?' ?>',
    del_cat_msg: '<?= t('inv_del_cat_msg') ?? '{name} akan dihapus.' ?>',
    btn_yes: '<?= t('btn_yes_delete') ?? 'Ya, Hapus' ?>'
};
</script>
<script src="assets/js/RAZMain.js?v=<?= time() ?>"></script>


<script src="assets/js/RAZInventory.js?v=<?= time() ?>"></script>
<script src="https://unpkg.com/html5-qrcode" type="text/javascript"></script>
<script>
const checkMobile=()=>{const b=document.getElementById('mobileMenuBtn');if(b)b.style.display=window.innerWidth<=1024?'flex':'none';};
checkMobile();window.addEventListener('resize',checkMobile);
</script>
</body>
</html>


