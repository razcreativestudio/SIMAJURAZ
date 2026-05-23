<?php
/**
 * ============================================================
 * RAZusers.php â€” Manajemen Karyawan SIMAJURAZ
 * ============================================================
 * Versi: 1.0.0 | Dibuat: 2026-05-21 | Diupdate: 2026-05-21
 * Deskripsi: CRUD karyawan (tambah, edit, hapus, reset password).
 *            Toggle aktif/nonaktif, profil Owner.
 * ============================================================
 */
require_once __DIR__ . '/RAZconfig.php';
require_once __DIR__ . '/includes/RAZsession.php';
require_once __DIR__ . '/includes/RAZhelpers.php';
RAZrequireOwner();
$user = RAZgetCurrentUser();
$currentPage = 'users';
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Karyawan â€” SIMAJURAZ</title>
    <link rel="stylesheet" href="https://unpkg.com/@phosphor-icons/web@2.1.1/src/bold/style.css">
    <link rel="stylesheet" href="assets/css/RAZMain.css">
    <link rel="stylesheet" href="assets/css/RAZComponents.css">
    <link rel="stylesheet" href="assets/css/RAZModal.css">
    <link rel="stylesheet" href="assets/css/RAZUsers.css">
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
            <li><a href="RAZfinance.php"><span class="menu-icon"><i class="ph-bold ph-wallet"></i></span><span class="menu-text">Keuangan</span></a></li>
            <li><a href="RAZreports.php"><span class="menu-icon"><i class="ph-bold ph-chart-bar"></i></span><span class="menu-text">Laporan</span></a></li>
            <li><a href="RAZusers.php" class="active"><span class="menu-icon"><i class="ph-bold ph-users"></i></span><span class="menu-text">Karyawan</span></a></li>
        </ul>
        <div class="raz-sidebar-toggle"><button onclick="RAZ.toggleSidebar()"><i class="ph-bold ph-sidebar-simple"></i></button></div>
    </aside>

    <div class="raz-main">
        <header class="raz-topbar">
            <div class="raz-topbar-left">
                <button class="raz-btn raz-btn-ghost raz-btn-icon-only raz-btn-sm" onclick="RAZ.toggleMobileSidebar()" id="mobileMenuBtn" style="display:none;"><i class="ph-bold ph-list"></i></button>
                <h1 class="raz-topbar-title">Karyawan</h1>
            </div>
            <div class="raz-topbar-right">
                <button class="raz-btn raz-btn-ghost raz-btn-sm" onclick="openProfile()"><i class="ph-bold ph-user-circle"></i> Profil Saya</button>
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
            <!-- Tabs -->
            <div class="raz-tabs" style="margin-bottom: 20px; border-bottom: 1px solid var(--raz-border);">
                <button class="raz-tab-btn active" onclick="switchUserTab('karyawan')" id="btnTabKaryawan" style="padding:10px 20px; background:none; border:none; border-bottom:2px solid var(--raz-primary); color:var(--raz-primary); font-weight:600; cursor:pointer;"><i class="ph-bold ph-users"></i> Data Karyawan</button>
                <button class="raz-tab-btn" onclick="switchUserTab('penggajian')" id="btnTabPenggajian" style="padding:10px 20px; background:none; border:none; color:var(--raz-text-muted); font-weight:600; cursor:pointer;"><i class="ph-bold ph-money"></i> Penggajian (Payroll)</button>
            </div>

            <!-- TAB 1: KARYAWAN -->
            <div id="tabKaryawan" class="usr-tab-content">
            <!-- Stats -->
            <div class="usr-stats">
                <div class="usr-stat"><div class="usr-stat-icon primary"><i class="ph-bold ph-users"></i></div><div><div class="usr-stat-value" id="statTotal">0</div><div class="usr-stat-label">Total Karyawan</div></div></div>
                <div class="usr-stat"><div class="usr-stat-icon success"><i class="ph-bold ph-check-circle"></i></div><div><div class="usr-stat-value" id="statActive">0</div><div class="usr-stat-label">Aktif</div></div></div>
                <div class="usr-stat"><div class="usr-stat-icon warning"><i class="ph-bold ph-prohibit"></i></div><div><div class="usr-stat-value" id="statInactive">0</div><div class="usr-stat-label">Nonaktif</div></div></div>
            </div>

            <!-- Toolbar -->
            <div class="raz-card" style="margin-bottom:20px;">
                <div class="raz-table-header">
                    <div class="raz-table-header-left">
                        <div class="raz-table-search"><span class="search-icon"><i class="ph-bold ph-magnifying-glass"></i></span><input type="text" id="userSearch" placeholder="Cari karyawan..."></div>
                    </div>
                    <div class="raz-table-header-right">
                        <button class="raz-btn raz-btn-primary" onclick="openAddUser()"><i class="ph-bold ph-user-plus"></i> Tambah Karyawan</button>
                    </div>
                </div>
            </div>

            <!-- User Grid -->
            <div class="usr-grid" id="userGrid">
                <div class="usr-card" style="pointer-events:none"><div class="raz-skeleton" style="height:60px"></div><div class="raz-skeleton raz-skeleton-text" style="margin-top:12px"></div></div>
                <div class="usr-card" style="pointer-events:none"><div class="raz-skeleton" style="height:60px"></div><div class="raz-skeleton raz-skeleton-text" style="margin-top:12px"></div></div>
            </div>
                    </div>

            <!-- TAB 2: PENGGAJIAN -->
            <div id="tabPenggajian" class="usr-tab-content" style="display:none;">
                <div class="raz-card" style="margin-bottom:20px;">
                    <div class="raz-table-header">
                        <div class="raz-table-header-left">
                            <h3 class="raz-card-title"><i class="ph-bold ph-wallet"></i> Riwayat Penggajian</h3>
                        </div>
                        <div class="raz-table-header-right">
                            <button class="raz-btn raz-btn-success" onclick="openAddSalary()"><i class="ph-bold ph-hand-coins"></i> Bayar Gaji</button>
                        </div>
                    </div>
                </div>

                <div class="raz-card">
                    <div class="raz-table-wrapper">
                        <table class="raz-table">
                            <thead>
                                <tr>
                                    <th>Tanggal</th>
                                    <th>Nama Karyawan</th>
                                    <th>Periode</th>
                                    <th style="text-align:right">Gaji Bersih</th>
                                    <th style="text-align:center">Aksi</th>
                                </tr>
                            </thead>
                            <tbody id="salaryBody">
                                <tr><td colspan="5"><div class="raz-skeleton raz-skeleton-table-row"></div></td></tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </main>
    </div>
</div>

<!-- MODAL: Tambah/Edit Karyawan -->
<div class="raz-modal-overlay" id="userModal">
    <div class="raz-modal modal-sm">
        <div class="raz-modal-header">
            <div class="raz-modal-title"><i class="ph-bold ph-user-plus modal-icon"></i> <span id="userFormTitle">Tambah Karyawan</span></div>
            <button class="raz-modal-close" onclick="RAZ.closeModal('userModal')"><i class="ph-bold ph-x"></i></button>
        </div>
        <form onsubmit="return false;" autocomplete="off">
        <div class="raz-modal-body">
            <input type="hidden" id="userId">
            <div class="raz-form-group">
                <label class="raz-form-label">Nama Lengkap <span class="required">*</span></label>
                <input type="text" id="userName" class="raz-form-input" placeholder="Nama lengkap karyawan" autocomplete="off">
            </div>
            <div class="raz-form-group">
                <label class="raz-form-label">Username <span class="required">*</span></label>
                <input type="text" id="userUsername" class="raz-form-input" placeholder="Username untuk login" autocomplete="off">
            </div>
            <div class="raz-form-group" id="pwGroup">
                <label class="raz-form-label">Password <span class="required">*</span> <small style="color:var(--raz-text-muted)">(min. 6 karakter)</small></label>
                <input type="password" id="userPassword" class="raz-form-input" placeholder="Buat password" minlength="6" autocomplete="new-password">
            </div>
        </div>
        <div class="raz-modal-footer">
            <button type="button" class="raz-btn raz-btn-secondary" onclick="RAZ.closeModal('userModal')"><i class="ph-bold ph-x"></i> Batal</button>
            <button type="button" class="raz-btn raz-btn-primary" id="btnSaveUser" onclick="saveUser()"><i class="ph-bold ph-floppy-disk"></i> Simpan</button>
        </div>
        </form>
    </div>
</div>

<!-- MODAL: Reset Password -->
<div class="raz-modal-overlay" id="resetPwModal">
    <div class="raz-modal modal-sm">
        <div class="raz-modal-header">
            <div class="raz-modal-title"><i class="ph-bold ph-key modal-icon"></i> Reset Password</div>
            <button class="raz-modal-close" onclick="RAZ.closeModal('resetPwModal')"><i class="ph-bold ph-x"></i></button>
        </div>
        <div class="raz-modal-body">
            <input type="hidden" id="resetPwUserId">
            <p style="margin-bottom:16px;">Reset password untuk: <strong id="resetPwName"></strong></p>
            <div class="raz-form-group">
                <label class="raz-form-label">Password Baru <span class="required">*</span></label>
                <input type="text" id="resetPwInput" class="raz-form-input" placeholder="Minimal 6 karakter" minlength="6">
            </div>
        </div>
        <div class="raz-modal-footer">
            <button class="raz-btn raz-btn-secondary" onclick="RAZ.closeModal('resetPwModal')">Batal</button>
            <button class="raz-btn raz-btn-primary" id="btnResetPw" onclick="submitResetPw()"><i class="ph-bold ph-key"></i> Reset</button>
        </div>
    </div>
</div>

<!-- MODAL: Profil Owner -->
<div class="raz-modal-overlay" id="profileModal">
    <div class="raz-modal modal-sm">
        <div class="raz-modal-header">
            <div class="raz-modal-title"><i class="ph-bold ph-user-circle modal-icon"></i> Profil Saya</div>
            <button class="raz-modal-close" onclick="RAZ.closeModal('profileModal')"><i class="ph-bold ph-x"></i></button>
        </div>
        <form onsubmit="return false;" autocomplete="off">
        <div class="raz-modal-body">
            <div class="raz-form-group">
                <label class="raz-form-label">Nama Lengkap</label>
                <input type="text" id="profileName" class="raz-form-input" autocomplete="off">
            </div>
            <div style="padding:8px 0;font-size:0.82rem;color:var(--raz-text-muted);">Ubah password (kosongkan jika tidak ingin ubah):</div>
            <div class="raz-form-group">
                <label class="raz-form-label">Password Lama</label>
                <input type="password" id="profileCurrentPw" class="raz-form-input" placeholder="Password saat ini" autocomplete="current-password">
            </div>
            <div class="raz-form-group">
                <label class="raz-form-label">Password Baru</label>
                <input type="password" id="profileNewPw" class="raz-form-input" placeholder="Min. 6 karakter" autocomplete="new-password">
            </div>
        </div>
        <div class="raz-modal-footer">
            <button type="button" class="raz-btn raz-btn-secondary" onclick="RAZ.closeModal('profileModal')">Batal</button>
            <button type="button" class="raz-btn raz-btn-primary" id="btnSaveProfile" onclick="saveProfile()"><i class="ph-bold ph-floppy-disk"></i> Simpan</button>
        </div>
        </form>
    </div>
</div>

<!-- MODAL: Bayar Gaji -->
<div class="raz-modal-overlay" id="salaryModal">
    <div class="raz-modal modal-md">
        <div class="raz-modal-header">
            <div class="raz-modal-title"><i class="ph-bold ph-hand-coins modal-icon"></i> <span id="salaryFormTitle">Pembayaran Gaji</span></div>
            <button class="raz-modal-close" onclick="RAZ.closeModal('salaryModal')"><i class="ph-bold ph-x"></i></button>
        </div>
        <div class="raz-modal-body">
            <input type="hidden" id="salaryId">
            <div style="display:grid; grid-template-columns:1fr 1fr; gap:15px;">
                <div class="raz-form-group">
                    <label class="raz-form-label">Pilih Karyawan <span class="required">*</span></label>
                    <select id="salaryUserId" class="raz-form-input"></select>
                </div>
                <div class="raz-form-group">
                    <label class="raz-form-label">Tipe Periode <span class="required">*</span></label>
                    <select id="salaryPeriod" class="raz-form-input">
                        <option value="Harian">Harian</option>
                        <option value="Mingguan">Mingguan</option>
                        <option value="Bulanan" selected>Bulanan</option>
                    </select>
                </div>
            </div>
            
            <div class="raz-form-group">
                <label class="raz-form-label">Gaji Pokok (Rp) <span class="required">*</span></label>
                <input type="number" id="salaryBase" class="raz-form-input" placeholder="0" min="0" oninput="calcNetSalary()">
            </div>
            
            <div style="display:grid; grid-template-columns:1fr 1fr; gap:15px;">
                <div class="raz-form-group">
                    <label class="raz-form-label" style="color:var(--raz-success)">+ Bonus / Tunjangan (Rp)</label>
                    <input type="number" id="salaryBonus" class="raz-form-input" placeholder="0" min="0" oninput="calcNetSalary()">
                </div>
                <div class="raz-form-group">
                    <label class="raz-form-label" style="color:var(--raz-danger)">- Potongan / Kasbon (Rp)</label>
                    <input type="number" id="salaryDeduction" class="raz-form-input" placeholder="0" min="0" oninput="calcNetSalary()">
                </div>
            </div>
            
            <div class="raz-form-group" style="background:var(--raz-bg); padding:15px; border-radius:8px; margin:10px 0;">
                <div style="display:flex; justify-content:space-between; align-items:center;">
                    <strong style="font-size:1.1rem;">Total Gaji Bersih:</strong>
                    <strong style="font-size:1.3rem; color:var(--raz-primary);" id="salaryNetText">Rp 0</strong>
                </div>
            </div>

            <div class="raz-form-group">
                <label class="raz-form-label">Tanggal Pembayaran</label>
                <input type="date" id="salaryDate" class="raz-form-input">
            </div>
            <div class="raz-form-group">
                <label class="raz-form-label">Catatan Tambahan (Masuk ke Arus Kas)</label>
                <input type="text" id="salaryNotes" class="raz-form-input" placeholder="Misal: Gaji bulan ini potong kasbon 100rb">
            </div>
        </div>
        <div class="raz-modal-footer">
            <button class="raz-btn raz-btn-secondary" onclick="RAZ.closeModal('salaryModal')">Batal</button>
            <button class="raz-btn raz-btn-primary" onclick="saveSalary()"><i class="ph-bold ph-floppy-disk"></i> Simpan Pembayaran</button>
        </div>
    </div>
</div>

<script src="assets/js/RAZMain.js"></script>
<script src="assets/js/RAZUsers.js"></script>
<script>const checkMobile=()=>{const b=document.getElementById('mobileMenuBtn');if(b)b.style.display=window.innerWidth<=1024?'flex':'none';};checkMobile();window.addEventListener('resize',checkMobile);</script>
</body>
</html>



