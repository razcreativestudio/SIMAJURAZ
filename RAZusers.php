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
require_once __DIR__ . '/includes/RAZlang.php';
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
            <li><a href="RAZreports.php"><span class="menu-icon"><i class="ph-bold ph-chart-bar"></i></span><span class="menu-text"><?= t('menu_reports') ?></span></a></li>
            <li><a href="RAZusers.php" class="active"><span class="menu-icon"><i class="ph-bold ph-users"></i></span><span class="menu-text"><?= t('menu_employees') ?></span></a></li>
        </ul>
        <div class="raz-sidebar-toggle"><button onclick="RAZ.toggleSidebar()"><i class="ph-bold ph-sidebar-simple"></i></button></div>
    </aside>

    <div class="raz-main">
        <header class="raz-topbar">
            <div class="raz-topbar-left">
                <button class="raz-btn raz-btn-ghost raz-btn-icon-only raz-btn-sm" onclick="RAZ.toggleMobileSidebar()" id="mobileMenuBtn" style="display:none;"><i class="ph-bold ph-list"></i></button>
                <h1 class="raz-topbar-title"><?= t('menu_employees') ?></h1>
            </div>
            <div class="raz-topbar-right" style="display:flex; align-items:center;">
                    <!-- Language & Theme Shortcuts -->
                    <a href="?lang=<?= (isset($current_lang) && $current_lang === 'id') ? 'en' : 'id' ?>" class="nav-action-icon raz-btn-icon" style="color:var(--raz-text); font-size:1rem; font-weight:700; text-decoration:none; display:none; align-items:center; justify-content:center; width:36px; height:36px; border-radius:50%; border:1px solid var(--raz-border); margin-right: 8px;">
                        <?= strtoupper((isset($current_lang) && $current_lang === 'id') ? 'en' : 'id') ?>
                    </a>
                    <a href="#" id="theme-toggle" class="nav-action-icon raz-btn-icon" style="color:var(--raz-text); font-size:1.2rem; text-decoration:none; display:none; align-items:center; justify-content:center; width:36px; height:36px; border-radius:50%; border:1px solid var(--raz-border); margin-right: 16px;">
                        <i class="ph-bold ph-sun"></i>
                    </a>
                <button class="raz-btn raz-btn-ghost raz-btn-sm" onclick="openProfile()"><i class="ph-bold ph-user-circle"></i> Profil Saya</button>
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
            <!-- Tabs -->
            <div class="raz-tabs" style="margin-bottom: 20px; border-bottom: 1px solid var(--raz-border);">
                <button class="raz-tab-btn active" onclick="switchUserTab('karyawan')" id="btnTabKaryawan" style="padding:10px 20px; background:none; border:none; border-bottom:2px solid var(--raz-primary); color:var(--raz-primary); font-weight:600; cursor:pointer;"><i class="ph-bold ph-users"></i> <?= t('usr_tab_data') ?></button>
                <button class="raz-tab-btn" onclick="switchUserTab('penggajian')" id="btnTabPenggajian" style="padding:10px 20px; background:none; border:none; color:var(--raz-text-muted); font-weight:600; cursor:pointer;"><i class="ph-bold ph-money"></i> <?= t('usr_tab_payroll') ?></button>
            </div>

            <!-- TAB 1: KARYAWAN -->
            <div id="tabKaryawan" class="usr-tab-content">
            <!-- Stats -->
            <div class="usr-stats">
                <div class="usr-stat"><div class="usr-stat-icon primary"><i class="ph-bold ph-users"></i></div><div><div class="usr-stat-value" id="statTotal">0</div><div class="usr-stat-label"><?= t('usr_stat_total') ?></div></div></div>
                <div class="usr-stat"><div class="usr-stat-icon success"><i class="ph-bold ph-check-circle"></i></div><div><div class="usr-stat-value" id="statActive">0</div><div class="usr-stat-label"><?= t('usr_stat_active') ?></div></div></div>
                <div class="usr-stat"><div class="usr-stat-icon warning"><i class="ph-bold ph-prohibit"></i></div><div><div class="usr-stat-value" id="statInactive">0</div><div class="usr-stat-label"><?= t('usr_stat_inactive') ?></div></div></div>
            </div>

            <!-- Toolbar -->
            <div class="raz-card" style="margin-bottom:20px;">
                <div class="raz-table-header">
                    <div class="raz-table-header-left">
                        <div class="raz-table-search"><span class="search-icon"><i class="ph-bold ph-magnifying-glass"></i></span><input type="text" id="userSearch" placeholder="<?= t('usr_search_ph') ?>"></div>
                    </div>
                    <div class="raz-table-header-right">
                        <button class="raz-btn raz-btn-primary" onclick="openAddUser()"><i class="ph-bold ph-user-plus"></i> <?= t('usr_btn_add') ?></button>
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
                            <h3 class="raz-card-title"><i class="ph-bold ph-wallet"></i> <?= t('usr_payroll_history') ?></h3>
                        </div>
                        <div class="raz-table-header-right">
                            <button class="raz-btn raz-btn-success" onclick="openAddSalary()"><i class="ph-bold ph-hand-coins"></i> <?= t('usr_btn_pay') ?></button>
                        </div>
                    </div>
                </div>

                <div class="raz-card">
                    <div class="raz-table-wrapper">
                        <table class="raz-table">
                            <thead>
                                <tr>
                                    <th><?= t('usr_table_date') ?></th>
                                    <th><?= t('usr_table_name') ?></th>
                                    <th><?= t('usr_table_period') ?></th>
                                    <th style="text-align:right"><?= t('usr_table_net') ?></th>
                                    <th style="text-align:center"><?= t('usr_table_action') ?></th>
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
            <div class="raz-modal-title"><i class="ph-bold ph-user-plus modal-icon"></i> <span id="userFormTitle"><?= t('usr_modal_add_title') ?></span></div>
            <button class="raz-modal-close" onclick="RAZ.closeModal('userModal')"><i class="ph-bold ph-x"></i></button>
        </div>
        <form onsubmit="return false;" autocomplete="off">
        <div class="raz-modal-body">
            <input type="hidden" id="userId">
            <div class="raz-form-group">
                <label class="raz-form-label"><?= t('usr_table_name') ?> <span class="required">*</span></label>
                <input type="text" id="userName" class="raz-form-input" placeholder="<?= t('usr_ph_name') ?>" autocomplete="off">
            </div>
            <div class="raz-form-group">
                <label class="raz-form-label"><?= t('usr_form_username') ?> <span class="required">*</span></label>
                <input type="text" id="userUsername" class="raz-form-input" placeholder="<?= t('usr_ph_username') ?>" autocomplete="off">
            </div>
            <div class="raz-form-group" id="pwGroup">
                <label class="raz-form-label"><?= t('usr_form_pwd') ?> <span class="required">*</span> <small style="color:var(--raz-text-muted)"><?= t('usr_lbl_min_char') ?></small></label>
                <input type="password" id="userPassword" class="raz-form-input" placeholder="<?= t('usr_ph_pwd') ?>" minlength="6" autocomplete="new-password">
            </div>
        </div>
        <div class="raz-modal-footer">
            <button type="button" class="raz-btn raz-btn-secondary" onclick="RAZ.closeModal('userModal')"><i class="ph-bold ph-x"></i> <?= t('usr_btn_cancel') ?></button>
            <button type="button" class="raz-btn raz-btn-primary" id="btnSaveUser" onclick="saveUser()"><i class="ph-bold ph-floppy-disk"></i> <?= t('usr_btn_save') ?></button>
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
            <p style="margin-bottom:16px;">Reset password for: <strong id="resetPwName"></strong></p>
            <div class="raz-form-group">
                <label class="raz-form-label"><?= t('usr_form_pwd') ?> Baru <span class="required">*</span></label>
                <input type="text" id="resetPwInput" class="raz-form-input" placeholder="<?= t('usr_lbl_min_char') ?>" minlength="6">
            </div>
        </div>
        <div class="raz-modal-footer">
            <button class="raz-btn raz-btn-secondary" onclick="RAZ.closeModal('resetPwModal')"><?= t('usr_btn_cancel') ?></button>
            <button class="raz-btn raz-btn-primary" id="btnResetPw" onclick="submitResetPw()"><i class="ph-bold ph-key"></i> Reset</button>
        </div>
    </div>
</div>

<!-- MODAL: Profil Owner -->
<div class="raz-modal-overlay" id="profileModal">
    <div class="raz-modal modal-sm">
        <div class="raz-modal-header">
            <div class="raz-modal-title"><i class="ph-bold ph-user-circle modal-icon"></i> <?= t('set_tab_profile') ?></div>
            <button class="raz-modal-close" onclick="RAZ.closeModal('profileModal')"><i class="ph-bold ph-x"></i></button>
        </div>
        <form onsubmit="return false;" autocomplete="off">
        <div class="raz-modal-body">
            <div class="raz-form-group">
                <label class="raz-form-label"><?= t('usr_table_name') ?></label>
                <input type="text" id="profileName" class="raz-form-input" autocomplete="off">
            </div>
            <div style="padding:8px 0;font-size:0.82rem;color:var(--raz-text-muted);">Ubah password (kosongkan jika tidak ingin ubah):</div>
            <div class="raz-form-group">
                <label class="raz-form-label"><?= t('usr_form_pwd') ?> Lama</label>
                <input type="password" id="profileCurrentPw" class="raz-form-input" placeholder="Password saat ini" autocomplete="current-password">
            </div>
            <div class="raz-form-group">
                <label class="raz-form-label"><?= t('usr_form_pwd') ?> Baru</label>
                <input type="password" id="profileNewPw" class="raz-form-input" placeholder="Min. 6 karakter" autocomplete="new-password">
            </div>
        </div>
        <div class="raz-modal-footer">
            <button type="button" class="raz-btn raz-btn-secondary" onclick="RAZ.closeModal('profileModal')"><?= t('usr_btn_cancel') ?></button>
            <button type="button" class="raz-btn raz-btn-primary" id="btnSaveProfile" onclick="saveProfile()"><i class="ph-bold ph-floppy-disk"></i> <?= t('usr_btn_save') ?></button>
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
                    <label class="raz-form-label"><?= t('usr_lbl_select_emp') ?> <span class="required">*</span></label>
                    <select id="salaryUserId" class="raz-form-input"></select>
                </div>
                <div class="raz-form-group">
                    <label class="raz-form-label"><?= t('usr_lbl_period_type') ?> <span class="required">*</span></label>
                    <select id="salaryPeriod" class="raz-form-input">
                        <option value="Harian"><?= t('usr_opt_daily') ?></option>
                        <option value="Mingguan"><?= t('usr_opt_weekly') ?></option>
                        <option value="Bulanan" selected><?= t('usr_opt_monthly') ?></option>
                    </select>
                </div>
            </div>
            
            <div class="raz-form-group">
                <label class="raz-form-label"><?= t('usr_lbl_base_salary') ?> <span class="required">*</span></label>
                <input type="number" id="salaryBase" class="raz-form-input" placeholder="0" min="0" oninput="calcNetSalary()">
            </div>
            
            <div style="display:grid; grid-template-columns:1fr 1fr; gap:15px;">
                <div class="raz-form-group">
                    <label class="raz-form-label" style="color:var(--raz-success)"><?= t('usr_lbl_bonus') ?></label>
                    <input type="number" id="salaryBonus" class="raz-form-input" placeholder="0" min="0" oninput="calcNetSalary()">
                </div>
                <div class="raz-form-group">
                    <label class="raz-form-label" style="color:var(--raz-danger)"><?= t('usr_lbl_deduction') ?></label>
                    <input type="number" id="salaryDeduction" class="raz-form-input" placeholder="0" min="0" oninput="calcNetSalary()">
                </div>
            </div>
            
            <div class="raz-form-group" style="background:var(--raz-bg); padding:15px; border-radius:8px; margin:10px 0;">
                <div style="display:flex; justify-content:space-between; align-items:center;">
                    <strong style="font-size:1.1rem;"><?= t('usr_lbl_net_total') ?></strong>
                    <strong style="font-size:1.3rem; color:var(--raz-primary);" id="salaryNetText">Rp 0</strong>
                </div>
            </div>

            <div class="raz-form-group">
                <label class="raz-form-label"><?= t('usr_lbl_pay_date') ?></label>
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

<script src="assets/js/RAZMain.js?v=<?= time() ?>"></script>
<script src="assets/js/RAZUsers.js"></script>
<script>const checkMobile=()=>{const b=document.getElementById('mobileMenuBtn');if(b)b.style.display=window.innerWidth<=1024?'flex':'none';};checkMobile();window.addEventListener('resize',checkMobile);</script>
</body>
</html>



