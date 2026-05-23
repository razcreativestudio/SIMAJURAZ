<?php
/**
 * ============================================================
 * RAZsettings.php — Pengaturan Toko SIMAJURAZ
 * ============================================================
 * Versi: 1.0.0
 * ============================================================
 */
require_once __DIR__ . '/RAZconfig.php';
require_once __DIR__ . '/includes/RAZsession.php';
require_once __DIR__ . '/includes/RAZhelpers.php';
require_once __DIR__ . '/includes/RAZlang.php';
RAZrequireOwner();
$user = RAZgetCurrentUser();
$currentPage = 'settings';
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pengaturan Toko — SIMAJURAZ</title>
    <link rel="stylesheet" href="https://unpkg.com/@phosphor-icons/web@2.1.1/src/bold/style.css">
    <link rel="stylesheet" href="assets/css/RAZMain.css">
    <link rel="stylesheet" href="assets/css/RAZComponents.css">
    <link rel="stylesheet" href="assets/css/RAZSettings.css">
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
            <li><a href="RAZsettings.php" class="active"><span class="menu-icon"><i class="ph-bold ph-gear"></i></span><span class="menu-text"><?= t('menu_settings') ?></span></a></li>
            <li class="raz-sidebar-section"><?= t('menu_manage') ?></li>
            <li><a href="RAZinventory.php"><span class="menu-icon"><i class="ph-bold ph-package"></i></span><span class="menu-text"><?= t('menu_inventory') ?></span></a></li>
            <li><a href="RAZfinance.php"><span class="menu-icon"><i class="ph-bold ph-wallet"></i></span><span class="menu-text"><?= t('menu_finance') ?></span></a></li>
            <li><a href="RAZreports.php"><span class="menu-icon"><i class="ph-bold ph-chart-bar"></i></span><span class="menu-text"><?= t('menu_reports') ?></span></a></li>
            <li><a href="RAZusers.php"><span class="menu-icon"><i class="ph-bold ph-users"></i></span><span class="menu-text"><?= t('menu_employees') ?></span></a></li>
        </ul>
        <div class="raz-sidebar-toggle"><button onclick="RAZ.toggleSidebar()"><i class="ph-bold ph-sidebar-simple"></i></button></div>
    </aside>

    <div class="raz-main">
        <header class="raz-topbar">
            <div class="raz-topbar-left">
                <button class="raz-btn raz-btn-ghost raz-btn-icon-only raz-btn-sm" onclick="RAZ.toggleMobileSidebar()" id="mobileMenuBtn" style="display:none;"><i class="ph-bold ph-list"></i></button>
                <h1 class="raz-topbar-title"><?= t('menu_settings') ?></h1>
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
            <div class="set-wrapper">
                
                <!-- SIDEBAR SETTINGS -->
                <div class="set-sidebar">
                    <div class="set-tab active" data-target="tabProfile"><i class="ph-bold ph-storefront"></i> <?= t('set_tab_profile') ?></div>
                    <div class="set-tab" data-target="tabReceipt"><i class="ph-bold ph-receipt"></i> <?= t('set_tab_receipt') ?></div>
                    <div class="set-tab" data-target="tabSecurity"><i class="ph-bold ph-shield-check"></i> <?= t('set_tab_security') ?></div>
                </div>

                <!-- CONTENT -->
                <div class="set-content">
                    
                    <!-- TAB 1: PROFIL -->
                    <div class="set-panel active raz-card" id="tabProfile">
                        <div class="raz-card-header"><h3 class="raz-card-title"><?= t('set_tab_profile') ?></h3></div>
                        <div class="raz-card-body">
                            <div class="set-logo-wrap">
                                <div class="set-logo-preview" id="setLogoPreview"><i class="ph-bold ph-image"></i></div>
                                <div>
                                    <h4 style="margin-bottom:8px"><?= t('set_logo') ?></h4>
                                    <div class="set-logo-btn raz-btn raz-btn-secondary raz-btn-sm">
                                        <i class="ph-bold ph-upload-simple"></i> <?= t('set_logo_btn') ?>
                                        <input type="file" id="setLogoFile" accept="image/*">
                                    </div>
                                    <div style="font-size:0.75rem;color:var(--raz-text-muted);margin-top:6px;">Format: JPG, PNG, max 2MB.</div>
                                </div>
                            </div>

                            <form id="formProfile" onsubmit="event.preventDefault();">
                                <div class="raz-form-group">
                                    <label class="raz-form-label"><?= t('set_name') ?> <span class="required">*</span></label>
                                    <input type="text" id="setName" class="raz-form-input" required>
                                </div>
                                <div class="raz-form-group">
                                    <label class="raz-form-label"><?= t('set_type') ?></label>
                                    <select id="setType" class="raz-form-input">
                                        <option value="">Pilih Jenis</option>
                                        <option value="Cafe">Cafe / Coffee Shop</option>
                                        <option value="Restoran">Restoran</option>
                                        <option value="Kaki Lima">Kaki Lima / Street Food</option>
                                        <option value="Jajanan">Toko Jajanan / Snack</option>
                                        <option value="Warung Kelontong">Warung Kelontong</option>
                                        <option value="Toko Serba Ada">Toko Serba Ada (Toserba)</option>
                                        <option value="Toko Bangunan">Toko Bangunan</option>
                                        <option value="Apotek">Apotek / Klinik</option>
                                        <option value="Lainnya">Lainnya</option>
                                    </select>
                                </div>
                                <div class="raz-form-group">
                                    <label class="raz-form-label"><?= t('set_desc') ?></label>
                                    <textarea id="setDesc" class="raz-form-input" rows="2" placeholder="Contoh: Menyajikan kopi terbaik sejak 2026"></textarea>
                                </div>
                                <div class="raz-form-group">
                                    <label class="raz-form-label"><?= t('set_phone') ?></label>
                                    <input type="text" id="setPhone" class="raz-form-input">
                                </div>
                                <div class="raz-form-group">
                                    <label class="raz-form-label"><?= t('set_address') ?></label>
                                    <textarea id="setAddress" class="raz-form-input" rows="3"></textarea>
                                </div>
                                <button type="button" class="raz-btn raz-btn-primary" id="btnSaveProfile" onclick="saveProfile()"><i class="ph-bold ph-floppy-disk"></i> <?= t('set_btn_save_profile') ?></button>
                            </form>
                        </div>
                    </div>

                    <!-- TAB 2: STRUK -->
                    <div class="set-panel raz-card" id="tabReceipt">
                        <div class="raz-card-header"><h3 class="raz-card-title"><?= t('set_tab_receipt') ?></h3></div>
                        <div class="raz-card-body">
                            <div class="raz-form-group">
                                <label class="raz-form-label"><?= t('set_rec_format') ?></label>
                                <div style="display:flex; gap:8px; margin-bottom:8px; flex-wrap:wrap;">
                                    <button type="button" class="raz-btn raz-btn-secondary raz-btn-sm" onclick="addFormatTag('{dmY}')">+ Tgl (dmY)</button>
                                    <button type="button" class="raz-btn raz-btn-secondary raz-btn-sm" onclick="addFormatTag('{Ymd}')">+ Tgl (Ymd)</button>
                                    <button type="button" class="raz-btn raz-btn-secondary raz-btn-sm" onclick="addFormatTag('{dmy}')">+ Tgl (dmy)</button>
                                    <button type="button" class="raz-btn raz-btn-secondary raz-btn-sm" onclick="addFormatTag('{ymd}')">+ Tgl (ymd)</button>
                                    <button type="button" class="raz-btn raz-btn-secondary raz-btn-sm" onclick="addFormatTag('{SEQ5}')">+ No. Urut (SEQ)</button>
                                    <button type="button" class="raz-btn raz-btn-secondary raz-btn-sm" onclick="addFormatTag('{RAND4}')">+ Acak (RAND)</button>
                                    <button type="button" class="raz-btn raz-btn-secondary raz-btn-sm" onclick="addFormatTag('{MIX5}')">+ Acak (MIX)</button>
                                    <button type="button" class="raz-btn raz-btn-secondary raz-btn-sm" onclick="addFormatTag('-')">+ Strip (-)</button>
                                    <button type="button" class="raz-btn raz-btn-secondary raz-btn-sm" onclick="addFormatTag('/')">+ Garis Miring (/)</button>
                                </div>
                                <input type="text" id="setInvoiceFormat" class="raz-form-input raz-text-mono" placeholder="Contoh: INV-{Ymd}-{SEQ5}" value="INV-{Ymd}-{SEQ5}" oninput="updateInvoicePreview()">
                                <div style="font-size:0.85rem;color:var(--raz-text-muted);margin-top:8px;">
                                    Preview: <strong id="invoicePreview" style="color:var(--raz-primary)">INV-20260522-00001</strong>
                                </div>
                                
                                <!-- Penjelasan Panduan Format -->
                                <div style="margin-top:12px; background:rgba(79, 70, 229, 0.05); padding:12px; border-radius:8px; border:1px solid rgba(79, 70, 229, 0.1);">
                                    <h4 style="font-size:0.85rem; margin-bottom:8px; color:var(--raz-primary);"><i class="ph-bold ph-info"></i> <?= t('set_rec_guide') ?>:</h4>
                                    <ul style="font-size:0.75rem; color:var(--raz-text-muted); padding-left:16px; margin:0; line-height:1.6;">
                                        <li><strong>Tanggal:</strong> <code>{dmY}</code>=22052026, <code>{Ymd}</code>=20260522, <code>{dmy}</code>=220526, <code>{ymd}</code>=260522, <code>{mdy}</code>=052226, <code>{Ym}</code>=202605, <code>{Y-m-d}</code>=2026-05-22</li>
                                        <li><strong>Nomor Urut Harian:</strong> <code>{SEQ3}</code>=001, <code>{SEQ4}</code>=0001, <code>{SEQ5}</code>=00001 (Akan otomatis reset ke 1 setiap hari baru)</li>
                                        <li><strong>Nomor Acak:</strong> <code>{RAND3}</code> s/d <code>{RAND6}</code> (Contoh RAND4 = 8192)</li>
                                        <li><strong>Kombinasi Huruf & Angka Acak:</strong> <code>{MIX3}</code> s/d <code>{MIX6}</code> (Contoh MIX4 = A9X2)</li>
                                        <li>Teks lain di luar tanda kurung kurawal (seperti <code>INV-</code> atau <code>STRUK/</code>) akan ditampilkan apa adanya.</li>
                                    </ul>
                                </div>
                            </div>
                            <div class="raz-form-group">
                                <label class="raz-form-label"><?= t('set_rec_show_logo') ?></label>
                                <select id="setShowLogo" class="raz-form-input">
                                    <option value="1">Atas Tengah</option>
                                    <option value="2">Atas Kiri</option>
                                    <option value="3">Atas Kanan</option>
                                    <option value="4">Bawah Tengah</option>
                                    <option value="5">Bawah Kiri</option>
                                    <option value="6">Bawah Kanan</option>
                                    <option value="7">Watermark (Tengah Transparan 50% & BW)</option>
                                    <option value="8">Watermark (Tile Full Kertas 50% & BW)</option>
                                    <option value="0">Sembunyikan Logo</option>
                                </select>
                            </div>
                            <div class="raz-form-group">
                                <label class="raz-form-label"><?= t('set_rec_header') ?></label>
                                <textarea id="setHeader" class="raz-form-input" rows="2" placeholder="Contoh: Selamat Datang di Toko Kami!"></textarea>
                            </div>
                            <div class="raz-form-group">
                                <label class="raz-form-label"><?= t('set_rec_footer') ?></label>
                                <textarea id="setFooter" class="raz-form-input" rows="2" placeholder="Contoh: Terima Kasih, Barang yang sudah dibeli tidak bisa dikembalikan."></textarea>
                            </div>
                            
                            <hr style="border:0;border-bottom:1px solid var(--raz-border);margin:20px 0;">
                            
                            <label class="raz-form-label"><?= t('set_rec_template') ?></label>
                            <input type="hidden" id="inputTemplateId" value="1">
                            
                            <div style="display:flex; flex-direction:column; gap:20px; margin-bottom:20px;">
                                <!-- Template Grid -->
                                <div class="set-tpl-grid" id="tplGrid" style="max-height:400px; overflow-y:auto; padding-right:10px;">
                                    <!-- Generated by JS -->
                                </div>
                                
                                <!-- Live Preview Struk -->
                                <div>
                                    <h4 style="font-size:1rem; margin-bottom:12px; color:var(--raz-text); font-weight:600;"><i class="ph-bold ph-eye"></i> <?= t('set_rec_preview') ?></h4>
                                    <div style="background:var(--raz-bg-alt); padding:20px; border-radius:8px; display:flex; justify-content:center; align-items:center; border:1px solid var(--raz-border);">
                                        <div id="fullReceiptPreview" style="background:#fff; color:#000; padding:15px; width:100%; max-width:80mm; min-height:200px; box-shadow:0 4px 12px rgba(0,0,0,0.1); display:flex; flex-direction:column;">
                                            <!-- Generated by JS -->
                                        </div>
                                    </div>
                                    <p style="font-size:0.75rem; color:var(--raz-text-muted); margin-top:8px; text-align:center;">Ini adalah simulasi kasar. Hasil cetak asli mungkin sedikit berbeda tergantung printer Anda.</p>
                                </div>
                            </div>

                            <button type="button" class="raz-btn raz-btn-primary" id="btnSaveReceipt" onclick="saveReceipt()"><i class="ph-bold ph-floppy-disk"></i> <?= t('set_btn_save_rec') ?></button>
                        </div>
                    </div>

                    <!-- TAB 3: KEAMANAN -->
                    <div class="set-panel raz-card" id="tabSecurity">
                        <div class="raz-card-header"><h3 class="raz-card-title"><?= t('set_sec_title') ?></h3></div>
                        <div class="raz-card-body">
                            <form id="formSecurity" onsubmit="event.preventDefault(); savePassword();">
                                <!-- Hidden username field for accessibility -->
                                <input type="text" id="secUsername" autocomplete="username" style="display:none;" value="<?= htmlspecialchars($user['username']) ?>">
                                
                                <div class="raz-form-group">
                                    <label class="raz-form-label"><?= t('set_sec_cur_pw') ?></label>
                                    <input type="password" id="setCurPw" class="raz-form-input" autocomplete="current-password">
                                </div>
                                <div class="raz-form-group">
                                    <label class="raz-form-label"><?= t('set_sec_new_pw') ?></label>
                                    <input type="password" id="setNewPw" class="raz-form-input" placeholder="Minimal 6 karakter" autocomplete="new-password">
                                </div>
                                <button type="submit" class="raz-btn raz-btn-primary" id="btnSavePw"><i class="ph-bold ph-key"></i> <?= t('set_btn_save_pw') ?></button>
                            </form>
                        </div>
                    </div>

                </div>
            </div>
        </main>
    </div>
</div>

<script src="assets/js/RAZMain.js?v=<?= filemtime(__DIR__.'/assets/js/RAZMain.js') ?>"></script>
<script src="assets/js/RAZSettings.js?v=<?= filemtime(__DIR__.'/assets/js/RAZSettings.js') ?>"></script>
<script>const checkMobile=()=>{const b=document.getElementById('mobileMenuBtn');if(b)b.style.display=window.innerWidth<=1024?'flex':'none';};checkMobile();window.addEventListener('resize',checkMobile);</script>
</body>
</html>

