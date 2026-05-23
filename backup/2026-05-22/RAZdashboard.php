<?php
/**
 * ============================================================
 * RAZdashboard.php — Dashboard SIMAJURAZ
 * ============================================================
 * Versi       : 1.0.0
 * Dibuat      : 2026-05-21
 * Diupdate    : 2026-05-21
 * Deskripsi   : Halaman dashboard utama dengan layout sidebar,
 *               topbar, dan konten analitik. Menampilkan tampilan
 *               berbeda untuk Super Admin, Owner, dan Karyawan.
 * ============================================================
 */

require_once __DIR__ . '/RAZconfig.php';
require_once __DIR__ . '/includes/RAZsession.php';
require_once __DIR__ . '/includes/RAZhelpers.php';

// Wajib login untuk akses halaman ini
RAZrequireLogin();

$user = RAZgetCurrentUser();
$isSuperAdmin = ($user['role'] === 'superadmin');
$isOwner = ($user['role'] === 'owner');
$isEmployee = ($user['role'] === 'employee');

// Halaman aktif saat ini (untuk highlight sidebar)
$currentPage = 'dashboard';
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard — SIMAJURAZ</title>
    <meta name="description" content="Dashboard analitik SIMAJURAZ">

    <!-- Phosphor Icons -->
    <link rel="stylesheet" href="https://unpkg.com/@phosphor-icons/web@2.1.1/src/bold/style.css">
    <!-- Chart.js untuk grafik -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.4/dist/chart.umd.min.js"></script>

    <!-- CSS -->
    <link rel="stylesheet" href="assets/css/RAZMain.css">
    <link rel="stylesheet" href="assets/css/RAZComponents.css">
    <link rel="stylesheet" href="assets/css/RAZModal.css">
    <link rel="stylesheet" href="assets/css/RAZDashboard.css">
</head>
<body data-role="<?= $user['role'] ?>">
    <div class="raz-app">

        <!-- ============================
             SIDEBAR NAVIGASI
             ============================ -->
        <aside class="raz-sidebar" id="sidebar">
            <!-- Logo -->
            <div class="raz-sidebar-logo">
                <?php if (!$isSuperAdmin && $user['store_logo']): ?>
                    <img src="uploads/logos/<?= htmlspecialchars($user['store_logo']) ?>" alt="Logo Toko">
                <?php else: ?>
                    <div style="width:36px;height:36px;background:rgba(255,255,255,0.15);border-radius:8px;display:flex;align-items:center;justify-content:center;font-size:1.2rem;">
                        <i class="ph-bold ph-storefront"></i>
                    </div>
                <?php endif; ?>
                <span><?= $isSuperAdmin ? 'SIMAJURAZ' : htmlspecialchars($user['store_name'] ?: 'SIMAJURAZ') ?></span>
            </div>

            <!-- Menu -->
            <ul class="raz-sidebar-menu">
                <?php if ($isSuperAdmin): ?>
                    <!-- Menu Super Admin -->
                    <li class="raz-sidebar-section">Administrasi</li>
                    <li><a href="RAZdashboard.php" class="<?= $currentPage === 'dashboard' ? 'active' : '' ?>">
                        <span class="menu-icon"><i class="ph-bold ph-squares-four"></i></span>
                        <span class="menu-text">Dashboard</span>
                    </a></li>
                <?php else: ?>
                    <!-- Menu Owner / Karyawan -->
                    <li class="raz-sidebar-section">Menu Utama</li>
                    <li><a href="RAZdashboard.php" class="<?= $currentPage === 'dashboard' ? 'active' : '' ?>">
                        <span class="menu-icon"><i class="ph-bold ph-squares-four"></i></span>
                        <span class="menu-text">Dashboard</span>
                    </a></li>
                    <li><a href="RAZpos.php" class="<?= $currentPage === 'pos' ? 'active' : '' ?>">
                        <span class="menu-icon"><i class="ph-bold ph-shopping-cart"></i></span>
                        <span class="menu-text">Kasir (POS)</span>
                    </a></li>

                    <?php if ($isOwner): ?>
                        <li class="raz-sidebar-section">Manajemen</li>
                        <li><a href="RAZinventory.php" class="<?= $currentPage === 'inventory' ? 'active' : '' ?>">
                            <span class="menu-icon"><i class="ph-bold ph-package"></i></span>
                            <span class="menu-text">Inventori</span>
                        </a></li>
                        <li><a href="RAZfinance.php" class="<?= $currentPage === 'finance' ? 'active' : '' ?>">
                            <span class="menu-icon"><i class="ph-bold ph-wallet"></i></span>
                            <span class="menu-text">Keuangan</span>
                        </a></li>
                        <li><a href="RAZreports.php" class="<?= $currentPage === 'reports' ? 'active' : '' ?>">
                            <span class="menu-icon"><i class="ph-bold ph-chart-bar"></i></span>
                            <span class="menu-text">Laporan</span>
                        </a></li>
                        <li><a href="RAZusers.php" class="<?= $currentPage === 'users' ? 'active' : '' ?>">
                            <span class="menu-icon"><i class="ph-bold ph-users"></i></span>
                            <span class="menu-text">Karyawan</span>
                        </a></li>
                    <?php endif; ?>
                <?php endif; ?>
            </ul>

            <!-- Toggle Sidebar -->
            <div class="raz-sidebar-toggle">
                <button onclick="RAZ.toggleSidebar()" data-tooltip="Ciutkan Menu">
                    <i class="ph-bold ph-sidebar-simple"></i>
                </button>
            </div>
        </aside>

        <!-- ============================
             AREA KONTEN UTAMA
             ============================ -->
        <div class="raz-main">
            <!-- Topbar -->
            <header class="raz-topbar">
                <div class="raz-topbar-left">
                    <button class="raz-btn raz-btn-ghost raz-btn-icon-only raz-btn-sm" onclick="RAZ.toggleMobileSidebar()" style="display:none;" id="mobileMenuBtn">
                        <i class="ph-bold ph-list"></i>
                    </button>
                    <h1 class="raz-topbar-title">Dashboard</h1>
                </div>
                <div class="raz-topbar-right">
                    <!-- User Dropdown -->
                    <div style="position:relative;">
                        <button class="raz-topbar-user" onclick="toggleUserDropdown()">
                            <div class="raz-topbar-avatar"><?= strtoupper(substr($user['full_name'], 0, 2)) ?></div>
                            <div class="raz-topbar-info">
                                <span class="raz-topbar-name"><?= htmlspecialchars($user['full_name']) ?></span>
                                <span class="raz-topbar-role"><?= $user['role'] ?></span>
                            </div>
                            <i class="ph-bold ph-caret-down" style="color:var(--raz-text-muted);font-size:0.8rem;"></i>
                        </button>
                        <div class="raz-dropdown" id="userDropdown">
                            <?php if ($isOwner): ?>
                            <button onclick="RAZ.openModal('storeSettingsModal')">
                                <i class="ph-bold ph-gear"></i> Pengaturan Toko
                            </button>
                            <?php endif; ?>
                            <a href="RAZlogout.php" class="danger">
                                <i class="ph-bold ph-sign-out"></i> Keluar
                            </a>
                        </div>
                    </div>
                </div>
            </header>

            <!-- Konten Dashboard -->
            <main class="raz-content">
                <?php if ($isSuperAdmin): ?>
                    <!-- ==================== DASHBOARD SUPER ADMIN ==================== -->
                    <div class="raz-welcome">
                        <div class="raz-welcome-content">
                            <h2>👋 Halo, <?= htmlspecialchars($user['full_name']) ?></h2>
                            <p>Panel administrasi SIMAJURAZ. Kelola infrastruktur dan pantau tenant.</p>
                            <div class="welcome-date"><i class="ph-bold ph-calendar"></i> <?= RAZformatTanggal(date('Y-m-d')) ?></div>
                        </div>
                    </div>

                    <!-- Stat Cards Admin -->
                    <div class="raz-stats">
                        <div class="raz-stat-card">
                            <div class="raz-stat-icon primary"><i class="ph-bold ph-storefront"></i></div>
                            <div><div class="raz-stat-value" id="statTotalStores">-</div><div class="raz-stat-label">Total Toko</div></div>
                        </div>
                        <div class="raz-stat-card">
                            <div class="raz-stat-icon success"><i class="ph-bold ph-user-circle"></i></div>
                            <div><div class="raz-stat-value" id="statTotalOwners">-</div><div class="raz-stat-label">Owner Terdaftar</div></div>
                        </div>
                        <div class="raz-stat-card">
                            <div class="raz-stat-icon info"><i class="ph-bold ph-users"></i></div>
                            <div><div class="raz-stat-value" id="statTotalEmployees">-</div><div class="raz-stat-label">Karyawan</div></div>
                        </div>
                        <div class="raz-stat-card">
                            <div class="raz-stat-icon warning"><i class="ph-bold ph-identification-badge"></i></div>
                            <div><div class="raz-stat-value" id="statTotalUsers">-</div><div class="raz-stat-label">Total Pengguna</div></div>
                        </div>
                    </div>

                    <!-- Daftar Tenant -->
                    <div class="raz-card">
                        <div class="raz-card-header">
                            <h3 class="raz-card-title"><i class="ph-bold ph-buildings"></i> Toko Terdaftar</h3>
                        </div>
                        <div id="tenantList">
                            <div class="dash-empty"><div class="empty-icon"><i class="ph-bold ph-storefront"></i></div><p>Belum ada toko terdaftar</p></div>
                        </div>
                    </div>

                <?php else: ?>
                    <!-- ==================== DASHBOARD OWNER / KARYAWAN ==================== -->
                    <div class="raz-welcome">
                        <div class="raz-welcome-content">
                            <h2>👋 Selamat Datang, <?= htmlspecialchars($user['full_name']) ?>!</h2>
                            <p><?= htmlspecialchars($user['store_name']) ?> — Ringkasan bisnis hari ini.</p>
                            <div class="welcome-date"><i class="ph-bold ph-calendar"></i> <?= RAZformatTanggal(date('Y-m-d')) ?></div>
                        </div>
                    </div>

                    <!-- Stat Cards -->
                    <div class="raz-stats">
                        <div class="raz-stat-card">
                            <div class="raz-stat-icon primary"><i class="ph-bold ph-currency-dollar"></i></div>
                            <div>
                                <div class="raz-stat-value" id="statSalesToday">
                                    <div class="raz-skeleton raz-skeleton-text" style="width:120px;height:24px;"></div>
                                </div>
                                <div class="raz-stat-label">Penjualan Hari Ini · <span id="statSalesCount">0</span></div>
                            </div>
                        </div>
                        <div class="raz-stat-card">
                            <div class="raz-stat-icon success"><i class="ph-bold ph-trend-up"></i></div>
                            <div>
                                <div class="raz-stat-value" id="statProfit">
                                    <div class="raz-skeleton raz-skeleton-text" style="width:100px;height:24px;"></div>
                                </div>
                                <div class="raz-stat-label">Laba Hari Ini</div>
                            </div>
                        </div>
                        <div class="raz-stat-card">
                            <div class="raz-stat-icon warning"><i class="ph-bold ph-package"></i></div>
                            <div>
                                <div class="raz-stat-value" id="statProducts">
                                    <div class="raz-skeleton raz-skeleton-text" style="width:60px;height:24px;"></div>
                                </div>
                                <div class="raz-stat-label">Total Produk · <span class="raz-badge danger" id="lowStockBadge" style="display:none;">0</span> stok menipis</div>
                            </div>
                        </div>
                        <div class="raz-stat-card">
                            <div class="raz-stat-icon danger"><i class="ph-bold ph-arrow-down-right"></i></div>
                            <div>
                                <div class="raz-stat-value" id="statExpense">
                                    <div class="raz-skeleton raz-skeleton-text" style="width:100px;height:24px;"></div>
                                </div>
                                <div class="raz-stat-label">Pengeluaran Hari Ini</div>
                            </div>
                        </div>
                    </div>

                    <?php if ($isOwner): ?>
                    <!-- Quick Actions -->
                    <div class="dash-quick-actions">
                        <a href="RAZpos.php" class="dash-quick-btn">
                            <div class="qb-icon primary"><i class="ph-bold ph-shopping-cart"></i></div>
                            <span class="qb-label">Buka Kasir</span>
                        </a>
                        <a href="RAZinventory.php" class="dash-quick-btn">
                            <div class="qb-icon success"><i class="ph-bold ph-plus-circle"></i></div>
                            <span class="qb-label">Tambah Barang</span>
                        </a>
                        <a href="RAZfinance.php" class="dash-quick-btn">
                            <div class="qb-icon warning"><i class="ph-bold ph-money"></i></div>
                            <span class="qb-label">Arus Kas</span>
                        </a>
                        <a href="RAZreports.php" class="dash-quick-btn">
                            <div class="qb-icon info"><i class="ph-bold ph-file-pdf"></i></div>
                            <span class="qb-label">Cetak Laporan</span>
                        </a>
                    </div>
                    <?php endif; ?>

                    <!-- Chart + Transaksi Terbaru -->
                    <div class="dash-grid dash-grid-2">
                        <!-- Chart Penjualan -->
                        <div class="raz-card">
                            <div class="raz-card-header">
                                <h3 class="raz-card-title"><i class="ph-bold ph-chart-line-up"></i> Penjualan 7 Hari</h3>
                                <span class="raz-badge primary" id="statSalesMonth">Rp 0</span>
                            </div>
                            <div class="raz-chart-container" id="chartSkeleton">
                                <canvas id="salesChart"></canvas>
                            </div>
                        </div>

                        <!-- Item Terlaris -->
                        <div class="raz-card">
                            <div class="raz-card-header">
                                <h3 class="raz-card-title"><i class="ph-bold ph-trophy"></i> Item Terlaris</h3>
                            </div>
                            <table class="dash-mini-table">
                                <thead><tr><th>Nama Item</th><th>Qty</th><th>Total</th></tr></thead>
                                <tbody id="topItemsBody">
                                    <tr><td colspan="3"><div class="raz-skeleton raz-skeleton-table-row"></div></td></tr>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- Transaksi Terbaru -->
                    <div class="raz-card" style="margin-top:20px;">
                        <div class="raz-card-header">
                            <h3 class="raz-card-title"><i class="ph-bold ph-receipt"></i> Transaksi Terbaru</h3>
                            <a href="RAZreports.php" class="raz-btn raz-btn-secondary raz-btn-sm">
                                <i class="ph-bold ph-arrow-right"></i> Lihat Semua
                            </a>
                        </div>
                        <div class="raz-table-wrapper">
                            <table class="dash-mini-table">
                                <thead><tr><th>Invoice</th><th>Kasir</th><th>Total</th><th>Status</th></tr></thead>
                                <tbody id="recentTransBody">
                                    <tr><td colspan="4"><div class="raz-skeleton raz-skeleton-table-row"></div></td></tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                <?php endif; ?>
            </main>
        </div>
    </div>

    <?php if ($isOwner): ?>
    <!-- ==================== MODAL: Pengaturan Toko ==================== -->
    <div class="raz-modal-overlay" id="storeSettingsModal">
        <div class="raz-modal modal-lg">
            <div class="raz-modal-header">
                <div class="raz-modal-title"><i class="ph-bold ph-gear modal-icon"></i> Pengaturan Toko</div>
                <button class="raz-modal-close" onclick="RAZ.closeModal('storeSettingsModal')"><i class="ph-bold ph-x"></i></button>
            </div>
            <div class="raz-modal-body">
                <form id="storeSettingsForm" enctype="multipart/form-data">
                    <div class="raz-form-group">
                        <label class="raz-form-label">Nama Toko <span class="required">*</span></label>
                        <input type="text" name="store_name" class="raz-form-input" id="settStoreName" required>
                    </div>
                    <div class="raz-form-group">
                        <label class="raz-form-label">Alamat</label>
                        <textarea name="store_address" class="raz-form-textarea" id="settStoreAddr" rows="2"></textarea>
                    </div>
                    <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;">
                        <div class="raz-form-group">
                            <label class="raz-form-label">Telepon</label>
                            <input type="text" name="store_phone" class="raz-form-input" id="settStorePhone">
                        </div>
                        <div class="raz-form-group">
                            <label class="raz-form-label">Pajak (%)</label>
                            <input type="number" name="tax_percentage" class="raz-form-input" id="settStoreTax" step="0.1" min="0" max="100">
                        </div>
                    </div>
                    <div class="raz-form-group">
                        <label class="raz-form-label">Footer Struk</label>
                        <input type="text" name="receipt_footer" class="raz-form-input" id="settStoreFooter" placeholder="Terima kasih telah berbelanja!">
                    </div>
                    <div class="raz-form-group">
                        <label class="raz-form-label">Logo Toko</label>
                        <input type="file" name="store_logo" class="raz-form-input" accept="image/*">
                        <div class="raz-form-hint">Format: JPG, PNG, WebP. Maks 2MB.</div>
                    </div>
                </form>
            </div>
            <div class="raz-modal-footer">
                <button class="raz-btn raz-btn-secondary" onclick="RAZ.closeModal('storeSettingsModal')">
                    <i class="ph-bold ph-x"></i> Batal
                </button>
                <button class="raz-btn raz-btn-primary" id="btnSaveStore">
                    <i class="ph-bold ph-floppy-disk"></i> Simpan
                </button>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <!-- JavaScript -->
    <script src="assets/js/RAZMain.js"></script>
    <script src="assets/js/RAZDashboard.js"></script>
    <?php if ($isOwner): ?>
    <script>
    // Handler simpan pengaturan toko
    document.getElementById('btnSaveStore')?.addEventListener('click', async () => {
        const form = document.getElementById('storeSettingsForm');
        const btn = document.getElementById('btnSaveStore');
        const fd = new FormData(form);
        RAZ.btnLoading(btn, 'Menyimpan...');
        const res = await RAZ.upload('api/RAZapiStores.php?action=update_store', fd);
        RAZ.btnReset(btn);
        if (res.success) {
            RAZ.success('Berhasil', res.message);
            RAZ.closeModal('storeSettingsModal');
            setTimeout(() => location.reload(), 1500);
        }
    });

    // Load store settings saat modal dibuka
    document.getElementById('storeSettingsModal')?.addEventListener('click', async function handler() {
        const data = await RAZ.api('api/RAZapiStores.php?action=store_profile');
        if (data.success) {
            const s = data.data;
            document.getElementById('settStoreName').value = s.store_name || '';
            document.getElementById('settStoreAddr').value = s.store_address || '';
            document.getElementById('settStorePhone').value = s.store_phone || '';
            document.getElementById('settStoreTax').value = s.tax_percentage || 0;
            document.getElementById('settStoreFooter').value = s.receipt_footer || '';
        }
    }, { once: true });
    </script>
    <?php endif; ?>

    <script>
    // Tampilkan tombol menu mobile di viewport kecil
    const checkMobile = () => {
        const btn = document.getElementById('mobileMenuBtn');
        if (btn) btn.style.display = window.innerWidth <= 1024 ? 'flex' : 'none';
    };
    checkMobile();
    window.addEventListener('resize', checkMobile);
    </script>
</body>
</html>

